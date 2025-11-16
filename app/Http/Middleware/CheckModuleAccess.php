<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the organization has access to the requested module
     * based on their subscription and enabled modules.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $moduleSlug  The slug of the module to check access for
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get organization from request
        $organizationId = $request->route('organizationId')
            ?? $request->route('organization')
            ?? $request->input('organization_id')
            ?? $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Organization ID is required',
            ], 400);
        }

        $organization = \App\Models\Organization::find($organizationId);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Check if organization has active subscription
        if (! $organization->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'Organization subscription is not active.',
                'subscription_status' => $organization->subscription_status,
            ], 403);
        }

        // Check if organization has access to the module
        if (! $organization->hasModule($moduleSlug)) {
            return response()->json([
                'success' => false,
                'message' => "Access denied. Module '{$moduleSlug}' is not enabled for this organization.",
                'module_slug' => $moduleSlug,
                'available_modules' => $organization->enabledModules()->pluck('slug'),
            ], 403);
        }

        // Check if module has expired
        $module = $organization->modules()
            ->where('slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->first();

        if ($module && $module->pivot->expires_at && now()->isAfter($module->pivot->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => "Module '{$moduleSlug}' access has expired.",
                'module_slug' => $moduleSlug,
                'expired_at' => $module->pivot->expires_at,
            ], 403);
        }

        // Attach module to request for use in controllers
        $request->merge(['_module' => $module]);

        return $next($request);
    }
}
