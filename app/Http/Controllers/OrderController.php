<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Lista pedidos do cliente autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $result = $this->orderService->getClientOrders($userId);

        if ($result['success']) {
            return $this->success($result['orders'], 'Pedidos carregados com sucesso');
        }

        return $this->error($result['message']);
    }

    /**
     * Detalhes de um pedido
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = auth()->id();
        $result = $this->orderService->getOrderDetails($id, $userId);

        if ($result['success']) {
            return $this->success($result['sale'], 'Detalhes do pedido carregados');
        }

        return $this->error($result['message'], 404);
    }

    /**
     * Cancela um pedido
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $userId = auth()->id();
        $result = $this->orderService->cancelOrder($id, $userId);

        if ($result['success']) {
            return $this->success(null, $result['message']);
        }

        return $this->error($result['message'], 400);
    }
}
