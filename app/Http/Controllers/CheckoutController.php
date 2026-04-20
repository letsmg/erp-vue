<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService
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
     * Cria preferência de pagamento no Mercado Pago
     */
    public function createPaymentPreference(Request $request, int $saleId): JsonResponse
    {
        $request->validate([
            'payment_type' => 'required|in:credit_card,pix,boleto',
        ]);

        $userId = auth()->id();
        
        // Verifica se o usuário pode acessar o pedido
        $orderResult = $this->orderService->getOrderDetails($saleId, $userId);
        if (!$orderResult['success']) {
            return $this->error($orderResult['message'], 403);
        }

        $sale = $orderResult['sale'];

        // Criar preferência no Mercado Pago
        $preferenceData = $this->createMercadoPagoPreference($sale, $request->input('payment_type'));

        if (!$preferenceData) {
            return $this->error('Erro ao criar preferência de pagamento', 500);
        }

        // Criar registro de pagamento
        $paymentResult = $this->paymentService->createPaymentPreference($saleId, [
            'preference_id' => $preferenceData['id'],
            'payment_type' => $request->input('payment_type'),
            'metadata' => [
                'user_id' => $userId,
                'sale_id' => $saleId,
            ],
        ]);

        if ($paymentResult['success']) {
            return $this->success([
                'preference_id' => $preferenceData['id'],
                'init_point' => $preferenceData['init_point'],
                'sandbox_init_point' => $preferenceData['sandbox_init_point'],
            ], 'Preferência de pagamento criada');
        }

        return $this->error($paymentResult['message'], 500);
    }

    /**
     * Webhook do Mercado Pago
     */
    public function webhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->processWebhook($request->all());

        if ($result['success']) {
            return response()->json(['status' => 'processed'], 200);
        }

        return response()->json(['status' => 'error'], 400);
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
