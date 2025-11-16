<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the organization's subscription is active
     * by reading directly from the JWT token's 'subscription_status' claim.
     * This is more efficient as it doesn't require a database call.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse JWT token and get payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Get subscription status from JWT claims
            $subscriptionStatus = $payload->get('subscription_status', 'active');

            // Check if subscription is active
            if ($subscriptionStatus === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your organization subscription has been suspended. Please contact support or update your payment method.',
                    'subscription_status' => 'suspended',
                ], 403);
            }

            if ($subscriptionStatus === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your organization subscription has been cancelled. Please renew your subscription to continue.',
                    'subscription_status' => 'cancelled',
                ], 403);
            }

            // For trial status, allow access but add warning header
            if ($subscriptionStatus === 'trial') {
                $response = $next($request);
                $response->headers->set('X-Subscription-Status', 'trial');

                return $response;
            }

            // Subscription is active, proceed
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
