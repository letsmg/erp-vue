<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingCart extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o Usuário (Cliente).
     * Um item do carrinho pertence a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o Produto.
     * Um item do carrinho pertence a um produto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula o total formatado
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'R$ ' . number_format($this->total_price, 2, ',', '.');
    }

    /**
     * Calcula o preço unitário formatado
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_price, 2, ',', '.');
    }

    /**
     * Atualiza o total baseado na quantidade e preço unitário
     */
    public function updateTotal(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Escopo para itens de carrinho de um usuário específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Escopo para itens ativos (com produtos disponíveis)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('product', function ($productQuery) {
            $productQuery->where('is_active', true);
        });
    }
}
