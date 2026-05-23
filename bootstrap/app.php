<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LoadBalancerSimulation;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. استثناء الـ CSRF للـ API
        $middleware->validateCsrfTokens(except: [
            'api/buy/*', 
        ]);

        // 2. تفعيل الـ Load Balancer لكل مسارات الـ API تلقائياً
        $middleware->api(append: [
            LoadBalancerSimulation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();