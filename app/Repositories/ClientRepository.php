<?php

namespace App\Repositories;

use App\Helpers\SanitizerHelper;
use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository
{
    /**
     * Retorna clientes paginados com filtros
     */
    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = Client::with(['user', 'addresses'])
            ->orderBy('name');

        // Filtro por nome
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $searchTerm = "%{$search}%";
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('document_number', 'like', $searchTerm)
                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('email', 'like', $searchTerm);
                  });
            });
        }

        // Filtro por tipo de documento
        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        // Filtro por status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filtro por tipo de contribuinte
        if (!empty($filters['contributor_type'])) {
            $query->where('contributor_type', $filters['contributor_type']);
        }

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Busca cliente por ID com relacionamentos
     */
    public function findById(int $id): ?Client
    {
        return Client::with(['user', 'addresses' => function ($query) {
            $query->orderBy('is_delivery_address', 'desc');
        }])->find($id);
    }

    /**
     * Busca cliente por documento
     */
    public function findByDocument(string $document): ?Client
    {
        return Client::where('document_number', $document)->first();
    }

    /**
     * Busca cliente por usuário
     */
    public function findByUserId(int $userId): ?Client
    {
        return Client::where('user_id', $userId)->first();
    }

    /**
     * Cria novo cliente
     */
    public function create(array $data): Client
    {
        $data = SanitizerHelper::sanitize($data);
        return Client::create($data);
    }

    /**
     * Atualiza cliente
     */
    public function update(Client $client, array $data): Client
    {
        $data = SanitizerHelper::sanitize($data);
        $client->update($data);
        return $client->fresh();
    }

    /**
     * Remove cliente (soft delete)
     */
    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    /**
     * Ativa/Desativa cliente
     */
    public function toggleStatus(Client $client): Client
    {
        $client->update(['is_active' => !$client->is_active]);
        return $client->fresh();
    }

    /**
     * Retorna opções para filtros
     */
    public function getFilterOptions(): array
    {
        return [
            'document_types' => [
                'CPF' => 'CPF',
                'CNPJ' => 'CNPJ',
            ],
            'contributor_types' => [
                1 => 'Contribuinte ICMS',
                2 => 'Contribuinte Isento',
                9 => 'Não Contribuinte',
            ],
            'status_options' => [
                1 => 'Ativo',
                0 => 'Inativo',
            ],
        ];
    }

    /**
     * Verifica se documento já existe
     */
    public function documentExists(string $document, ?int $excludeId = null): bool
    {
        $query = Client::where('document_number', $document);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Retorna clientes ativos para select
     */
    public function getActiveForSelect(): array
    {
        return Client::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
