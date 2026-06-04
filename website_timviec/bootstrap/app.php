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
            // Alias cũ (giữ lại để không break CV builder routes)
            'employee' => \App\Http\Middleware\EnsureEmployee::class,

            // Alias mới cho phân quyền đúng role
            'candidate' => \App\Http\Middleware\EnsureCandidate::class,
            'employer'  => \App\Http\Middleware\EnsureEmployer::class,
        ]);

        // Loại trừ VNPay IPN khỏi CSRF verification (server-to-server callback)
        $middleware->validateCsrfTokens(except: [
            'payment/token/ipn',
            'payment/subscription/ipn',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();