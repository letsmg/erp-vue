<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Processa webhooks do Stripe
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            // Verifica a assinatura do webhook
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook payload inválido', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payload inválido'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook assinatura inválida', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Assinatura inválida'], 400);
        }

        Log::info('Stripe webhook recebido', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        // Processa o evento baseado no tipo
        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            'charge.refunded' => $this->handleRefund($event->data->object),
            default => Log::info('Evento Stripe não tratado', ['type' => $event->type]),
        };

        return response()->json(['status' => 'success']);
    }

    /**
     * Processa pagamento bem-sucedido
     */
    private function handlePaymentSucceeded($paymentIntent): void
    {
        $saleId = $paymentIntent->metadata->sale_id ?? null;

        if (!$saleId) {
            Log::warning('PaymentIntent sem sale_id', ['payment_intent' => $paymentIntent->id]);
            return;
        }

        $result = $this->paymentService->updatePaymentStatus(
            (int) $saleId,
            'approved',
            $paymentIntent->status
        );

        if ($result['success']) {
            Log::info('Pagamento Stripe aprovado', [
                'sale_id' => $saleId,
                'payment_intent' => $paymentIntent->id,
            ]);
        } else {
            Log::error('Falha ao atualizar pagamento Stripe', [
                'sale_id' => $saleId,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * Processa pagamento falho
     */
    private function handlePaymentFailed($paymentIntent): void
    {
        $saleId = $paymentIntent->metadata->sale_id ?? null;

        if (!$saleId) {
            Log::warning('PaymentIntent falho sem sale_id', ['payment_intent' => $paymentIntent->id]);
            return;
        }

        $result = $this->paymentService->updatePaymentStatus(
            (int) $saleId,
            'rejected',
            $paymentIntent->last_payment_error?->message ?? 'Pagamento falhou'
        );

        Log::info('Pagamento Stripe rejeitado', [
            'sale_id' => $saleId,
            'payment_intent' => $paymentIntent->id,
        ]);
    }

    /**
     * Processa reembolso
     */
    private function handleRefund($charge): void
    {
        $saleId = $charge->metadata->sale_id ?? null;

        if (!$saleId) {
            Log::warning('Charge sem sale_id no reembolso', ['charge' => $charge->id]);
            return;
        }

        $result = $this->paymentService->updatePaymentStatus(
            (int) $saleId,
            'refunded',
            'Reembolso processado'
        );

        Log::info('Reembolso Stripe processado', [
            'sale_id' => $saleId,
            'charge' => $charge->id,
        ]);
    }
}
