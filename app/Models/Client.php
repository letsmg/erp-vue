<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'phone',
        'phone1',
        'contact1',
        'phone2',
        'contact2',
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
     * Um cliente possui um endereço.
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    /**
     * Relacionamento com as Vendas.
     * Um cliente pode ter várias vendas.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}