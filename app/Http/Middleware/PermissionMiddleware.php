<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $organization = $request->attributes->get('organization') ?? app('organization');

        if (! $organization) {
            return response()->json([
                'message' => 'Tenant context is required.',
            ], 400);
        }

        if (! $request->user()->hasAnyPermissionInTenant($permissions, $organization)) {
            return response()->json([
                'message' => 'Unauthorized. Required permission(s) in this tenant: '.implode(', ', $permissions),
            ], 403);
        }

        return $next($request);
    }
}
