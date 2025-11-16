<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Configure Carbon to serialize dates in ISO 8601 format with timezone
        \Illuminate\Support\Facades\Date::use(\Carbon\CarbonImmutable::class);

        // Set JSON serialization format for all Carbon dates
        \Carbon\Carbon::serializeUsing(function ($carbon) {
            return $carbon->toIso8601String();
        });
    }
}
