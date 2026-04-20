<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly PaymentRepository $paymentRepository
    ) {}

    /**
     * Cria pedido a partir do carrinho
     */
    public function createOrderFromCart(int $userId, int $addressId, ?int $sellerId = null): array
    {
        try {
            DB::beginTransaction();

            $sale = $this->orderRepository->createFromCart($userId, $addressId, $sellerId);

            // Disparar evento para Kafka
            event(new OrderCreatedEvent($sale));

            DB::commit();

            return [
                'success' => true,
                'sale' => $sale,
                'message' => 'Pedido criado com sucesso',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Erro ao criar pedido: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retorna pedidos de um cliente
     */
    public function getClientOrders(int $userId): array
    {
        $clientId = $this->getClientId($userId);

        if (!$clientId) {
            return [
                'success' => false,
                'message' => 'Cliente não encontrado',
            ];
        }

        $orders = $this->orderRepository->getByClient($clientId);

        return [
            'success' => true,
            'orders' => $orders,
        ];
    }

    /**
     * Retorna detalhes de um pedido
     */
    public function getOrderDetails(int $orderId, int $userId): array
    {
        $sale = $this->orderRepository->findById($orderId);

        if (!$sale) {
            return [
                'success' => false,
                'message' => 'Pedido não encontrado',
            ];
        }

        // Verifica se o usuário tem acesso ao pedido
        if (!$this->userCanAccessOrder($sale, $userId)) {
            return [
                'success' => false,
                'message' => 'Acesso negado',
            ];
        }

        return [
            'success' => true,
            'sale' => $sale,
        ];
    }

    /**
     * Atualiza status do pedido
     */
    public function updateOrderStatus(int $orderId, string $status): array
    {
        try {
            $sale = $this->orderRepository->findById($orderId);

            if (!$sale) {
                return [
                    'success' => false,
                    'message' => 'Pedido não encontrado',
                ];
            }

            $oldStatus = $sale->status;
            $updated = $this->orderRepository->updateStatus($orderId, $status);

            if ($updated) {
                // Disparar evento para Kafka
                event(new OrderStatusChangedEvent($sale, $oldStatus, $status));

                return [
                    'success' => true,
                    'message' => 'Status atualizado com sucesso',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao atualizar status',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancela pedido
     */
    public function cancelOrder(int $orderId, int $userId): array
    {
        try {
            $sale = $this->orderRepository->findById($orderId);

            if (!$sale) {
                return [
                    'success' => false,
                    'message' => 'Pedido não encontrado',
                ];
            }

            // Verifica se o usuário tem acesso ao pedido
            if (!$this->userCanAccessOrder($sale, $userId)) {
                return [
                    'success' => false,
                    'message' => 'Acesso negado',
                ];
            }

            $oldStatus = $sale->status;
            $canceled = $this->orderRepository->cancelOrder($orderId);

            if ($canceled) {
                // Disparar evento para Kafka
                event(new OrderStatusChangedEvent($sale, $oldStatus, 'canceled'));

                return [
                    'success' => true,
                    'message' => 'Pedido cancelado com sucesso',
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao cancelar pedido',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao cancelar pedido: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retorna pedidos para admin
     */
    public function getAdminOrders(array $filters = []): array
    {
        $orders = $this->orderRepository->getAllForAdmin($filters);

        return [
            'success' => true,
            'orders' => $orders,
        ];
    }

    /**
     * Verifica se usuário pode acessar o pedido
     */
    private function userCanAccessOrder(Sale $sale, int $userId): bool
    {
        // Admin e operador podem acessar qualquer pedido
        $user = \App\Models\User::find($userId);
        if ($user && in_array($user->access_level, [0, 1])) {
            return true;
        }

        // Cliente só pode acessar seus próprios pedidos
        $clientId = $this->getClientId($userId);
        return $sale->client_id === $clientId;
    }

    /**
     * Obtém client_id a partir de user_id
     */
    private function getClientId(int $userId): ?int
    {
        $client = \App\Models\Client::where('user_id', $userId)->first();
        return $client ? $client->id : null;
    }
}
