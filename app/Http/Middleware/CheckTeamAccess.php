<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckTeamAccess
{
    /**
     * Handle an incoming request.
     *
     * Validates if the authenticated user belongs to the specified team(s)
     * using the JWT token payload (zero database queries).
     *
     * Usage:
     * Route::get('/endpoint', [Controller::class, 'method'])->middleware('team.access:sales');
     * Route::get('/endpoint', [Controller::class, 'method'])->middleware('team.access:sales,dev');
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$teams): Response
    {
        try {
            // Get JWT payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Get teams from JWT
            $userTeams = $payload->get('teams', []);

            // If no teams specified in middleware, just check if user has any teams
            if (empty($teams)) {
                if (empty($userTeams)) {
                    return response()->json([
                        'error' => 'Team membership required',
                    ], 403);
                }

                return $next($request);
            }

            // Check if user belongs to any of the specified teams
            $hasAccess = ! empty(array_intersect($teams, $userTeams));

            if (! $hasAccess) {
                return response()->json([
                    'error' => 'Access denied. Required team: '.implode(' or ', $teams),
                    'your_teams' => $userTeams,
                ], 403);
            }

            // Store teams in request for controller access
            $request->merge(['user_teams' => $userTeams]);

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Team validation failed',
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
