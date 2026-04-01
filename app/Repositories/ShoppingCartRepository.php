<?php

namespace App\Repositories;

use App\Helpers\SanitizerHelper;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Support\Collection;

class ShoppingCartRepository
{
    /**
     * Retorna itens do carrinho de um usuário
     */
    public function getByUser(int $userId): Collection
    {
        return ShoppingCart::with(['product' => function ($query) {
            $query->where('is_active', true);
        }, 'product.images'])
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Adiciona item ao carrinho
     */
    public function addItem(int $userId, int $productId, int $quantity, float $unitPrice): ShoppingCart
    {
        // Verifica se item já existe no carrinho
        $existingItem = ShoppingCart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            // Atualiza quantidade
            $newQuantity = $existingItem->quantity + $quantity;
            $existingItem->update([
                'quantity' => $newQuantity,
                'total_price' => $newQuantity * $unitPrice,
            ]);
            return $existingItem->fresh();
        }

        // Cria novo item
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
        ];

        $sanitizedData = SanitizerHelper::sanitize($data);
        return ShoppingCart::create($sanitizedData);
    }

    /**
     * Atualiza quantidade de um item
     */
    public function updateQuantity(ShoppingCart $cartItem, int $quantity): ShoppingCart
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItem);
        }

        $cartItem->update([
            'quantity' => $quantity,
            'total_price' => $quantity * $cartItem->unit_price,
        ]);

        return $cartItem->fresh();
    }

    /**
     * Remove item do carrinho
     */
    public function removeItem(ShoppingCart $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Limpa carrinho de um usuário
     */
    public function clearCart(int $userId): int
    {
        return ShoppingCart::where('user_id', $userId)->delete();
    }

    /**
     * Calcula total do carrinho
     */
    public function getCartTotal(int $userId): float
    {
        return ShoppingCart::where('user_id', $userId)
            ->sum('total_price');
    }

    /**
     * Retorna quantidade de itens no carrinho
     */
    public function getCartItemCount(int $userId): int
    {
        return ShoppingCart::where('user_id', $userId)
            ->sum('quantity');
    }

    /**
     * Verifica se produto está no carrinho
     */
    public function isProductInCart(int $userId, int $productId): bool
    {
        return ShoppingCart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Atualiza preços do carrinho (caso produtos tenham mudado de preço)
     */
    public function updateCartPrices(int $userId): int
    {
        $cartItems = ShoppingCart::with('product')->where('user_id', $userId)->get();
        $updatedCount = 0;

        foreach ($cartItems as $item) {
            if ($item->product) {
                $currentPrice = $item->product->sale_price ?? $item->product->cost_price;
                
                if ($currentPrice != $item->unit_price) {
                    $item->update([
                        'unit_price' => $currentPrice,
                        'total_price' => $currentPrice * $item->quantity,
                    ]);
                    $updatedCount++;
                }
            }
        }

        return $updatedCount;
    }

    /**
     * Remove itens com produtos inativos
     */
    public function removeInactiveProducts(int $userId): int
    {
        return ShoppingCart::where('user_id', $userId)
            ->whereHas('product', function ($query) {
                $query->where('is_active', false);
            })
            ->delete();
    }

    /**
     * Retorna itens para checkout (com produtos válidos)
     */
    public function getItemsForCheckout(int $userId): Collection
    {
        return ShoppingCart::with(['product' => function ($query) {
                $query->where('is_active', true)
                    ->with(['supplier', 'category']);
            }, 'product.images'])
            ->where('user_id', $userId)
            ->get();
    }
}
