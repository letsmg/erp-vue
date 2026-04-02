<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ClientLoginRequest;
use App\Http\Requests\Auth\ClientRegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Password;

class ClientAuthController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly AuthService $authService
    ) {}

    /**
     * Mostra formulário de login do cliente
     */
    public function showLogin()
    {
        if (auth()->check()) {
            if (auth()->user()->isClient()) {
                return redirect()->route('client.dashboard');
            }
            if (auth()->user()->isStaff()) {
                return redirect()->route('dashboard');
            }
        }

        return Inertia::render('Client/Auth/Login', [
            'userIp' => request()->ip(),
            'status' => session('status'),
        ]);
    }

    /**
     * Processa login do cliente
     */
    public function login(ClientLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        
        // Adiciona filtro para apenas clientes
        $credentials['access_level'] = 2; // CLIENT

        if ($this->authService->login($credentials, $request->boolean('remember'), true)) {
            // Verifica se o usuário tem cliente associado
            $user = auth()->user();
            $client = $this->clientService->findByUserId($user->id);
            
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

            // Redireciona para dashboard do cliente
            return redirect()->route('client.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou conta bloqueada.',
        ]);
    }

    /**
     * Mostra formulário de registro do cliente
     */
    public function showRegister()
    {
        return Inertia::render('Client/Auth/Register');
    }

    /**
     * Processa registro do cliente
     */
    public function register(ClientRegisterRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            // Valida documento
            $documentValidation = $this->clientService->validateDocument($data['document_number']);
            if (!$documentValidation['valid']) {
                return back()->withErrors([
                    'document_number' => $documentValidation['message'],
                ]);
            }

            // Prepara dados do cliente
            $clientData = $this->clientService->prepareClientData([
                'name' => $data['name'],
                'document_number' => $documentValidation['clean_document'],
                'phone1' => $data['phone'] ?? null,
                'contact1' => $data['name'],
                'is_active' => true,
            ], $documentValidation['type']);

            // Prepara dados do usuário
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'access_level' => 2, // CLIENT
                'is_active' => true,
            ];

            // Cria cliente e usuário
            $result = $this->clientService->createClientWithUser($clientData, $userData);

            // Faz login automático
            auth()->login($result['user']);

            return redirect()->route('client.dashboard')
                ->with('success', 'Cadastro realizado com sucesso!');

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Erro ao realizar cadastro: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Mostra formulário de esqueci senha
     */
    public function showForgotPassword()
    {
        return Inertia::render('Client/Auth/ForgotPassword');
    }

    /**
     * Envia link de redefinição de senha
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            // Verifica se é um cliente
            $user = User::where('email', $data['email'])
                ->where('access_level', 2) // CLIENT
                ->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'E-mail não encontrado em nossa base de clientes.',
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
        return redirect()->route('client.login');
    }
}
