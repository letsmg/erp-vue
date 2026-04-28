<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Cria um PaymentIntent no Stripe
     */
    public function createPaymentIntent($sale, array $paymentData): ?array
    {
        try {
            $items = $sale->items->map(function ($item) {
                return [
                    'name' => $item->product_description,
                    'quantity' => $item->quantity,
                    'amount' => (int) ($item->unit_price * 100), // Stripe usa centavos
                    'currency' => 'brl',
                ];
            })->toArray();

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($sale->total_amount * 100), // Stripe usa centavos
                'currency' => 'brl',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'sale_id' => $sale->id,
                    'user_id' => $sale->client_id,
                ],
                'description' => "Pedido #{$sale->id}",
            ]);

            Log::info('PaymentIntent Stripe criado', [
                'sale_id' => $sale->id,
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $sale->total_amount,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao criar PaymentIntent Stripe', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Retorna a chave pública do Stripe
     */
    public function getPublishableKey(): string
    {
        return config('services.stripe.publishable_key');
    }
}
