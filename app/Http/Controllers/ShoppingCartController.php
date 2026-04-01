<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShoppingCartRequest;
use App\Models\ShoppingCart;
use App\Policies\ShoppingCartPolicy;
use App\Services\ShoppingCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function __construct(
        private readonly ShoppingCartService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ShoppingCart::class);
        
        $userId = auth()->id();
        $cart = $this->service->getCart($userId);
        
        return $this->success($cart, 'Carrinho carregado com sucesso.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShoppingCartRequest $request): JsonResponse
    {
        $this->authorize('create', ShoppingCart::class);
        
        $productId = $request->input('product_id');
        $this->authorize('addProduct', ShoppingCart::class, $productId);
        
        $quantity = $request->input('quantity', 1);
        $userId = auth()->id();
        
        $result = $this->service->addToCart($userId, $productId, $quantity);
        
        if ($result['success']) {
            return $this->created($result, $result['message']);
        } else {
            return $this->error($result['message']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShoppingCartRequest $request, ShoppingCart $cartItem): JsonResponse
    {
        $this->authorize('update', $cartItem);
        
        $quantity = $request->input('quantity');
        $userId = auth()->id();
        
        $result = $this->service->updateQuantity($userId, $cartItem->id, $quantity);
        
        if ($result['success']) {
            return $this->success($result, $result['message']);
        } else {
            return $this->error($result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShoppingCart $cartItem): JsonResponse
    {
        $this->authorize('delete', $cartItem);
        
        $userId = auth()->id();
        $result = $this->service->removeFromCart($userId, $cartItem->id);
        
        return $this->success($result, $result['message']);
    }

    /**
     * Clear shopping cart
     */
    public function clear(Request $request): JsonResponse
    {
        $this->authorize('clear', ShoppingCart::class);
        
        $userId = auth()->id();
        $result = $this->service->clearCart($userId);
        
        return $this->success($result, $result['message']);
    }

    /**
     * Calculate shipping
     */
    public function shipping(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ShoppingCart::class);
        
        $userId = auth()->id();
        $addressId = $request->input('address_id');
        
        $result = $this->service->calculateShipping($userId, $addressId);
        
        if ($result['success']) {
            return $this->success($result, 'Cálculo de frete realizado.');
        } else {
            return $this->error($result['message']);
        }
    }

    /**
     * Prepare checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $this->authorize('checkout', ShoppingCart::class);
        
        $userId = auth()->id();
        $result = $this->service->prepareCheckoutData($userId);
        
        if ($result['success']) {
            return $this->success($result, 'Checkout preparado com sucesso.');
        } else {
            return $this->error($result['message'], $result);
        }
    }

    /**
     * Get cart summary
     */
    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ShoppingCart::class);
        
        $userId = auth()->id();
        $cart = $this->service->getCart($userId);
        
        $summary = [
            'total_items' => $cart['count'],
            'total_value' => $cart['total'],
            'formatted_total' => $cart['formatted_total'],
            'items_count' => $cart['items']->count(),
        ];
        
        return $this->success($summary, 'Resumo do carrinho carregado.');
    }
}
