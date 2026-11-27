<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureBusinessAccess;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/settings.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enforce non-persistent authentication for every Fortify login request.
        $middleware->appendToGroup('web', \App\Http\Middleware\ForceNoRemember::class);

        $middleware->alias([
            'business.access' => EnsureBusinessAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();