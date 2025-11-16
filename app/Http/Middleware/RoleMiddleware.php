<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $organization = $request->attributes->get('organization') ?? (app()->has('organization') ? app('organization') : null);

        if (! $organization) {
            return response()->json([
                'message' => 'Tenant context is required.',
            ], 400);
        }

        if (! $request->user()->hasAnyRoleInOrganization($roles, $organization)) {
            return response()->json([
                'message' => 'Unauthorized. Required role(s) in this organization: '.implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
