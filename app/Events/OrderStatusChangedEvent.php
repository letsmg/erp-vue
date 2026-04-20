<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Sale $order,
        public readonly string $oldStatus,
        public readonly string $newStatus
    ) {}

    public function toKafkaPayload(): array
    {
        return [
            'event_type' => 'order_status_changed',
            'event_timestamp' => now()->toISOString(),
            'order_id' => $this->order->id,
            'client_id' => $this->order->client_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'total_amount' => (float) $this->order->total_amount,
        ];
    }
}
