<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\EnsureUserIsNotBlocked;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Intervention\Image\Laravel\ServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // This is the only global middleware change we need.
        // It runs our smart caching logic early for every web request.
        $middleware->prependToGroup('web', \App\Http\Middleware\HandlePublicCaching::class);

        // Your existing aliases are perfect.
        $middleware->alias([
            'auth'         => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'can'          => \Illuminate\Auth\Middleware\Authorize::class,
            'guest'        => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle'     => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'     => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role'         => CheckUserRole::class,
            'isNotBlocked' => EnsureUserIsNotBlocked::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session has expired. Please refresh and try again.'], 419);
            }
            return redirect()->route('login')
                             ->withInput($request->except(['password', 'password_confirmation', '_token']))
                             ->with('error_419', 'Your session has expired or the form was invalid. Please try again.');
        });
    })->create();