<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use App\Models\Organization;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'timezone' => $request->timezone ?? 'UTC',
            'language' => $request->language ?? 'en',
        ]);

        // Send email verification
        $token = $user->generateEmailVerificationToken();
        $verificationUrl = config('app.frontend_url', config('app.url'))
            .'/verify-email?token='.$token
            .'&email='.urlencode($user->email);

        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        // Note: Roles are assigned when user joins a tenant via joinOrganization()
        // New users don't have roles until they're added to an organization

        $authToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $authToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'message' => 'Registration successful. Please check your email to verify your account.',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'organization_id' => 'nullable|uuid',
            'two_factor_code' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            if (! $request->two_factor_code) {
                return response()->json([
                    'requires_2fa' => true,
                    'message' => 'Two-factor authentication code required.',
                ], 200);
            }

            // Verify 2FA code
            $google2fa = new \PragmaRX\Google2FA\Google2FA;
            $secret = $user->getTwoFactorSecret();

            // Check if it's a recovery code
            $valid = false;
            if (strlen($request->two_factor_code) > 6) {
                $valid = $user->useRecoveryCode($request->two_factor_code);
            } else {
                $valid = $google2fa->verifyKey($secret, $request->two_factor_code);
            }

            if (! $valid) {
                return response()->json([
                    'error' => 'Invalid two-factor authentication code.',
                ], 401);
            }
        }

        // Update last login information
        $user->updateLastLogin($request->ip());

        // Get all organizations user belongs to
        $organizations = $user->getActiveOrganizations()->map(function ($organization) {
            return [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'domain' => $organization->domain,
            ];
        });

        // Determine current organization
        $currentOrganization = null;
        if ($request->organization_id) {
            // Find tenant by UUID only
            $organization = Organization::find($request->organization_id);

            if ($organization && $user->belongsToOrganization($organization)) {
                $currentOrganization = $organizations->firstWhere('id', $organization->id);
            }
        }

        // If no tenant specified or invalid, use first organization
        if (! $currentOrganization && $organizations->isNotEmpty()) {
            $currentOrganization = $organizations->first();
        }

        // Get roles and permissions as slugs for JWT token
        $organization = null;
        if ($currentOrganization) {
            $organization = Organization::find($currentOrganization['id']);
        }

        $rolesSlugs = $organization ? $user->rolesInOrganization($organization)->pluck('slug')->toArray() : [];
        $permissionsSlugs = $organization ? $user->getAllPermissionsInOrganization($organization)->pluck('slug')->toArray() : [];
        $moduleSlugs = $organization ? $organization->enabledModules()->pluck('slug')->toArray() : [];
        $teamSlugs = $organization ? $user->teamsInOrganization($organization->id)->pluck('slug')->toArray() : [];
        $userModuleSlugs = $organization ? $user->getAccessibleModules($organization->id) : [];

        // Create JWT token with custom claims (only slugs)
        $customClaims = [
            'organization_id' => $currentOrganization['id'] ?? null,
            'organization_slug' => $organization?->slug,
            'is_owner' => $organization ? in_array('owner', $rolesSlugs) : false,
            'subscription_status' => $organization?->subscription_status ?? 'active',
            'max_users' => $organization?->max_users ?? 10,
            'roles' => $rolesSlugs,
            'permissions' => $permissionsSlugs,
            'modules' => $moduleSlugs,
            'teams' => $teamSlugs,
            'user_modules' => $userModuleSlugs,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        // Generate refresh token
        $refreshToken = RefreshToken::generate(
            $user,
            $currentOrganization['id'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken->token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
            'refresh_expires_in' => 14 * 24 * 60 * 60, // 2 weeks in seconds
            'user' => new UserResource($user),
            'current_organization' => $currentOrganization,
            'organizations' => $organizations,
        ])->cookie('refresh_token', $refreshToken->token, 14 * 24 * 60, null, null, false, true); // HttpOnly cookie
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // Invalidate JWT access token
            JWTAuth::invalidate(JWTAuth::getToken());

            // Revoke all refresh tokens for this user
            RefreshToken::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            return response()->json([
                'message' => 'Successfully logged out',
            ])->cookie('refresh_token', '', -1); // Delete refresh token cookie
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to logout, please try again.',
            ], 500);
        }
    }

    /**
     * Refresh JWT access token using refresh token
     */
    public function refresh(Request $request)
    {
        // Get refresh token from cookie or body
        $refreshTokenString = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if (! $refreshTokenString) {
            return response()->json([
                'error' => 'Refresh token not provided',
            ], 401);
        }

        // Find the refresh token
        $refreshToken = RefreshToken::where('token', $refreshTokenString)->first();

        if (! $refreshToken) {
            return response()->json([
                'error' => 'Invalid refresh token',
            ], 401);
        }

        // Check if token is valid
        if (! $refreshToken->isValid()) {
            return response()->json([
                'error' => 'Refresh token has expired or been revoked',
            ], 401);
        }

        // Get user and tenant context
        $user = $refreshToken->user;
        $tenantId = $refreshToken->organization_id;

        // Get roles and permissions for the tenant
        $rolesSlugs = [];
        $permissionsSlugs = [];
        $moduleSlugs = [];
        $teamSlugs = [];

        if ($tenantId) {
            $organization = Organization::find($tenantId);
            if ($organization) {
                $rolesSlugs = $user->rolesInOrganization($organization)->pluck('slug')->toArray();
                $permissionsSlugs = $user->getAllPermissionsInOrganization($organization)->pluck('slug')->toArray();
                $moduleSlugs = $organization->enabledModules()->pluck('slug')->toArray();
                $teamSlugs = $user->teamsInOrganization($organization->id)->pluck('slug')->toArray();
                $userModuleSlugs = $user->getAccessibleModules($organization->id);
            }
        }

        // Create new JWT access token
        $customClaims = [
            'organization_id' => $tenantId,
            'organization_slug' => $organization?->slug,
            'is_owner' => $organization ? in_array('owner', $rolesSlugs) : false,
            'subscription_status' => $organization?->subscription_status ?? 'active',
            'max_users' => $organization?->max_users ?? 10,
            'roles' => $rolesSlugs,
            'permissions' => $permissionsSlugs,
            'modules' => $moduleSlugs,
            'teams' => $teamSlugs,
            'user_modules' => $userModuleSlugs,
        ];

        $newAccessToken = JWTAuth::claims($customClaims)->fromUser($user);

        // Optionally: Generate new refresh token (rotation)
        $newRefreshToken = RefreshToken::generate(
            $user,
            $tenantId,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken->token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_expires_in' => 14 * 24 * 60 * 60,
            'user' => new UserResource($user),
            'organization_id' => $tenantId,
            'roles' => $rolesSlugs,
            'permissions' => $permissionsSlugs,
        ])->cookie('refresh_token', $newRefreshToken->token, 14 * 24 * 60, null, null, false, true);
    }

    /**
     * Switch to a different organization and get new JWT token
     */
    public function switchOrganization(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|uuid',
        ]);

        $user = $request->user();

        // Find tenant by UUID only
        $organization = Organization::find($request->organization_id);

        if (! $organization) {
            return response()->json([
                'error' => 'Organization not found.',
            ], 404);
        }

        if (! $user->belongsToOrganization($organization)) {
            return response()->json([
                'error' => 'You do not have access to this organization.',
            ], 403);
        }

        // Get roles and permissions as slugs only for JWT token
        $rolesSlugs = $user->rolesInOrganization($organization)->pluck('slug')->toArray();
        $permissionsSlugs = $user->getAllPermissionsInOrganization($organization)->pluck('slug')->toArray();
        $moduleSlugs = $organization->enabledModules()->pluck('slug')->toArray();
        $teamSlugs = $user->teamsInOrganization($organization->id)->pluck('slug')->toArray();
        $userModuleSlugs = $user->getAccessibleModules($organization->id);

        $currentOrganization = [
            'id' => $organization->id,
            'name' => $organization->name,
            'slug' => $organization->slug,
            'domain' => $organization->domain,
        ];

        // Invalidate old token and create new one with updated organization context
        JWTAuth::invalidate(JWTAuth::getToken());

        $customClaims = [
            'organization_id' => $currentOrganization['id'],
            'organization_slug' => $organization->slug,
            'is_owner' => in_array('owner', $rolesSlugs),
            'subscription_status' => $organization->subscription_status ?? 'active',
            'max_users' => $organization->max_users ?? 10,
            'roles' => $rolesSlugs,
            'permissions' => $permissionsSlugs,
            'modules' => $moduleSlugs,
            'teams' => $teamSlugs,
            'user_modules' => $userModuleSlugs,
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'current_organization' => $currentOrganization,
            'message' => 'Successfully switched to '.$organization->name,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        // Get JWT token payload to extract current organization
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $tenantId = $payload->get('organization_id');

            if ($tenantId) {
                $organization = Organization::find($tenantId);

                if ($organization && $user->belongsToOrganization($organization)) {
                    return response()->json([
                        'user' => new UserResource($user),
                        'current_organization' => new OrganizationResource($organization),
                        'roles' => $payload->get('roles', []),
                        'permissions' => $payload->get('permissions', []),
                        'modules' => $payload->get('modules', []),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Token parsing failed, return basic user info
        }

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
