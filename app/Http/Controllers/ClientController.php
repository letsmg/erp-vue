<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ClientRepository $repository,
        private readonly ClientService $service
    ) {}

    /**
     * Lista os clientes para o administrativo.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $filters = $request->only(['search', 'document_type', 'is_active', 'contributor_type', 'active', 'blocked']);
        $clients = $this->repository->getFiltered($filters);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $this->authorize('create', Client::class);
        return Inertia::render('Clients/Create');
    }

    /**
     * Salva novo cliente.
     */
    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class);

        $data = $request->validated();
        
        // Regra: Se um usuário padrão (não admin) cadastrar, fica como bloqueado
        if (!auth()->user()->isAdmin()) {
            $data['is_active'] = false;
        }

        // Se não tem user_id, cria usuário junto
        if (!isset($data['user_id'])) {
            $userData = [
                'name' => $data['user_name'] ?? $data['name'],
                'email' => $data['user_email'],
                'password' => $data['user_password'],
            ];
            
            // Status do usuário segue o do cliente
            $userData['is_active'] = $data['is_active'] ?? true;
            
            unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_password_confirmation']);
            
            $this->service->createClientWithUser($data, $userData);
        } else {
            $this->service->createClientOnly($data);
        }

        return redirect()->route('clients.index')->with('message', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Formulário de edição.
     */
    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        $client->load('user');
        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Atualiza o cliente.
     */
    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validated();
        
        // Atualiza usuário associado se necessário
        if ($client->user) {
            $userData = [
                'name' => $data['user_name'] ?? $data['name'],
                'email' => $data['user_email'],
            ];
            
            if ($request->filled('user_password')) {
                $userData['password'] = $request->user_password;
            }

            unset($data['user_name'], $data['user_email'], $data['user_password'], $data['user_password_confirmation']);
            
            $this->service->updateClientWithUser($client, $data, $userData);
        } else {
            $this->repository->update($client, $data);
        }

        return redirect()->route('clients.index')->with('message', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove o cliente (apenas Admin + regra de 5 anos).
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $this->repository->delete($client);

        return redirect()->route('clients.index')->with('message', 'Cliente excluído com sucesso!');
    }

    /**
     * Ativa/Desativa o cliente.
     */
    public function toggleStatus(Client $client)
    {
        $this->authorize('toggleStatus', $client);

        $client->update(['is_active' => !$client->is_active]);

        if ($client->user) {
            $client->user->update(['is_active' => $client->is_active]);
        }

        $status = $client->is_active ? 'ativado' : 'desativado';
        return back()->with('message', "Cliente $status com sucesso!");
    }

    /**
     * Métodos para área do cliente (Frontend).
     */
    public function showClientData()
    {
        $user = auth()->user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return back()->with('error', 'Dados de cliente não encontrados.');
        }

        $client->load(['addresses' => fn($q) => $q->orderBy('is_delivery_address', 'desc')]);

        return Inertia::render('Client/Profile', [
            'client' => $client
        ]);
    }

    public function updateClientData(Request $request)
    {
        $user = auth()->user();
        $client = $this->repository->findByUserId($user->id);

        // Validação básica para o próprio cliente
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone1' => 'nullable|string|max:20',
        ]);

        $this->repository->update($client, $data);

        return back()->with('message', 'Dados atualizados com sucesso!');
    }
}
