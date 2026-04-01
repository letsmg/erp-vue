<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Repositories\ClientRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    public function __construct(private readonly ClientRepository $repository) {}

    /**
     * Cria novo cliente e usuário associado
     */
    public function createClientWithUser(array $clientData, array $userData): array
    {
        return DB::transaction(function () use ($clientData, $userData) {
            // Cria o usuário cliente
            $userData['access_level'] = 2; // CLIENT
            $userData['is_active'] = true;
            $user = User::create($userData);

            // Associa o user_id ao cliente
            $clientData['user_id'] = $user->id;
            $client = $this->repository->create($clientData);

            return [
                'client' => $client,
                'user' => $user,
            ];
        });
    }

    /**
     * Cria cliente sem usuário (para importação/manual)
     */
    public function createClientOnly(array $data): Client
    {
        return $this->repository->create($data);
    }

    /**
     * Atualiza cliente e usuário associado
     */
    public function updateClientWithUser(Client $client, array $clientData, ?array $userData = null): Client
    {
        return DB::transaction(function () use ($client, $clientData, $userData) {
            // Atualiza cliente
            $this->repository->update($client, $clientData);

            // Atualiza usuário se fornecido
            if ($userData && $client->user) {
                $client->user->update($userData);
            }

            return $client->fresh();
        });
    }

    /**
     * Valida documento único
     */
    public function validateDocument(string $document, ?int $excludeId = null): array
    {
        // Remove caracteres não numéricos
        $cleanDocument = preg_replace('/[^0-9]/', '', $document);

        // Valida CPF
        if (strlen($cleanDocument) === 11) {
            if (!$this->isValidCPF($cleanDocument)) {
                return [
                    'valid' => false,
                    'message' => 'CPF inválido',
                    'type' => 'CPF',
                ];
            }
        }

        // Valida CNPJ
        if (strlen($cleanDocument) === 14) {
            if (!$this->isValidCNPJ($cleanDocument)) {
                return [
                    'valid' => false,
                    'message' => 'CNPJ inválido',
                    'type' => 'CNPJ',
                ];
            }
        }

        // Verifica duplicidade
        if ($this->repository->documentExists($cleanDocument, $excludeId)) {
            return [
                'valid' => false,
                'message' => 'Documento já cadastrado',
                'type' => 'duplicate',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Documento válido',
            'type' => $this->getDocumentType($cleanDocument),
            'clean_document' => $cleanDocument,
        ];
    }

    /**
     * Valida CPF
     */
    private function isValidCPF(string $cpf): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida CNPJ
     */
    private function isValidCNPJ(string $cnpj): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Primeiro dígito verificador
        $sum = 0;
        $weight = 5;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = $weight == 2 ? 9 : $weight - 1;
        }
        $rest = $sum % 11;
        $digit1 = $rest < 2 ? 0 : 11 - $rest;

        // Segundo dígito verificador
        $sum = 0;
        $weight = 6;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight;
            $weight = $weight == 2 ? 9 : $weight - 1;
        }
        $rest = $sum % 11;
        $digit2 = $rest < 2 ? 0 : 11 - $rest;

        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }

    /**
     * Retorna tipo de documento
     */
    private function getDocumentType(string $document): string
    {
        return strlen($document) === 11 ? 'CPF' : 'CNPJ';
    }

    /**
     * Prepara dados do cliente com base no tipo de documento
     */
    public function prepareClientData(array $data, string $documentType): array
    {
        $cleanDocument = preg_replace('/[^0-9]/', '', $data['document_number']);

        return array_merge($data, [
            'document_type' => $documentType,
            'document_number' => $cleanDocument,
            'state_registration' => $documentType === 'CPF' ? null : ($data['state_registration'] ?? null),
            'municipal_registration' => $documentType === 'CPF' ? null : ($data['municipal_registration'] ?? null),
            'contributor_type' => $documentType === 'CPF' 
                ? ($data['contributor_type'] ?? 9) // Não Contribuinte para PF
                : ($data['contributor_type'] ?? 1), // Contribuinte para PJ
        ]);
    }

    /**
     * Busca cliente por documento ou email
     */
    public function searchClient(string $search): ?Client
    {
        // Se for email, busca por usuário
        if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $search)->first();
            return $user ? $this->repository->findByUserId($user->id) : null;
        }

        // Se for documento, limpa e busca
        $cleanDocument = preg_replace('/[^0-9]/', '', $search);
        return $this->repository->findByDocument($cleanDocument);
    }
}
