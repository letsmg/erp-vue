<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\ClientMiddleware;
use App\Http\Middleware\StaffMiddleware;
use App\Http\Middleware\ApiJsonResponse;
use App\Http\Middleware\JwtAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SanitizeInput::class,
        ]);

        $middleware->api(append: [
            ApiJsonResponse::class,
        ]);

        // Registra aliases de middleware
        $middleware->alias([
            'client' => ClientMiddleware::class,
            'staff' => StaffMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'jwt.auth' => JwtAuth::class,
        ]);

        // Redireciona usuários não autenticados que tentarem acessar rotas protegidas
        // para a rota NOMEADA 'login', exceto para requisições API que devem retornar 401 JSON
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // Não redireciona, permite que o middleware retorne 401
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        \Sentry\Laravel\Integration::handles($exceptions);
    })->create();