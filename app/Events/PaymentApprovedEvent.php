<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentApprovedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
        public readonly string $oldStatus
    ) {}

    public function toKafkaPayload(): array
    {
        return [
            'event_type' => 'payment_approved',
            'event_timestamp' => now()->toISOString(),
            'payment_id' => $this->payment->id,
            'sale_id' => $this->payment->sale_id,
            'payment_id_mp' => $this->payment->payment_id,
            'payment_type' => $this->payment->payment_type,
            'old_status' => $this->oldStatus,
            'new_status' => $this->payment->status,
            'transaction_amount' => (float) $this->payment->transaction_amount,
            'net_amount' => (float) $this->payment->net_amount,
            'card_last_four' => $this->payment->card_last_four,
            'card_brand' => $this->payment->card_brand,
        ];
    }
}
