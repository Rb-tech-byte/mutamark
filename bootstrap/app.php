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
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\CheckInstallation::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UpdateUserActiveStatus::class,
            \App\Http\Middleware\EnsureAccountEnabled::class,
        ]);
        $middleware->alias([
            'PlanModuleCheck' => \App\Http\Middleware\PlanModuleCheck::class,
            'api.json' => \App\Http\Middleware\ApiForceJson::class
        ]);

        $middleware->validateCsrfTokens(
            except: [
                'aamarpay/*',
                'iyzipay/*',
                'meta/callback',
                'payfort/*',
                'sslcommerz/*',
                'twocheckout/*',
                'payu/*',
                'powertranz/*',
                'users/*/assign-plan',
                'plans/*/assign-free',
                'api/v1/payments/webhook/*',
                'pay/checkout/*',
            ]
        );

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
