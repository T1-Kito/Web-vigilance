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
        $middleware->validateCsrfTokens(except: [
            '/api/chat/track-question',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login', ['expired' => 1]));
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, $request) {
            if ((int) $e->getStatusCode() === 419) {
                $message = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                    ], 419);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $message);
            }
        });
    })
    ->withProviders([
        Maatwebsite\Excel\ExcelServiceProvider::class,
    ])
    ->create();
