<?php

use App\Http\Middleware\RequestIdMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            RequestIdMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => null,
                    'meta' => [
                        'request_id' => $requestId,
                        'timestamp' => now()->toIso8601String(),
                    ],
                    'errors' => $e->errors(),
                ], 422)->header('X-Request-ID', $requestId);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data' => null,
                    'meta' => [
                        'request_id' => $requestId,
                        'timestamp' => now()->toIso8601String(),
                    ],
                    'errors' => null,
                ], 401)->header('X-Request-ID', $requestId);
            }
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'This action is unauthorized',
                    'data' => null,
                    'meta' => [
                        'request_id' => $requestId,
                        'timestamp' => now()->toIso8601String(),
                    ],
                    'errors' => null,
                ], 403)->header('X-Request-ID', $requestId);
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'data' => null,
                    'meta' => [
                        'request_id' => $requestId,
                        'timestamp' => now()->toIso8601String(),
                    ],
                    'errors' => null,
                ], 404)->header('X-Request-ID', $requestId);
            }
        });
    })->create();
