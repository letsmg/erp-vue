<?php

namespace App\Repositories;

use App\Helpers\SanitizerHelper;
use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository
{
    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = Client::with(['user', 'addresses'])
            ->orderBy('display_name');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            if (strlen($search) >= 4) {
                $query->where(function ($q) use ($search) {
                    $searchTerm = "%{$search}%";
                    $q->where('display_name', 'ilike', $searchTerm)
                      ->orWhereHas('user', function ($uq) use ($searchTerm) {
                          $uq->where('display_name', 'ilike', $searchTerm);
                      });
                });
            }
        }

        if (!empty($filters['active'])) {
            $query->where('is_active', true);
        }

        if (!empty($filters['blocked'])) {
            $query->where('is_active', false);
        }

        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['is_active']) && empty($filters['active']) && empty($filters['blocked'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['contributor_type'])) {
            $query->where('contributor_type', $filters['contributor_type']);
        }

        return $query->paginate(15)->withQueryString();
    }

    public function findById(int $id): ?Client
    {
        return Client::with(['user', 'addresses' => function ($query) {
            $query->orderBy('is_delivery_address', 'desc');
        }])->find($id);
    }

    public function findByDocument(string $document): ?Client
    {
        $documentHash = hash('sha256', $document);
        return Client::where('document_hash', $documentHash)->first();
    }

    public function findByUserId(int $userId): ?Client
    {
        return Client::where('user_id', $userId)->first();
    }

    public function create(array $data): Client
    {
        $data = SanitizerHelper::sanitize($data);
        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $data = SanitizerHelper::sanitize($data);
        $client->update($data);
        return $client->fresh();
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    public function toggleStatus(Client $client): Client
    {
        $client->update(['is_active' => !$client->is_active]);
        return $client->fresh();
    }

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

    public function documentExists(string $document, ?int $excludeId = null): bool
    {
        $documentHash = hash('sha256', $document);
        $query = Client::where('document_hash', $documentHash);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getActiveForSelect(): array
    {
        return Client::where('is_active', true)
            ->orderBy('display_name')
            ->pluck('display_name', 'id')
            ->toArray();
    }
}