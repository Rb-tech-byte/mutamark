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
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $is403 = $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException
                  || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                  || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 403);

            if ($is403) {
                $rawMsg = $e->getMessage();
                $cleanMsg = 'You do not have the required permissions to access this enterprise module or perform this action.';
                if (!empty($rawMsg) && !str_contains($rawMsg, 'User does not have') && $rawMsg !== 'This action is unauthorized.') {
                    $cleanMsg = $rawMsg;
                }

                if ($request->header('X-Inertia')) {
                    return \Inertia\Inertia::render('errors/403', [
                        'status' => 403,
                        'title' => 'Access Restricted',
                        'message' => $cleanMsg,
                    ])->toResponse($request)->setStatusCode(403);
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $cleanMsg,
                        'status' => 403,
                        'error' => 'Forbidden'
                    ], 403);
                }

                return response()->view('errors.403', [
                    'status' => 403,
                    'title' => 'Access Restricted',
                    'message' => $cleanMsg,
                ], 403);
            }
        });
    })->create();
