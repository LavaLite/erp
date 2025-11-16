<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckOwnerAccess
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the user is an owner of the organization
     * by reading directly from the JWT token's 'is_owner' claim.
     * This is more efficient as it doesn't require a database call.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse JWT token and get payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Get is_owner flag from JWT claims
            $isOwner = $payload->get('is_owner', false);

            // Check if user is owner
            if (! $isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only organization owners can perform this action.',
                ], 403);
            }

            // User is owner, proceed
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
}
