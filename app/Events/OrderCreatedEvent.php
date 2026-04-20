<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Sale $order
    ) {}

    public function toKafkaPayload(): array
    {
        return [
            'event_type' => 'order_created',
            'event_timestamp' => now()->toISOString(),
            'order_id' => $this->order->id,
            'client_id' => $this->order->client_id,
            'total_amount' => (float) $this->order->total_amount,
            'status' => $this->order->status,
            'address_id' => $this->order->address_id,
            'items_count' => $this->order->items->count(),
            'items' => $this->order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_description' => $item->product_description,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->toArray(),
        ];
    }
}
