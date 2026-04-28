<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
        private readonly StripeService $stripeService
    ) {}

    /**
     * Cria pedido a partir do carrinho
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $userId = auth()->id();
        $addressId = $request->input('address_id');

        $result = $this->orderService->createOrderFromCart($userId, $addressId);

        if ($result['success']) {
            return $this->created($result['sale'], $result['message']);
        }

        return $this->error($result['message'], 400);
    }

    /**
     * Cria PaymentIntent no Stripe
     */
    public function createPaymentIntent(Request $request, int $saleId): JsonResponse
    {
        $userId = auth()->id();

        // Verifica se o usuário pode acessar o pedido
        $orderResult = $this->orderService->getOrderDetails($saleId, $userId);
        if (!$orderResult['success']) {
            return $this->error($orderResult['message'], 403);
        }

        $sale = $orderResult['sale'];

        // Criar PaymentIntent no Stripe
        $paymentData = $this->stripeService->createPaymentIntent($sale, []);

        if (!$paymentData) {
            return $this->error('Erro ao criar intenção de pagamento', 500);
        }

        // Criar registro de pagamento
        $paymentResult = $this->paymentService->createPaymentPreference($saleId, [
            'preference_id' => $paymentData['payment_intent_id'],
            'payment_type' => 'stripe',
            'metadata' => [
                'user_id' => $userId,
                'sale_id' => $saleId,
                'client_secret' => $paymentData['client_secret'],
            ],
        ]);

        if ($paymentResult['success']) {
            return $this->success([
                'client_secret' => $paymentData['client_secret'],
                'payment_intent_id' => $paymentData['payment_intent_id'],
                'publishable_key' => $this->stripeService->getPublishableKey(),
                'amount' => $paymentData['amount'],
            ], 'Intenção de pagamento criada');
        }

        return $this->error($paymentResult['message'], 500);
    }

    /**
     * Webhook do Mercado Pago (legado - mantido para compatibilidade)
     */
    public function webhook(Request $request): JsonResponse
    {
        // Webhook agora é tratado pelo StripeWebhookController
        return response()->json(['status' => 'use_stripe_webhook'], 200);
    }

    /**
     * Cria preferência no Mercado Pago
     */
    private function createMercadoPagoPreference($sale, string $paymentType): ?array
    {
        try {
            $accessToken = config('services.mercadopago.access_token');
            $apiUrl = config('services.mercadopago.api_url', 'https://api.mercadopago.com');

            $items = $sale->items->map(function ($item) {
                return [
                    'title' => $item->product_description,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ];
            })->toArray();

            $preferenceData = [
                'items' => $items,
                'back_urls' => [
                    'success' => route('checkout.success', ['saleId' => $sale->id]),
                    'failure' => route('checkout.failure', ['saleId' => $sale->id]),
                    'pending' => route('checkout.pending', ['saleId' => $sale->id]),
                ],
                'auto_return' => 'approved',
                'external_reference' => $sale->id,
                'payment_methods' => [
                    'excluded_payment_types' => [
                        ['id' => 'ticket'],
                    ],
                ],
            ];

            if ($paymentType === 'credit_card') {
                $preferenceData['payment_methods']['excluded_payment_types'] = [
                    ['id' => 'ticket'],
                    ['id' => 'atm'],
                ];
            } elseif ($paymentType === 'pix') {
                $preferenceData['payment_methods']['excluded_payment_types'] = [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'ticket'],
                ];
            } elseif ($paymentType === 'boleto') {
                $preferenceData['payment_methods']['excluded_payment_types'] = [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'atm'],
                ];
            }

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$apiUrl}/checkout/preferences", $preferenceData);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Página de sucesso
     */
    public function checkoutSuccess(Request $request, int $saleId)
    {
        return inertia('Checkout/Success', ['sale_id' => $saleId]);
    }

    /**
     * Página de falha
     */
    public function failure(Request $request, int $saleId)
    {
        return inertia('Checkout/Failure', ['sale_id' => $saleId]);
    }

    /**
     * Página de pendente
     */
    public function pending(Request $request, int $saleId)
    {
        return inertia('Checkout/Pending', ['sale_id' => $saleId]);
    }
}
