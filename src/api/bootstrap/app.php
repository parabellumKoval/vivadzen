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
        // Apply regional context middleware globally for all API requests
        // This ensures Accept-Language and X-Region headers are processed for registration and other requests
        $middleware->append(\App\Http\Middleware\AddXRegionHeadersToRequest::class);
        
        // глобально для api
        $middleware->group('api', [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
        
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
