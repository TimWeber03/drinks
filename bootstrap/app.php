<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'v3',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            /** v1 of the Space-Market API has no base path of its own. */
            Route::middleware('api')->group(base_path('routes/api_v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('v3/*') || $request->is('*.json') || $request->expectsJson(),
        );

        /**
         * The Space-Market specification answers invalid input with 400 rather
         * than Laravel's 422.
         */
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('v3/*') && ! $request->routeIs('api.v1.*')) {
                return null;
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 400);
        });
    })->create();
