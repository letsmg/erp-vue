<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelfClientRequest;
use App\Http\Requests\Auth\SelfClientLoginRequest;
use App\Http\Requests\Auth\SelfClientForgotPasswordRequest;
use App\Models\Client;
use App\Models\User;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class SelfClientAuthController extends Controller
{
    public function __construct(
        private readonly ClientRepository $repository,
        private readonly ClientService $service,
        private readonly AuthService $authService
    ) {}

    /**
     * Mostra formulário de login do cliente
     */
    public function showLogin(Request $request)
    {
        if (auth()->check()) {
            if (auth()->user()->isClient()) {
                return redirect()->route('client.dashboard');
            }
            if (auth()->user()->isStaff()) {
                return redirect()->route('dashboard');
            }
        }

        // Armazena URL de redirecionamento se fornecida
        if ($request->has('redirect')) {
            session(['url.intended' => $request->input('redirect')]);
        }

        return Inertia::render('Client/Auth/Login', [
            'userIp' => request()->ip(),
            'status' => session('status'),
        ]);
    }

    /**
     * Processa login do cliente
     */
    public function login(SelfClientLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        
        // Adiciona filtro para apenas clientes
        $credentials['access_level'] = 2; // CLIENT

        if ($this->authService->login($credentials, $request->boolean('remember'), true)) {
            // Verifica se o usuário tem cliente associado
            $user = auth()->user();
            $client = $this->repository->findByUserId($user->id);
            
            if (!$client) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Cadastro de cliente não encontrado. Entre em contato com o suporte.',
                ]);
            }

            // Se o cliente (tabela clients) estiver inativo, bloqueia mesmo com user ativo
            if (!$client->is_active) {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Sua conta de cliente está bloqueada. Entre em contato com a administração.',
                ]);
            }

            // Verifica se há URL de redirecionamento armazenada
            $intendedUrl = session()->pull('url.intended');
            
            if ($intendedUrl) {
                return redirect($intendedUrl);
            }

            // Redireciona para dashboard do cliente
            return redirect()->route('client.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou conta bloqueada.',
        ]);
    }

    /**
     * Mostra formulário de esqueci senha
     */
    public function showForgotPassword()
    {
        return Inertia::render('Client/Auth/ForgotPassword');
    }

    /**
     * Verifica o email do cliente
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('client.login')
                ->with('error', 'Usuário não encontrado.');
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('client.login')
                ->with('error', 'Link de verificação inválido.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('client.login')
                ->with('info', 'Email já verificado.');
        }

        $user->markEmailAsVerified();
        $user->update(['is_active' => true]);

        // Also activate the client record
        $client = $this->repository->findByUserId($user->id);
        if ($client) {
            $client->update([
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        return redirect()->route('client.login')
            ->with('success', 'Email verificado com sucesso! Sua conta está ativa.');
    }

    /**
     * Envia link de redefinição de senha
     */
    public function sendResetLinkEmail(SelfClientForgotPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            // Verifica se é um cliente
            $user = User::where('email', $data['email'])
                ->where('access_level', 2) // CLIENT
                ->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Credenciais inválidas.',
                ]);
            }

            $this->authService->sendResetLink($data['email']);
            
            return back()->with('success', 'Link de redefinição enviado para seu e-mail!');
            
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Erro no provedor de e-mail: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Logout do cliente
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);
        return redirect()->route('store.index');
    }

    /**
     * Mostra o perfil do cliente autenticado.
     */
    public function profile()
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return redirect()->route('client.register.form')
                ->with('error', 'Complete seu cadastro para continuar.');
        }

        $client->load(['addresses' => function ($query) {
            $query->orderBy('is_delivery_address', 'desc');
        }]);

        return inertia('Client/Profile', [
            'client' => $client,
            'addresses' => $client->addresses,
        ]);
    }

    /**
     * Mostra o formulário de cadastro.
     */
    public function showRegistrationForm()
    {
        return inertia('Client/Auth/Register');
    }

    /**
     * Mostra o formulário de edição.
     */
    public function edit()
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return redirect()->route('client.register.form')
                ->with('error', 'Complete seu cadastro para continuar.');
        }

        return inertia('Client/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Cadastra um novo cliente.
     */
    public function register(SelfClientRequest $request)
    {
        try {
            $data = $request->validated();
            $userData = $this->prepareUserData($data);

            $client = $this->service->createClientWithUser($data, $userData);

            // Enviar email de verificação
            $client->user->sendEmailVerificationNotification();

            return redirect()->route('client.login')
                ->with('success', 'Cadastro realizado! Um link de confirmação foi enviado para seu e-mail. Clique no link para ativar sua conta e poder fazer login.');
        } catch (\Exception $e) {
            \Log::error('Client registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao realizar cadastro: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza os dados do cliente.
     */
    public function update(SelfClientRequest $request)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return back()->with('error', 'Cliente não encontrado.');
        }

        try {
            $data = $request->validated();
            $this->repository->update($client, $data);

            return redirect()->route('client.profile')
                ->with('success', 'Dados atualizados com sucesso!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar dados: ' . $e->getMessage());
        }
    }

    /**
     * Mostra a página de exclusão/inativação.
     */
    public function showDeleteForm()
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return back()->with('error', 'Cliente não encontrado.');
        }

        // Verifica se tem compras nos últimos 5 anos
        $hasRecentPurchases = $this->service->hasRecentPurchases($client->id, 5);
        
        return inertia('Client/Delete', [
            'client' => $client,
            'canDelete' => !$hasRecentPurchases,
            'hasRecentPurchases' => $hasRecentPurchases,
        ]);
    }

    /**
     * Exclui ou inativa o cadastro do cliente.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return back()->with('error', 'Cliente não encontrado.');
        }

        try {
            $hasRecentPurchases = $this->service->hasRecentPurchases($client->id, 5);
            
            if ($hasRecentPurchases) {
                // Inativa o cadastro
                $this->repository->update($client, ['is_active' => false]);
                
                // Faz logout do usuário
                Auth::logout();
                
                return redirect()->route('login')
                    ->with('success', 'Seu cadastro foi inativado com sucesso.');
            } else {
                // Exclui o cadastro
                $this->repository->delete($client);
                
                // Exclui o usuário também
                $user->delete();
                
                // Faz logout
                Auth::logout();
                
                return redirect()->route('login')
                    ->with('success', 'Seu cadastro foi excluído com sucesso.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Prepara dados do usuário para cadastro.
     */
    private function prepareUserData(array $data): array
    {
        $documentType = $this->service->getDocumentType($data['document_number']);
        $cleanDocument = preg_replace('/[^0-9]/', '', $data['document_number']);

        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'password_confirmation' => $data['password_confirmation'],
            'access_level' => 2, // CLIENT
            'is_active' => false, // Requires email verification
        ];
    }

    /**
     * Retorna os dados do cliente para API.
     */
    public function getClientData()
    {
        $user = Auth::user();
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
     * Atualiza apenas a senha do usuário.
     */
    public function updatePassword(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verifica senha atual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        // Atualiza senha
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Cria um novo endereço para o cliente.
     */
    public function storeAddress(\App\Http\Requests\AddressRequest $request)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        if (!$client) {
            return back()->with('error', 'Cliente não encontrado.');
        }

        $data = $request->validated();
        $data['client_id'] = $client->id;

        // Se for o primeiro endereço ou marcado como entrega, garante que é o único de entrega
        if ($data['is_delivery_address'] ?? false) {
            $client->addresses()->update(['is_delivery_address' => false]);
        }

        // Se não tiver nenhum endereço de entrega, força este como entrega
        $hasDelivery = $client->addresses()->where('is_delivery_address', true)->exists();
        if (!$hasDelivery) {
            $data['is_delivery_address'] = true;
        }

        \App\Models\Address::create($data);

        return redirect()->route('client.profile')
            ->with('success', 'Endereço adicionado com sucesso!');
    }

    /**
     * Atualiza um endereço existente.
     */
    public function updateAddress(\App\Http\Requests\AddressRequest $request, \App\Models\Address $address)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        // Verifica se o endereço pertence ao cliente
        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Você não tem permissão para editar este endereço.');
        }

        $data = $request->validated();

        // Se este endereço está sendo marcado como entrega
        if ($data['is_delivery_address'] ?? false) {
            // Remove a flag de entrega de todos os outros endereços
            $client->addresses()->where('id', '!=', $address->id)->update(['is_delivery_address' => false]);
        }

        $address->update($data);

        return redirect()->route('client.profile')
            ->with('success', 'Endereço atualizado com sucesso!');
    }

    /**
     * Exclui um endereço.
     */
    public function destroyAddress(\App\Models\Address $address)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        // Verifica se o endereço pertence ao cliente
        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Você não tem permissão para excluir este endereço.');
        }

        // Não permite excluir o último endereço de entrega
        if ($address->is_delivery_address && $client->addresses()->count() === 1) {
            return back()->with('error', 'Você deve manter pelo menos um endereço de entrega.');
        }

        $wasDelivery = $address->is_delivery_address;
        $address->delete();

        // Se o endereço excluído era o de entrega, define o primeiro como novo endereço de entrega
        if ($wasDelivery) {
            $firstAddress = $client->addresses()->first();
            if ($firstAddress) {
                $firstAddress->update(['is_delivery_address' => true]);
            }
        }

        return redirect()->route('client.profile')
            ->with('success', 'Endereço excluído com sucesso!');
    }

    /**
     * Define um endereço como principal de entrega.
     */
    public function setDeliveryAddress(\App\Models\Address $address)
    {
        $user = Auth::user();
        $client = $this->repository->findByUserId($user->id);

        // Verifica se o endereço pertence ao cliente
        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Você não tem permissão para alterar este endereço.');
        }

        // Remove a flag de entrega de todos os outros endereços
        $client->addresses()->where('id', '!=', $address->id)->update(['is_delivery_address' => false]);

        // Define este como endereço de entrega
        $address->update(['is_delivery_address' => true]);

        return redirect()->route('client.profile')
            ->with('success', 'Endereço principal definido com sucesso!');
    }
}
