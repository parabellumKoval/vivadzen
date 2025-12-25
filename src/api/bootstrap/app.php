<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Use API group to apply middleware across /api/* routes only
        $middleware->group('api', [
            \App\Http\Middleware\AddXRegionHeadersToRequest::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Trust proxy headers so signed URLs validate behind a reverse proxy.
        // $middleware->trustProxies(at: '*');
        
        // Переопределенный Sanctum
        $middleware->alias([
            'auth.api' => \App\Http\Middleware\ApiAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            return response()->json([
                'message' => 'Unauthenticated',
                'code'    => 'unauthenticated',
            ], 401);
        });
    })->create();
