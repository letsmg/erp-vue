<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Inertia\Inertia;

class LoginController extends Controller
{
    protected $service;

    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    public function showLogin() {
        return Inertia::render('Auth/Login');
    }

    public function showRegister() {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $this->service->register($data);

        return redirect()->route('login')->with('success', 'Cadastro realizado!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->service->login($credentials, $request->remember)) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou conta bloqueada.',
        ]);
    }

    public function logout(Request $request)
    {
        $this->service->logout($request);

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $this->service->sendResetLink($request->email);

            return back()->with('success', 'Link enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Erro no provedor de e-mail: ' . $e->getMessage()
            ]);
        }
    }
}