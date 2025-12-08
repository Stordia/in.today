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
        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetLocale::class,
        ]);

        // Exclude public booking form from CSRF (it has honeypot protection)
        $middleware->validateCsrfTokens(except: [
            'book/*/request',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
