<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $this->determineTimezone($request);

        // Validate timezone
        if ($this->isValidTimezone($timezone)) {
            // Set application timezone
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }

    /**
     * Determine the timezone to use based on priority
     */
    private function determineTimezone(Request $request): string
    {
        // Priority 1: X-Timezone header (for API clients)
        if ($request->hasHeader('X-Timezone')) {
            $timezone = $request->header('X-Timezone');
            if (! empty($timezone)) {
                return $timezone;
            }
        }

        // Priority 2: User's timezone from profile (if authenticated)
        if ($request->user() && ! empty($request->user()->timezone)) {
            return $request->user()->timezone;
        }

        // Priority 3: Organization's timezone (if organization context is set)
        if ($request->attributes->has('organization')) {
            $organization = $request->attributes->get('organization');
            if (! empty($organization->timezone)) {
                return $organization->timezone;
            }
        }

        // Priority 4: Query parameter (for testing/one-off requests)
        if ($request->has('timezone')) {
            $timezone = $request->query('timezone');
            if (! empty($timezone)) {
                return $timezone;
            }
        }

        // Priority 5: Default fallback
        return config('app.timezone', 'UTC');
    }

    /**
     * Validate if timezone is valid
     */
    private function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }
}
