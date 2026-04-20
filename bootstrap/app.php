<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. adding our custom InjectTokenFromCookieMiddleware to the API middleware group so that it will be applied to all API routes.
        $middleware->api(prepend: [
            \App\Http\Middleware\InjectTokenFromCookieMiddleware::class,
        ]);

        // 2. adding our custom RoleMiddleware with the alias 'role' so that we can use it in our routes like this: ->middleware('role:admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            // For tokens with specific abilities (permissions), you can use the following middleware provided by Sanctum:
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // validation Handling: when a ValidationException is thrown, we will return a JSON response with the validation errors and a 422 status code.
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Errors in the provided data.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Authentication Handling: when an AuthenticationException is thrown, we will return a JSON response with a message indicating that the user is unauthorized and a 401 status code.
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized - Please login or your token has expired.'
                ], 401);
            }
        });

        // NotFound Handling: when a NotFoundHttpException is thrown, we will return a JSON response with a message indicating that the requested resource was not found and a 404 status code.
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Resource not found. The requested endpoint does not exist.'
                ], 404);
            }
        });
    })->create();
