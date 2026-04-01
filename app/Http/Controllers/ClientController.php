<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Policies\ClientPolicy;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientRepository $repository,
        private readonly ClientService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->only(['search', 'document_type', 'is_active', 'contributor_type']);
        $clients = $this->repository->getFiltered($filters);
        $filterOptions = $this->repository->getFilterOptions();

        return $this->success([
            'clients' => $clients,
            'filters' => $filterOptions,
        ], 'Clientes carregados com sucesso.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        try {
            $data = $request->validated();
            
            // Se não tem user_id, cria usuário junto
            if (!isset($data['user_id'])) {
                $userData = [
                    'name' => $data['user_name'] ?? $data['name'],
                    'email' => $data['user_email'],
                    'password' => $data['user_password'],
                    'password_confirmation' => $data['user_password_confirmation'] ?? null,
                ];
                
                unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_password_confirmation']);
                
                $result = $this->service->createClientWithUser($data, $userData);
            } else {
                $result = $this->service->createClientOnly($data);
            }

            return $this->created($result['client'] ?? $result, 'Cliente criado com sucesso.');
            
        } catch (\Exception $e) {
            return $this->error('Erro ao criar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $client->load(['user', 'addresses' => function ($query) {
            $query->orderBy('is_delivery_address', 'desc');
        }]);

        return $this->success($client, 'Cliente carregado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        try {
            $data = $request->validated();
            
            // Se não tem user_id, atualiza usuário junto
            if (!isset($data['user_id']) && $client->user) {
                $userData = [
                    'name' => $data['user_name'] ?? $data['name'],
                    'email' => $data['user_email'] ?? $client->user->email,
                ];
                
                unset($data['user_name'], $data['user_email']);
                
                $updatedClient = $this->service->updateClientWithUser($client, $data, $userData);
            } else {
                unset($data['user_name'], $data['user_email']);
                $updatedClient = $this->repository->update($client, $data);
            }

            return $this->success($updatedClient, 'Cliente atualizado com sucesso.');
            
        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar cliente: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        try {
            $this->repository->delete($client);
            return $this->deleted('Cliente removido com sucesso.');
            
        } catch (\Exception $e) {
            return $this->error('Erro ao remover cliente: ' . $e->getMessage());
        }
    }

    /**
     * Toggle client status
     */
    public function toggleStatus(Client $client): JsonResponse
    {
        $this->authorize('toggleStatus', $client);

        try {
            $updatedClient = $this->repository->toggleStatus($client);
            $status = $updatedClient->is_active ? 'ativado' : 'desativado';
            
            return $this->success($updatedClient, "Cliente {$status} com sucesso.");
            
        } catch (\Exception $e) {
            return $this->error('Erro ao alterar status do cliente: ' . $e->getMessage());
        }
    }

    /**
     * Validate document
     */
    public function validateDocument(Request $request): JsonResponse
    {
        $document = $request->input('document');
        $excludeId = $request->input('exclude_id');

        if (empty($document)) {
            return $this->error('Documento é obrigatório.');
        }

        $validation = $this->service->validateDocument($document, $excludeId);
        
        if ($validation['valid']) {
            return $this->success($validation, 'Documento válido.');
        } else {
            return $this->error($validation['message'], $validation);
        }
    }

    /**
     * Search client by document or email
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('search');
        
        if (empty($search)) {
            return $this->error('Termo de busca é obrigatório.');
        }

        $client = $this->service->searchClient($search);
        
        if ($client) {
            return $this->success($client, 'Cliente encontrado.');
        } else {
            return $this->error('Cliente não encontrado.');
        }
    }

    /**
     * Export clients
     */
    public function export(Request $request): JsonResponse
    {
        $this->authorize('export', Client::class);

        try {
            $filters = $request->only(['search', 'document_type', 'is_active', 'contributor_type']);
            $clients = $this->repository->getFiltered($filters);
            
            // Prepara dados para exportação
            $exportData = $clients->getCollection()->map(function ($client) {
                return [
                    'ID' => $client->id,
                    'Nome' => $client->name,
                    'Tipo Documento' => $client->document_type,
                    'Documento' => $client->formatted_document,
                    'Email' => $client->user?->email,
                    'Telefone 1' => $client->phone1,
                    'Contato 1' => $client->contact1,
                    'Telefone 2' => $client->phone2,
                    'Contato 2' => $client->contact2,
                    'IE' => $client->state_registration,
                    'IM' => $client->municipal_registration,
                    'Tipo Contribuinte' => $client->contributor_type_description,
                    'Status' => $client->is_active ? 'Ativo' : 'Inativo',
                    'Criado em' => $client->created_at->format('d/m/Y H:i'),
                ];
            });

            return $this->success($exportData, 'Dados preparados para exportação.');
            
        } catch (\Exception $e) {
            return $this->error('Erro ao exportar clientes: ' . $e->getMessage());
        }
    }

    /**
     * Show client data for client area
     */
    public function showClientData(): JsonResponse
    {
        $user = auth()->user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return $this->error('Dados de cliente não encontrados.');
        }

        $client->load(['addresses' => function ($query) {
            $query->orderBy('is_delivery_address', 'desc');
        }]);

        return $this->success($client, 'Dados carregados com sucesso.');
    }

    /**
     * Update client data from client area
     */
    public function updateClientData(Request $request): JsonResponse
    {
        $user = auth()->user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return $this->error('Dados de cliente não encontrados.');
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'phone1' => 'nullable|string|max:20',
                'contact1' => 'nullable|string|max:255',
                'phone2' => 'nullable|string|max:20',
                'contact2' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'current_password' => 'nullable|required_with:new_password|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            // Atualiza dados do cliente
            $clientData = [
                'name' => $data['name'],
                'phone1' => $data['phone1'] ?? null,
                'contact1' => $data['contact1'] ?? $client->contact1,
                'phone2' => $data['phone2'] ?? null,
                'contact2' => $data['contact2'] ?? null,
            ];

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Se houver mudança de senha
            if (!empty($data['new_password'])) {
                if (!Hash::check($data['current_password'], $user->password)) {
                    return $this->error('Senha atual incorreta.');
                }
                $userData['password'] = Hash::make($data['new_password']);
            }

            $updatedClient = $this->service->updateClientWithUser($client, $clientData, $userData);

            return $this->success($updatedClient, 'Dados atualizados com sucesso.');

        } catch (\Exception $e) {
            return $this->error('Erro ao atualizar dados: ' . $e->getMessage());
        }
    }
}
