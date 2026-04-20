<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',
        'payment_id',
        'preference_id',
        'payment_type',
        'status',
        'status_detail',
        'transaction_amount',
        'net_amount',
        'tax_amount',
        'cardholder_name',
        'card_last_four',
        'card_first_six',
        'card_brand',
        'pix_code',
        'pix_expiration_date',
        'barcode',
        'due_date',
        'metadata',
        'external_reference',
        'date_approved',
        'date_created',
        'date_last_updated',
        'payment_method_created_at',
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'metadata' => 'array',
        'pix_expiration_date' => 'datetime',
        'due_date' => 'datetime',
        'date_approved' => 'datetime',
        'date_created' => 'datetime',
        'date_last_updated' => 'datetime',
        'payment_method_created_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isInProcess(): bool
    {
        return $this->status === 'in_process';
    }

    public function getFormattedAmount(): string
    {
        return 'R$ ' . number_format($this->transaction_amount, 2, ',', '.');
    }
}
