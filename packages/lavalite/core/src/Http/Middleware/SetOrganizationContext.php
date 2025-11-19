<?php

namespace Lavalite\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lavalite\Core\Models\Organization;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizationIdentifier = $request->header('X-Organization-ID');

        if (! $organizationIdentifier) {
            return response()->json([
                'message' => 'Organization context required. Please provide X-Organization-ID header.',
            ], 400);
        }

        // Try to find organization by ID or slug
        $organization = Organization::where('id', $organizationIdentifier)
            ->orWhere('slug', $organizationIdentifier)
            ->first();

        if (! $organization) {
            return response()->json([
                'message' => 'Invalid organization identifier.',
            ], 404);
        }

        if (! $organization->isActive()) {
            return response()->json([
                'message' => 'Organization is not active.',
            ], 403);
        }

        // Store organization in request attributes
        $request->attributes->set('organization', $organization);

        // Store in app container for global access
        app()->instance('organization', $organization);

        return $next($request);
    }
}
