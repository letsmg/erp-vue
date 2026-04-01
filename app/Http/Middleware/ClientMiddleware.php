<?php

namespace App\Http\Middleware;

use App\Enums\AccessLevel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Verifica se o usuário está autenticado e é cliente
        if (!$user || !$user->isClient()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acesso não autorizado. Apenas clientes podem acessar esta área.',
                    'code' => 403
                ], 403);
            }

            return redirect()->route('client.login')
                ->with('error', 'Acesso não autorizado. Faça login como cliente.');
        }

        // Verifica se o cliente está ativo
        if (!$user->is_active) {
            auth()->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sua conta está desativada. Entre em contato com o suporte.',
                    'code' => 403
                ], 403);
            }

            return redirect()->route('client.login')
                ->with('error', 'Sua conta está desativada. Entre em contato com o suporte.');
        }

        return $next($request);
    }
}
