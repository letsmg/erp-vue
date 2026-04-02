<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarSenhaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use App\Enums\AccessLevel;

class AuthService
{
    /**
     * Valida a localização do IP.
     */
    private function validateGeographicAccess(): void
    {
        $ip = request()->ip();

        if ($ip === '127.0.0.1' || $ip === '::1') {
            return;
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode,message");

            if ($response->successful() && $response->json('status') === 'success') {
                $countryCode = $response->json('countryCode');

                if ($countryCode !== 'BR') {
                    logger()->warning("Tentativa de login bloqueada: IP estrangeiro detectado", [
                        'ip' => $ip,
                        'pais' => $countryCode,
                        'email' => request('email')
                    ]);

                    throw ValidationException::withMessages([
                        'email' => [
                            'Acesso negado: Este sistema não aceita logins fora do Brasil.'
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            logger()->error("Falha no serviço de verificação de IP: " . $e->getMessage());
        }
    }

    /**
     * 🔥 LOGIN CORRIGIDO COM ENUM E PERFIS DISTINTOS
     */
    public function login(array $credentials, $remember = false, bool $isClientAuth = false)
    {
        $this->validateGeographicAccess();

        // 🔎 1. Busca usuário
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return false;
        }

        // 🔐 2. Valida senha
        if (!Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        // 🚫 3. Verifica se o usuário está ativo (PADRÃO PARA TODOS)
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Sua conta está bloqueada. Entre em contato com a administração para reativá-la.'],
            ]);
        }

        // 🔥 4. VALIDAÇÃO DE PERFIL POR PORTAL
        if ($isClientAuth) {
            // Se for login de cliente, o usuário DEVE ser um CLIENT
            if ($user->access_level !== AccessLevel::CLIENT) {
                return false;
            }
        } else {
            // Se for login administrativo, o usuário NÃO PODE ser um CLIENT
            if ($user->access_level === AccessLevel::CLIENT) {
                return false;
            }
        }

        // ✅ 5. Login manual
        Auth::login($user, $remember);

        // 🧾 6. Auditoria
        $user->update([
            'last_login_ip' => request()->ip()
        ]);

        return true;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendResetLink(string $email)
    {
        $url = route('login');
        Mail::to($email)->send(new RecuperarSenhaMail($url));
    }
}