<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies (Railway reverse proxy) so HTTPS is detected correctly
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin'           => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'reseller'        => \App\Http\Middleware\EnsureResellerIsActive::class,
            'reseller.active' => \App\Http\Middleware\EnsureResellerIsActive::class,
            'jwt.auth'        => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
