<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Repositories\ShoppingCartRepository;
use Illuminate\Support\Collection;

class ShoppingCartService
{
    public function __construct(private readonly ShoppingCartRepository $repository) {}

    /**
     * Adiciona produto ao carrinho
     */
    public function addToCart(int $userId, int $productId, int $quantity = 1): array
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Produto não encontrado',
            ];
        }

        if (!$product->is_active) {
            return [
                'success' => false,
                'message' => 'Produto não está disponível',
            ];
        }

        if ($product->stock_quantity < $quantity) {
            return [
                'success' => false,
                'message' => 'Estoque insuficiente',
                'available' => $product->stock_quantity,
            ];
        }

        $unitPrice = $product->sale_price ?? $product->cost_price;
        $cartItem = $this->repository->addItem($userId, $productId, $quantity, $unitPrice);

        return [
            'success' => true,
            'message' => 'Produto adicionado ao carrinho',
            'cart_item' => $cartItem,
            'cart_total' => $this->repository->getCartTotal($userId),
            'cart_count' => $this->repository->getCartItemCount($userId),
        ];
    }

    /**
     * Atualiza quantidade no carrinho
     */
    public function updateQuantity(int $userId, int $cartItemId, int $quantity): array
    {
        $cartItem = ShoppingCart::find($cartItemId);
        
        if (!$cartItem || $cartItem->user_id != $userId) {
            return [
                'success' => false,
                'message' => 'Item do carrinho não encontrado',
            ];
        }

        $product = $cartItem->product;
        
        if (!$product || !$product->is_active) {
            return [
                'success' => false,
                'message' => 'Produto não está disponível',
            ];
        }

        if ($product->stock_quantity < $quantity) {
            return [
                'success' => false,
                'message' => 'Estoque insuficiente',
                'available' => $product->stock_quantity,
            ];
        }

        $updatedItem = $this->repository->updateQuantity($cartItem, $quantity);

        return [
            'success' => true,
            'message' => 'Quantidade atualizada',
            'cart_item' => $updatedItem,
            'cart_total' => $this->repository->getCartTotal($userId),
            'cart_count' => $this->repository->getCartItemCount($userId),
        ];
    }

    /**
     * Remove item do carrinho
     */
    public function removeFromCart(int $userId, int $cartItemId): array
    {
        $cartItem = ShoppingCart::find($cartItemId);
        
        if (!$cartItem || $cartItem->user_id != $userId) {
            return [
                'success' => false,
                'message' => 'Item do carrinho não encontrado',
            ];
        }

        $this->repository->removeItem($cartItem);

        return [
            'success' => true,
            'message' => 'Item removido do carrinho',
            'cart_total' => $this->repository->getCartTotal($userId),
            'cart_count' => $this->repository->getCartItemCount($userId),
        ];
    }

    /**
     * Limpa carrinho
     */
    public function clearCart(int $userId): array
    {
        $this->repository->clearCart($userId);

        return [
            'success' => true,
            'message' => 'Carrinho limpo',
            'cart_total' => 0,
            'cart_count' => 0,
        ];
    }

    /**
     * Retorna carrinho completo
     */
    public function getCart(int $userId): array
    {
        // Remove produtos inativos e atualiza preços
        $this->repository->removeInactiveProducts($userId);
        $this->repository->updateCartPrices($userId);

        $items = $this->repository->getByUser($userId);
        $total = $this->repository->getCartTotal($userId);
        $count = $this->repository->getCartItemCount($userId);

        return [
            'items' => $items,
            'total' => $total,
            'count' => $count,
            'formatted_total' => 'R$ ' . number_format($total, 2, ',', '.'),
        ];
    }

    /**
     * Verifica disponibilidade de produtos no carrinho
     */
    public function validateCartForCheckout(int $userId): array
    {
        $items = $this->repository->getItemsForCheckout($userId);
        $issues = [];

        foreach ($items as $item) {
            if (!$item->product) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'message' => 'Produto não encontrado',
                ];
                continue;
            }

            if (!$item->product->is_active) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'message' => 'Produto "' . $item->product->description . '" não está disponível',
                ];
                continue;
            }

            if ($item->product->stock_quantity < $item->quantity) {
                $issues[] = [
                    'product_id' => $item->product_id,
                    'message' => 'Estoque insuficiente para "' . $item->product->description . '". Disponível: ' . $item->product->stock_quantity,
                ];
                continue;
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'items' => $items,
        ];
    }

    /**
     * Prepara dados para checkout
     */
    public function prepareCheckoutData(int $userId): array
    {
        $validation = $this->validateCartForCheckout($userId);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Itens do carrinho inválidos',
                'issues' => $validation['issues'],
            ];
        }

        $cart = $this->getCart($userId);
        
        return [
            'success' => true,
            'cart' => $cart,
            'items' => $validation['items'],
        ];
    }

    /**
     * Calcula frete (simulação - pode integrar com API real)
     */
    public function calculateShipping(int $userId, ?int $addressId = null): array
    {
        $cart = $this->getCart($userId);
        
        if ($cart['count'] === 0) {
            return [
                'success' => false,
                'message' => 'Carrinho vazio',
            ];
        }

        // Simulação de cálculo de frete
        $weight = $cart['items']->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });

        // Regras simples de frete (pode substituir por API real)
        $shippingCost = 0;
        if ($weight > 0) {
            if ($weight <= 1) {
                $shippingCost = 15.00;
            } elseif ($weight <= 5) {
                $shippingCost = 25.00;
            } elseif ($weight <= 10) {
                $shippingCost = 40.00;
            } else {
                $shippingCost = 60.00;
            }
        }

        return [
            'success' => true,
            'weight' => $weight,
            'cost' => $shippingCost,
            'formatted_cost' => 'R$ ' . number_format($shippingCost, 2, ',', '.'),
            'delivery_time' => $this->estimateDeliveryTime($weight),
        ];
    }

    /**
     * Estima tempo de entrega
     */
    private function estimateDeliveryTime(float $weight): string
    {
        if ($weight <= 1) return '1-2 dias úteis';
        if ($weight <= 5) return '2-3 dias úteis';
        if ($weight <= 10) return '3-5 dias úteis';
        return '5-7 dias úteis';
    }
}
