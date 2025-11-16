<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority 1: User's preferred language from profile (if authenticated)
        if ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        }
        // Priority 2: Accept-Language header
        elseif ($request->hasHeader('Accept-Language')) {
            $acceptLanguage = $request->header('Accept-Language');
            // Parse Accept-Language header (e.g., "en-US,en;q=0.9,fr;q=0.8")
            $locale = $this->parseAcceptLanguage($acceptLanguage);
        }
        // Priority 3: X-Locale custom header
        elseif ($request->hasHeader('X-Locale')) {
            $locale = $request->header('X-Locale');
        }
        // Priority 4: Default fallback
        else {
            $locale = config('app.locale', 'en');
        }

        // Validate locale is supported
        $supportedLocales = ['en', 'fr', 'es'];
        if (! in_array($locale, $supportedLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }

        // Set the application locale
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Parse Accept-Language header to get primary locale
     */
    private function parseAcceptLanguage(string $acceptLanguage): string
    {
        // Split by comma and get first preference
        $languages = explode(',', $acceptLanguage);

        if (empty($languages)) {
            return config('app.locale', 'en');
        }

        // Get first language and remove quality value (;q=0.9)
        $primaryLanguage = explode(';', $languages[0])[0];

        // Extract base language code (en-US -> en)
        $locale = strtolower(substr($primaryLanguage, 0, 2));

        return $locale;
    }
}
