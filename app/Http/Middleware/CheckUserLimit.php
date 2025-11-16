<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckUserLimit
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if adding a new user would exceed the organization's
     * max_users limit by reading from the JWT token's 'max_users' claim.
     * It only performs a DB query to count users if needed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on user creation/invitation endpoints
        if (! $this->isUserCreationRequest($request)) {
            return $next($request);
        }

        try {
            // Parse JWT token and get payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Get max users and organization ID from JWT
            $maxUsers = $payload->get('max_users', 10);
            $organizationId = $payload->get('organization_id');

            if (! $organizationId) {
                return $next($request);
            }

            // Count current active users in the organization
            $currentUserCount = DB::table('organization_user')
                ->join('users', 'organization_user.user_id', '=', 'users.id')
                ->where('organization_user.organization_id', $organizationId)
                ->whereNull('users.deleted_at')
                ->count();

            // Check if limit would be exceeded
            if ($currentUserCount >= $maxUsers) {
                return response()->json([
                    'success' => false,
                    'message' => "User limit reached. Your plan allows {$maxUsers} users. Please upgrade your subscription to add more users.",
                    'current_users' => $currentUserCount,
                    'max_users' => $maxUsers,
                    'can_add' => false,
                ], 403);
            }

            // Add user count info to request for use in controller
            $request->merge([
                '_current_user_count' => $currentUserCount,
                '_max_users' => $maxUsers,
                '_users_remaining' => $maxUsers - $currentUserCount,
            ]);

            return $next($request);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is missing',
            ], 401);
        }
    }

    /**
     * Determine if the request is for creating/inviting a user.
     */
    private function isUserCreationRequest(Request $request): bool
    {
        $method = $request->method();
        $path = $request->path();

        // Check for user creation/invitation endpoints
        return $method === 'POST' && (
            str_contains($path, '/users') ||
            str_contains($path, '/add-user') ||
            str_contains($path, '/invite') ||
            str_contains($path, '/register')
        );
    }
}
