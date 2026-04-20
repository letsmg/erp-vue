<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Events\PaymentCreatedEvent;
use App\Events\PaymentFailedEvent;
use App\Events\PaymentApprovedEvent;
use App\Jobs\ProcessPaymentJob;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    ) {}

    /**
     * Cria preferência de pagamento no Mercado Pago
     */
    public function createPaymentPreference(int $saleId, array $paymentData): array
    {
        try {
            DB::beginTransaction();

            $sale = \App\Models\Sale::find($saleId);
            if (!$sale) {
                throw new \Exception('Pedido não encontrado');
            }

            if (!$sale->isPending()) {
                throw new \Exception('Apenas pedidos pendentes podem receber pagamentos');
            }

            // Criar pagamento inicial
            $payment = $this->paymentRepository->create([
                'sale_id' => $saleId,
                'preference_id' => $paymentData['preference_id'] ?? null,
                'payment_type' => $paymentData['payment_type'] ?? null,
                'status' => 'pending',
                'transaction_amount' => $sale->total_amount,
                'metadata' => $paymentData['metadata'] ?? null,
                'external_reference' => $saleId,
            ]);

            // Disparar evento para Kafka
            event(new PaymentCreatedEvent($payment));

            DB::commit();

            // Disparar job para RabbitMQ processar pagamento
            ProcessPaymentJob::dispatch($payment->id);

            return [
                'success' => true,
                'payment' => $payment,
                'preference_id' => $payment->preference_id,
                'message' => 'Preferência de pagamento criada',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Erro ao criar preferência: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processa webhook do Mercado Pago
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            $paymentId = $webhookData['data']['id'] ?? null;
            $action = $webhookData['action'] ?? null;

            if (!$paymentId) {
                return [
                    'success' => false,
                    'message' => 'ID do pagamento não fornecido',
                ];
            }

            if ($action === 'payment.created' || $action === 'payment.updated') {
                // Disparar job para processar pagamento
                ProcessPaymentJob::dispatch($paymentId);

                return [
                    'success' => true,
                    'message' => 'Webhook recebido e processamento iniciado',
                ];
            }

            return [
                'success' => false,
                'message' => 'Ação não reconhecida',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Atualiza status do pagamento
     */
    public function updatePaymentStatus(int $paymentId, string $status, ?string $statusDetail = null): array
    {
        try {
            $payment = $this->paymentRepository->findById($paymentId);

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado',
                ];
            }

            $oldStatus = $payment->status;
            $updated = $this->paymentRepository->updateStatus($paymentId, $status, $statusDetail);

            if ($updated) {
                // Atualizar status do pedido baseado no pagamento
                $this->updateOrderStatusBasedOnPayment($payment, $status);

                // Disparar eventos baseados no status
                if ($status === 'approved') {
                    event(new PaymentApprovedEvent($payment, $oldStatus));
                } elseif (in_array($status, ['rejected', 'cancelled'])) {
                    event(new PaymentFailedEvent($payment, $oldStatus));
                }

                return [
                    'success' => true,
                    'message' => 'Status do pagamento atualizado',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao atualizar status',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Atualiza pagamento com dados do Mercado Pago
     */
    public function updateWithMercadoPagoData(int $paymentId, array $mpData): array
    {
        try {
            $payment = $this->paymentRepository->findById($paymentId);

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado',
                ];
            }

            $oldStatus = $payment->status;
            $updated = $this->paymentRepository->updateWithMercadoPagoData($paymentId, $mpData);

            if ($updated) {
                $payment->refresh();

                // Atualizar status do pedido baseado no pagamento
                $this->updateOrderStatusBasedOnPayment($payment, $payment->status);

                // Disparar eventos baseados no status
                if ($payment->status === 'approved') {
                    event(new PaymentApprovedEvent($payment, $oldStatus));
                } elseif (in_array($payment->status, ['rejected', 'cancelled'])) {
                    event(new PaymentFailedEvent($payment, $oldStatus));
                }

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Pagamento atualizado com dados do Mercado Pago',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao atualizar pagamento',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar pagamento: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Atualiza status do pedido baseado no pagamento
     */
    private function updateOrderStatusBasedOnPayment(Payment $payment, string $paymentStatus): void
    {
        $sale = $payment->sale;

        if (!$sale) {
            return;
        }

        $orderStatus = match ($paymentStatus) {
            'approved' => 'paid',
            'rejected', 'cancelled' => 'canceled',
            default => $sale->status,
        };

        if ($orderStatus !== $sale->status) {
            $sale->update(['status' => $orderStatus]);
        }
    }

    /**
     * Retorna pagamento de uma venda
     */
    public function getSalePayment(int $saleId): array
    {
        $payment = $this->paymentRepository->getBySaleId($saleId);

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Pagamento não encontrado',
            ];
        }

        return [
            'success' => true,
            'payment' => $payment,
        ];
    }
}
