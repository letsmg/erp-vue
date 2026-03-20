<?php
    
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarSenhaMail;
use Illuminate\Http\Request;

class AuthService
{
    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function login(array $credentials, $remember = false)
    {
        return Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true
        ], $remember);
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