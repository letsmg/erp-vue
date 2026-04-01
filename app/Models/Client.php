<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     */
    protected $fillable = [
        'user_id',
        'name',
        'document_number',
        'document_type', // CPF ou CNPJ
        'phone',
        'phone1',
        'contact1',
        'phone2',
        'contact2',
        'state_registration', // Inscrição Estadual
        'municipal_registration', // Inscrição Municipal
        'contributor_type', // Contribuinte: 1=Contribuinte ICMS, 2=Contribuinte Isento, 9=Não Contribuinte
        'is_active',
    ];

    /**
     * Relacionamento com o Usuário (Login).
     * Um cliente pertence a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o Endereço.
     * Um cliente possui vários endereços.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Relacionamento com as Vendas.
     * Um cliente pode ter várias vendas.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relacionamento com o Carrinho de Compras.
     * Um cliente pode ter vários itens no carrinho (através da tabela users).
     */
    public function shoppingCartItems(): HasMany
    {
        return $this->hasMany(ShoppingCart::class, 'user_id', 'user_id');
    }

    /**
     * Retorna o endereço de entrega principal
     */
    public function getDeliveryAddressAttribute()
    {
        return $this->addresses()->where('is_delivery_address', true)->first();
    }

    /**
     * Verifica se o documento é CPF
     */
    public function isCPF(): bool
    {
        return $this->document_type === 'CPF' && strlen($this->document_number) === 11;
    }

    /**
     * Verifica se o documento é CNPJ
     */
    public function isCNPJ(): bool
    {
        return $this->document_type === 'CNPJ' && strlen($this->document_number) === 14;
    }

    /**
     * Formata o documento com máscara
     */
    public function getFormattedDocumentAttribute(): string
    {
        if ($this->isCPF()) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->document_number);
        }
        
        if ($this->isCNPJ()) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->document_number);
        }
        
        return $this->document_number;
    }

    /**
     * Retorna o tipo de contribuinte formatado
     */
    public function getContributorTypeDescriptionAttribute(): string
    {
        return match($this->contributor_type) {
            1 => 'Contribuinte ICMS',
            2 => 'Contribuinte Isento',
            9 => 'Não Contribuinte',
            default => 'Não definido'
        };
    }

    /**
     * Verifica se o cliente é contribuinte de ICMS
     */
    public function isICMSContributor(): bool
    {
        return $this->contributor_type === 1;
    }

    /**
     * Verifica se o cliente é isento de ICMS
     */
    public function isICMSExempt(): bool
    {
        return $this->contributor_type === 2;
    }

    /**
     * Verifica se o cliente não é contribuinte
     */
    public function isNonContributor(): bool
    {
        return $this->contributor_type === 9;
    }
}