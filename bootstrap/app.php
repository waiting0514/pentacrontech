<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SetApiLocale::class,
        ]);
        
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'maintenance' => \App\Http\Middleware\EnsureNotInMaintenance::class,
            'ai_enabled' => \App\Http\Middleware\EnsureAiEnabled::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 422,
                    'error_code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'error_code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'error_code' => 'UNAUTHENTICATED',
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 403,
                    'error_code' => 'UNAUTHORIZED',
                    'message' => 'Forbidden',
                ], 403);
            }
        });

        // abort(403/404...) 等 HTTP 例外維持原狀態碼，避免被下方轉成 500
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e->getStatusCode();
                return response()->json([
                    'success' => false,
                    'code' => $status,
                    'error_code' => $status === 403 ? 'UNAUTHORIZED' : ($status === 404 ? 'RESOURCE_NOT_FOUND' : 'HTTP_ERROR'),
                    'message' => $e->getMessage() ?: 'Error',
                ], $status, $e->getHeaders());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = app()->hasDebugModeEnabled() ? $e->getMessage() : 'Server error';
                return response()->json([
                    'success' => false,
                    'code' => 500,
                    'error_code' => 'SERVER_ERROR',
                    'message' => $message,
                ], 500);
            }
        });
    })->create();
