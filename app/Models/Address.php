<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     */
    protected $fillable = [
        'client_id',
        'zip_code',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'complement',
        'is_delivery_address',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'is_delivery_address' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o Cliente.
     * Um endereço pertence a um cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Retorna o endereço completo formatado
     */
    public function getFullAddressAttribute(): string
    {
        $address = "{$this->street}, {$this->number}";
        
        if ($this->complement) {
            $address .= " - {$this->complement}";
        }
        
        $address .= ", {$this->neighborhood}";
        $address .= ", {$this->city}/{$this->state}";
        $address .= " - CEP: {$this->zip_code}";
        
        return $address;
    }

    /**
     * Escopo para endereços de entrega
     */
    public function scopeDeliveryAddresses($query)
    {
        return $query->where('is_delivery_address', true);
    }

    /**
     * Escopo para endereço principal de entrega
     */
    public function scopeMainDelivery($query)
    {
        return $query->where('is_delivery_address', true)->first();
    }
}
