<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Payment $payment
    ) {}

    public function toKafkaPayload(): array
    {
        return [
            'event_type' => 'payment_created',
            'event_timestamp' => now()->toISOString(),
            'payment_id' => $this->payment->id,
            'sale_id' => $this->payment->sale_id,
            'preference_id' => $this->payment->preference_id,
            'payment_type' => $this->payment->payment_type,
            'status' => $this->payment->status,
            'transaction_amount' => (float) $this->payment->transaction_amount,
            'external_reference' => $this->payment->external_reference,
        ];
    }
}
