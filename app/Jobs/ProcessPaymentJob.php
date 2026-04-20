<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int $paymentId,
        public readonly ?string $paymentMpId = null
    ) {
        $this->onQueue('payments');
    }

    public function handle(PaymentService $paymentService): void
    {
        try {
            $payment = Payment::with('sale')->find($this->paymentId);

            if (!$payment) {
                Log::error("Payment not found: {$this->paymentId}");
                return;
            }

            // Se já temos o payment_id do Mercado Pago, buscar dados atualizados
            if ($this->paymentMpId) {
                $mpData = $this->fetchPaymentFromMercadoPago($this->paymentMpId);

                if ($mpData) {
                    $paymentService->updateWithMercadoPagoData($this->paymentId, $mpData);
                    Log::info("Payment {$this->paymentId} updated from Mercado Pago");
                }
            } elseif ($payment->payment_id) {
                // Se o pagamento já tem payment_id, buscar dados atualizados
                $mpData = $this->fetchPaymentFromMercadoPago($payment->payment_id);

                if ($mpData) {
                    $paymentService->updateWithMercadoPagoData($this->paymentId, $mpData);
                    Log::info("Payment {$this->paymentId} updated from Mercado Pago");
                }
            } else {
                Log::info("Payment {$this->paymentId} waiting for Mercado Pago ID");
            }
        } catch (\Exception $e) {
            Log::error("Error processing payment {$this->paymentId}: " . $e->getMessage());

            if ($this->attempts() >= $this->tries) {
                $paymentService->updatePaymentStatus($this->paymentId, 'rejected', 'processing_failed');
            }

            throw $e;
        }
    }

    /**
     * Busca dados do pagamento no Mercado Pago
     */
    private function fetchPaymentFromMercadoPago(string $paymentId): ?array
    {
        try {
            $accessToken = config('services.mercadopago.access_token');
            $apiUrl = config('services.mercadopago.api_url', 'https://api.mercadopago.com');

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get("{$apiUrl}/v1/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Mercado Pago API error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching payment from Mercado Pago: " . $e->getMessage());
            return null;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Payment job failed for payment {$this->paymentId}: " . $exception->getMessage());
    }
}
