<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckModuleAccessFromJWT
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the user has access to the requested module
     * by reading from the JWT token's 'user_modules' claim (hybrid team + role access).
     * Falls back to organization-level 'modules' if user_modules is not present.
     * This is efficient as it doesn't require a database call.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $moduleSlug  The slug of the module to check access for
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        try {
            // Parse JWT token and get payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Get user-specific modules (hybrid: team + role based)
            $userModules = $payload->get('user_modules', []);

            // Get organization modules as fallback
            $orgModules = $payload->get('modules', []);

            // Get user roles for super admin check
            $roles = $payload->get('roles', []);

            // Super admins bypass module restrictions
            if (in_array('superadmin', $roles) || in_array('admin', $roles)) {
                $request->merge(['_module_slug' => $moduleSlug]);

                return $next($request);
            }

            // Check user-specific modules first (hybrid approach)
            if (! empty($userModules)) {
                if (! in_array($moduleSlug, $userModules)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Access denied. You don't have permission to access module '{$moduleSlug}'.",
                        'module_slug' => $moduleSlug,
                        'your_modules' => $userModules,
                    ], 403);
                }
            } else {
                // Fallback: Check organization-level modules
                if (! in_array($moduleSlug, $orgModules)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Access denied. Module '{$moduleSlug}' is not enabled for this organization.",
                        'module_slug' => $moduleSlug,
                        'available_modules' => $orgModules,
                    ], 403);
                }
            }

            // Module access granted, attach to request for use in controllers
            $request->merge(['_module_slug' => $moduleSlug]);

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
