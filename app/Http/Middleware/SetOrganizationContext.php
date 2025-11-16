<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
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
        // Get tenant from header, subdomain, or request parameter
        $organizationId = $request->header('X-Organization-ID')
            ?? $request->input('organization_id')
            ?? $this->getTenantFromSubdomain($request);

        if ($organizationId) {
            // Try to find organization by ID (UUID or int) or slug
            $organization = Organization::where('id', $organizationId)
                ->orWhere('slug', $organizationId)
                ->first();

            if ($organization && $organization->is_active) {
                // Check if user belongs to this tenant
                if ($request->user() && ! $request->user()->belongsToOrganization($organization)) {
                    return response()->json([
                        'message' => 'You do not have access to this tenant.',
                    ], 403);
                }

                // Set tenant in request
                $request->attributes->set('organization', $organization);
                app()->instance('organization', $organization);
            } else {
                return response()->json([
                    'message' => 'Invalid or inactive tenant.',
                ], 404);
            }
        }

        return $next($request);
    }

    /**
     * Get tenant from subdomain.
     */
    private function getTenantFromSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // If we have a subdomain (more than 2 parts)
        if (count($parts) > 2) {
            return $parts[0]; // Return subdomain as potential tenant slug
        }

        return null;
    }
}
