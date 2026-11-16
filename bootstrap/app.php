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
        $middleware->validateCsrfTokens(except: ['payments/stripe/webhook']);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->authenticateSessions();
        $middleware->web(append: [
            \App\Http\Middleware\TrackActiveVisitors::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Illuminate\Database\QueryException $exception) {
            \Illuminate\Support\Facades\Log::error('Database operation failed', [
                'sqlstate' => $exception->errorInfo[0] ?? null,
                'route' => request()->route()?->getName(),
            ]);
        })->stop();
    })->create();
