<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\ClientMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SanitizeInput::class,
        ]);

        // Registra middleware de cliente
        $middleware->alias([
            'client' => ClientMiddleware::class,
        ]);

        // Redireciona usuários não autenticados que tentarem acessar rotas protegidas
        // para a rota NOMEADA 'login'.
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();