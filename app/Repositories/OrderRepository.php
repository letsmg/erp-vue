<?php

namespace App\Repositories;

use App\Helpers\SanitizerHelper;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShoppingCart;
use Illuminate\Support\Collection;

class OrderRepository
{
    /**
     * Cria um novo pedido a partir do carrinho
     */
    public function createFromCart(int $userId, int $addressId, ?int $sellerId = null): Sale
    {
        $cartItems = ShoppingCart::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            throw new \Exception('Carrinho vazio');
        }

        $totalAmount = $cartItems->sum('total_price');

        $saleData = [
            'client_id' => $this->getClientId($userId),
            'user_id' => $sellerId,
            'address_id' => $addressId,
            'total_amount' => $totalAmount,
            'sale_date' => now(),
            'status' => 'pending',
        ];

        $sanitizedData = SanitizerHelper::sanitize($saleData);
        $sale = Sale::create($sanitizedData);

        // Criar itens do pedido
        foreach ($cartItems as $cartItem) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $cartItem->product_id,
                'product_description' => $cartItem->product->title ?? $cartItem->product->description,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'subtotal' => $cartItem->total_price,
            ]);

            // Atualizar estoque
            $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
        }

        // Limpar carrinho
        ShoppingCart::where('user_id', $userId)->delete();

        return $sale->load('items');
    }

    /**
     * Retorna pedidos de um cliente
     */
    public function getByClient(int $clientId): Collection
    {
        return Sale::with(['items', 'items.product', 'payment', 'address'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Retorna um pedido por ID com relacionamentos
     */
    public function findById(int $id): ?Sale
    {
        return Sale::with(['items', 'items.product', 'payment', 'payment.sale', 'address', 'client'])
            ->find($id);
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus(int $saleId, string $status): bool
    {
        $sale = Sale::find($saleId);
        if (!$sale) {
            return false;
        }

        return $sale->update(['status' => $status]);
    }

    /**
     * Cancela pedido e restaura estoque
     */
    public function cancelOrder(int $saleId): bool
    {
        $sale = Sale::with('items')->find($saleId);
        if (!$sale) {
            return false;
        }

        if (!$sale->isPending()) {
            throw new \Exception('Apenas pedidos pendentes podem ser cancelados');
        }

        // Restaurar estoque
        foreach ($sale->items as $item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        return $sale->update(['status' => 'canceled']);
    }

    /**
     * Retorna pedidos para admin (com filtros)
     */
    public function getAllForAdmin(array $filters = []): Collection
    {
        $query = Sale::with(['items', 'items.product', 'payment', 'client', 'address']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('sale_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('sale_date', '<=', $filters['date_to']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Obtém client_id a partir de user_id
     */
    private function getClientId(int $userId): ?int
    {
        $client = \App\Models\Client::where('user_id', $userId)->first();
        return $client ? $client->id : null;
    }
}
