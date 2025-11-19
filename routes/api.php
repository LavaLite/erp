<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RoleModuleController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\OrganizationUsageController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle.auth');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.auth');
Route::post('/refresh', [AuthController::class, 'refresh']); // Refresh doesn't need auth

// Email verification routes
Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1');

// Password reset routes
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Two-factor authentication verification (public - for login flow)
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:5,1');

// Protected routes - using JWT authentication
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/switch-organization', [AuthController::class, 'switchOrganization']);

    // User profile management
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar']);
    Route::put('/profile/password', [UserController::class, 'changePassword']);
    Route::put('/profile/preferences', [UserController::class, 'updatePreferences']);

    // Email verification (authenticated)
    Route::post('/email/send-verification', [EmailVerificationController::class, 'send']);

    // Two-factor authentication routes
    Route::prefix('2fa')->group(function () {
        Route::post('/enable', [TwoFactorController::class, 'enable']);
        Route::post('/confirm', [TwoFactorController::class, 'confirm']);
        Route::post('/disable', [TwoFactorController::class, 'disable']);
        Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes']);
    });

    // Tenant management routes
    Route::prefix('organizations')->group(function () {
        Route::get('/', [OrganizationController::class, 'index']);
        Route::post('/', [OrganizationController::class, 'store']);
        Route::get('/current', [OrganizationController::class, 'current']);
        Route::get('/{id}', [OrganizationController::class, 'show']);
        Route::put('/{id}', [OrganizationController::class, 'update']);
        Route::delete('/{id}', [OrganizationController::class, 'destroy']);

        // Tenant user management
        Route::get('/{id}/users', [OrganizationController::class, 'getUsers']);
        Route::post('/{id}/users', [OrganizationController::class, 'addUser']);
        Route::post('/{id}/add-user', [OrganizationController::class, 'addUser']);
        Route::post('/{id}/remove-user', [OrganizationController::class, 'removeUser']);
        Route::get('/{id}/context', [OrganizationController::class, 'userContext']);

        // Organization module management
        Route::get('/{id}/modules', [ModuleController::class, 'getOrganizationModules']);
        Route::post('/{id}/modules', [ModuleController::class, 'enableModulesForOrganization']);
        Route::post('/{id}/modules/{moduleId}/enable', [ModuleController::class, 'enableForOrganization']);
        Route::post('/{id}/modules/{moduleId}/disable', [ModuleController::class, 'disableForOrganization']);
    });

    // Billing integration routes - for billing service to query usage and update subscriptions
    Route::prefix('billing')->group(function () {
        // Usage and subscription data endpoints
        Route::get('/organizations/{organizationId}/usage', [OrganizationUsageController::class, 'getUsage']);
        Route::get('/organizations/{organizationId}/users/count', [OrganizationUsageController::class, 'getUsersCount']);
        Route::get('/organizations/{organizationId}/modules', [OrganizationUsageController::class, 'getModules']);
        Route::get('/organizations/{organizationId}/subscription', [OrganizationUsageController::class, 'getSubscriptionStatus']);
        Route::get('/organizations/{organizationId}/limit', [OrganizationUsageController::class, 'getLimits']);
        Route::put('/organizations/{organizationId}/limit', [OrganizationUsageController::class, 'updateLimits']);

        // Update subscription from billing service
        Route::put('/organizations/{organizationId}/subscription', [OrganizationUsageController::class, 'updateSubscription']);
        Route::post('/organizations/{organizationId}/suspend', [OrganizationUsageController::class, 'suspendOrganization']);
        Route::post('/organizations/{organizationId}/activate', [OrganizationUsageController::class, 'activateOrganization']);
        Route::post('/organizations/{organizationId}/reactivate', [OrganizationUsageController::class, 'activateOrganization']);
        Route::post('/organizations/{organizationId}/cancel', [OrganizationUsageController::class, 'cancelOrganization']);

        // Check limits
        Route::post('/organizations/{organizationId}/check-user-limit', [OrganizationUsageController::class, 'checkUserLimit']);

        // Bulk operations for efficiency
        Route::post('/organizations/bulk-usage', [OrganizationUsageController::class, 'getBulkUsage']);
    });

    // Module management routes (global admin only)
    Route::middleware(['role:globaladmin'])->prefix('modules')->group(function () {
        Route::get('/', [ModuleController::class, 'index']);
        Route::post('/', [ModuleController::class, 'store']);
        Route::get('/{id}', [ModuleController::class, 'show']);
        Route::put('/{id}', [ModuleController::class, 'update']);
        Route::delete('/{id}', [ModuleController::class, 'destroy']);
    });

    // Global role and permission routes (no organization context, will be handled by controllers)
    // These routes are checked BEFORE the organization-scoped ones below
    Route::prefix('roles')->group(function () {
        // Allow POST to /api/roles for creating global or organization-scoped roles
        // Controller will determine if organization context is required based on organization_id parameter
        Route::post('/', [RoleController::class, 'store']);
        Route::post('/{id}/assign-user', [RoleController::class, 'assignToUser']);
    });

    Route::prefix('permissions')->group(function () {
        Route::post('/', [PermissionController::class, 'store']);
    });

    // Team management routes (organization-scoped)
    Route::prefix('teams')->group(function () {
        // List all teams in organization
        Route::get('/', [App\Http\Controllers\Api\TeamController::class, 'index']);

        // Get user's teams
        Route::get('/my-teams', [App\Http\Controllers\Api\TeamController::class, 'myTeams']);
        Route::get('/my', [App\Http\Controllers\Api\TeamController::class, 'myTeams']);

        // Create team
        Route::post('/', [App\Http\Controllers\Api\TeamController::class, 'store']);

        // Get single team
        Route::get('/{id}', [App\Http\Controllers\Api\TeamController::class, 'show']);

        // Update team
        Route::put('/{id}', [App\Http\Controllers\Api\TeamController::class, 'update']);

        // Delete team
        Route::delete('/{id}', [App\Http\Controllers\Api\TeamController::class, 'destroy']);

        // Team member management
        Route::post('/{id}/members', [App\Http\Controllers\Api\TeamController::class, 'addMember']);
        Route::delete('/{id}/members', [App\Http\Controllers\Api\TeamController::class, 'removeMember']);
        Route::patch('/{id}/members/role', [App\Http\Controllers\Api\TeamController::class, 'updateMemberRole']);

        // Assign modules to team
        Route::post('/{id}/modules', [App\Http\Controllers\Api\TeamController::class, 'assignModules']);
    });

    // Tenant-scoped routes (require tenant context)
    Route::middleware(['organization'])->group(function () {
        // User management routes (admin only in tenant)
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
        });

        // Role management routes (admin only in tenant)
        Route::middleware(['role:admin'])->prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::get('/{id}', [RoleController::class, 'show']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);

            // Assign/remove roles to users - handled above without middleware
            // Route::post('/{id}/assign-user', [RoleController::class, 'assignToUser']);
            Route::post('/{id}/remove-user', [RoleController::class, 'removeFromUser']);

            // Assign/remove permissions to roles
            Route::post('/{id}/assign-permission', [RoleController::class, 'assignPermission']);
            Route::post('/{id}/remove-permission', [RoleController::class, 'removePermission']);

            // Role-Module management (hybrid access control)
            Route::get('/{id}/modules', [RoleModuleController::class, 'index']);
            Route::post('/{id}/modules', [RoleModuleController::class, 'assignModules']);
            Route::post('/{id}/modules/{moduleId}', [RoleModuleController::class, 'addModule']);
            Route::delete('/{id}/modules/{moduleId}', [RoleModuleController::class, 'removeModule']);
        });

        // Available modules for organization
        Route::get('/organizations/{organizationId}/available-modules', [RoleModuleController::class, 'availableModules']);

        // Permission management routes (admin only in tenant)
        Route::middleware(['role:admin'])->prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/{id}', [PermissionController::class, 'show']);
            Route::put('/{id}', [PermissionController::class, 'update']);
            Route::delete('/{id}', [PermissionController::class, 'destroy']);

            // Assign/remove permissions directly to users
            Route::post('/{id}/assign-user', [PermissionController::class, 'assignToUser']);
            Route::post('/{id}/remove-user', [PermissionController::class, 'removeFromUser']);
        });
    });
});
