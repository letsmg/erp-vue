<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Sale;
use Carbon\Carbon;

class SelfClientService
{
    /**
     * Verifica se o cliente tem compras nos últimos X anos.
     */
    public function hasRecentPurchases(int $clientId, int $years = 5): bool
    {
        $dateLimit = Carbon::now()->subYears($years);
        
        return Sale::where('client_id', $clientId)
            ->where('created_at', '>=', $dateLimit)
            ->exists();
    }

    /**
     * Obtém o histórico de compras do cliente.
     */
    public function getPurchaseHistory(int $clientId, int $limit = 10)
    {
        return Sale::where('client_id', $clientId)
            ->with(['items.product', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtém estatísticas do cliente.
     */
    public function getClientStats(int $clientId): array
    {
        $client = Client::findOrFail($clientId);
        
        $totalPurchases = Sale::where('client_id', $clientId)->count();
        $totalSpent = Sale::where('client_id', $clientId)->sum('total_value');
        $lastPurchase = Sale::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        return [
            'total_purchases' => $totalPurchases,
            'total_spent' => $totalSpent,
            'last_purchase_date' => $lastPurchase?->created_at,
            'account_age' => $client->created_at->diffForHumans(),
            'can_delete_account' => !$this->hasRecentPurchases($clientId, 5),
        ];
    }

    /**
     * Verifica se o cliente pode excluir a conta.
     */
    public function canDeleteAccount(int $clientId): bool
    {
        return !$this->hasRecentPurchases($clientId, 5);
    }

    /**
     * Prepara dados do cliente para exibição.
     */
    public function prepareClientData(Client $client): array
    {
        return [
            'id' => $client->id,
            'display_name' => $client->display_name,
            'document_number' => $client->formatted_document,
            'document_type' => $client->contributor_type_description,
            'email' => $client->decrypted_email,
            'phone1' => $client->phone1,
            'phone2' => $client->phone2,
            'is_active' => $client->is_active,
            'addresses' => $client->addresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'street' => $address->street,
                    'number' => $address->number,
                    'complement' => $address->complement,
                    'neighborhood' => $address->neighborhood,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip_code' => $address->zip_code,
                    'is_delivery_address' => $address->is_delivery_address,
                ];
            }),
            'stats' => $this->getClientStats($client->id),
        ];
    }
}
