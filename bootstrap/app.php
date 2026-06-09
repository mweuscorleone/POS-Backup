<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    //middleware registration
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'  => \App\Http\Middleware\RoleMiddleware::class,
            'api.key' => \App\Http\Middleware\ApiKeyMiddeware::class
        ]);
    })
    // Custom message for unauthicated user
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (AuthenticationException $e, Request $request){
            return response()->json([
                'message' => 'please login first'
            ], 401);
        });
    })->create();
