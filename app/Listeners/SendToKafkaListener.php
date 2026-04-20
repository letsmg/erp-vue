<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Events\PaymentCreatedEvent;
use App\Events\PaymentApprovedEvent;
use App\Events\PaymentFailedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendToKafkaListener
{
    /**
     * Envia evento para Kafka de forma não bloqueante
     */
    public function handle($event): void
    {
        try {
            $payload = method_exists($event, 'toKafkaPayload') 
                ? $event->toKafkaPayload() 
                : ['event_type' => get_class($event), 'data' => serialize($event)];

            $this->sendToKafka($payload);
        } catch (\Exception $e) {
            // Falha no Kafka não deve interromper o fluxo principal
            Log::error('Kafka listener error: ' . $e->getMessage(), [
                'event' => get_class($event),
                'payload' => $payload ?? [],
            ]);
        }
    }

    /**
     * Envia dados para Kafka
     */
    private function sendToKafka(array $payload): void
    {
        $brokers = config('services.kafka.brokers');
        $topic = config('services.kafka.topic');

        if (!$brokers || !$topic) {
            Log::warning('Kafka not configured');
            return;
        }

        // Usar HTTP para enviar ao Kafka (via proxy se necessário)
        // ou usar biblioteca PHP-Kafka se disponível
        $this->sendViaHttp($payload, $topic);
    }

    /**
     * Envia via HTTP para Kafka (simplificado - pode usar biblioteca real)
     */
    private function sendViaHttp(array $payload, string $topic): void
    {
        $kafkaUrl = env('KAFKA_HTTP_URL');

        if (!$kafkaUrl) {
            Log::warning('Kafka HTTP URL not configured');
            return;
        }

        try {
            Http::timeout(2)
                ->post($kafkaUrl, [
                    'topic' => $topic,
                    'payload' => $payload,
                ]);
        } catch (\Exception $e) {
            // Falha silenciosa - não interrompe o fluxo principal
            Log::error('Failed to send to Kafka via HTTP: ' . $e->getMessage());
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): void
    {
        $events->listen(
            [OrderCreatedEvent::class, OrderStatusChangedEvent::class, 
             PaymentCreatedEvent::class, PaymentApprovedEvent::class, 
             PaymentFailedEvent::class],
            SendToKafkaListener::class
        );
    }
}
