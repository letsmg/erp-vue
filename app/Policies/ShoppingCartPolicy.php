<?php

namespace App\Policies;

use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShoppingCartPolicy
{
    /**
     * Determine whether the user can view any shopping cart items.
     */
    public function viewAny(User $user): Response
    {
        // Usuário só pode ver seu próprio carrinho
        return Response::allow();
    }

    /**
     * Determine whether the user can view the shopping cart item.
     */
    public function view(User $user, ShoppingCart $cartItem): Response
    {
        // Usuário só pode ver seus próprios itens
        return $cartItem->user_id === $user->id
            ? Response::allow()
            : Response::deny('Você não tem permissão para visualizar este item do carrinho.');
    }

    /**
     * Determine whether the user can create shopping cart items.
     */
    public function create(User $user): Response
    {
        // Apenas clientes podem adicionar itens ao carrinho
        return $user->isClient()
            ? Response::allow()
            : Response::deny('Apenas clientes podem adicionar itens ao carrinho.');
    }

    /**
     * Determine whether the user can update the shopping cart item.
     */
    public function update(User $user, ShoppingCart $cartItem): Response
    {
        // Usuário só pode atualizar seus próprios itens
        return $cartItem->user_id === $user->id
            ? Response::allow()
            : Response::deny('Você não tem permissão para atualizar este item do carrinho.');
    }

    /**
     * Determine whether the user can delete the shopping cart item.
     */
    public function delete(User $user, ShoppingCart $cartItem): Response
    {
        // Usuário só pode remover seus próprios itens
        return $cartItem->user_id === $user->id
            ? Response::allow()
            : Response::deny('Você não tem permissão para remover este item do carrinho.');
    }

    /**
     * Determine whether the user can clear the shopping cart.
     */
    public function clear(User $user): Response
    {
        // Apenas clientes podem limpar seu carrinho
        return $user->isClient()
            ? Response::allow()
            : Response::deny('Apenas clientes podem limpar o carrinho.');
    }

    /**
     * Determine whether the user can checkout.
     */
    public function checkout(User $user): Response
    {
        // Apenas clientes podem fazer checkout
        return $user->isClient()
            ? Response::allow()
            : Response::deny('Apenas clientes podem finalizar compras.');
    }

    /**
     * Determine whether the user can add specific product to cart.
     */
    public function addProduct(User $user, ?int $productId = null): Response
    {
        // Se não há produto ID, permite
        if (!$productId) {
            return Response::allow();
        }

        // Verifica se o produto existe e está ativo
        $product = \App\Models\Product::find($productId);
        
        if (!$product) {
            return Response::deny('Produto não encontrado.');
        }

        if (!$product->is_active) {
            return Response::deny('Produto não está disponível.');
        }

        // Cliente pode adicionar produto ativo ao carrinho
        return $user->isClient()
            ? Response::allow()
            : Response::deny('Apenas clientes podem adicionar produtos ao carrinho.');
    }
}
