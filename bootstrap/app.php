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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\SetTimezone::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // // Add session middleware for API routes in testing environment
        // if (app()->environment('testing')) {
        //     $middleware->api(append: [
        //         \Illuminate\Session\Middleware\StartSession::class,
        //     ]);
        // }

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'organization' => \App\Http\Middleware\SetOrganizationContext::class,
            'check.limits' => \App\Http\Middleware\CheckOrganizationLimits::class,
            'module.access' => \App\Http\Middleware\CheckModuleAccess::class,
            'module.jwt' => \App\Http\Middleware\CheckModuleAccessFromJWT::class,
            'team.access' => \App\Http\Middleware\CheckTeamAccess::class,
            'subscription.active' => \App\Http\Middleware\CheckSubscriptionStatus::class,
            'user.limit' => \App\Http\Middleware\CheckUserLimit::class,
            'owner.only' => \App\Http\Middleware\CheckOwnerAccess::class,
            'throttle.auth' => \App\Http\Middleware\ThrottleAuthAttempts::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'timezone' => \App\Http\Middleware\SetTimezone::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
