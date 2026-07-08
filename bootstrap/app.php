<?php

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureSetupComplete;
use App\Http\Middleware\RedirectIfSetupComplete;
use App\Http\Middleware\RoleMiddleware;
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
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'active' => EnsureAccountActive::class,
            'setup.pending' => RedirectIfSetupComplete::class,
        ]);

        $middleware->web(append: [
            EnsureSetupComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
