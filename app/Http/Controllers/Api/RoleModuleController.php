<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleModuleController extends Controller
{
    /**
     * Get modules assigned to a role.
     *
     * GET /api/roles/{roleId}/modules
     */
    public function index(Request $request, $roleId)
    {
        $user = $request->user();
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.org_context_required'),
            ], 400);
        }

        $role = Role::where('id', $roleId)
            ->where('organization_id', $organizationId)
            ->first();

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.not_found_in_org'),
            ], 404);
        }

        // Check if user can manage roles in this organization
        if (! $user->hasRoleInOrganization(['owner', 'admin'], $organizationId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.view_modules_denied'),
            ], 403);
        }

        $modules = $role->modules()
            ->wherePivot('organization_id', $organizationId)
            ->wherePivot('has_access', true)
            ->get(['modules.id', 'modules.name', 'modules.slug', 'modules.category']);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ],
                'modules' => $modules,
                'module_count' => $modules->count(),
            ],
        ]);
    }

    /**
     * Assign modules to a role.
     *
     * POST /api/roles/{roleId}/modules
     * Body: { "module_ids": [1, 2, 3] }
     */
    public function assignModules(Request $request, $roleId)
    {
        $user = $request->user();
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.org_context_required'),
            ], 400);
        }

        $role = Role::where('id', $roleId)
            ->where('organization_id', $organizationId)
            ->first();

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.not_found_in_org'),
            ], 404);
        }

        // Check if user can manage roles
        if (! $user->hasRoleInOrganization(['owner', 'admin'], $organizationId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.manage_modules_denied'),
            ], 403);
        }

        $request->validate([
            'module_ids' => 'required|array|min:1',
            'module_ids.*' => 'required|integer|exists:modules,id',
        ]);

        // Verify all modules are enabled for the organization
        $orgModuleIds = DB::table('organization_module')
            ->where('organization_id', $organizationId)
            ->where('is_enabled', true)
            ->pluck('module_id')
            ->toArray();

        $invalidModules = array_diff($request->module_ids, $orgModuleIds);

        if (! empty($invalidModules)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.module.some_not_enabled'),
                'invalid_module_ids' => $invalidModules,
            ], 422);
        }

        // Assign modules to role
        $role->syncModules($request->module_ids, $user->id);

        $assignedModules = $role->modules()
            ->wherePivot('organization_id', $organizationId)
            ->get(['modules.id', 'modules.name', 'modules.slug']);

        return response()->json([
            'success' => true,
            'message' => __('messages.role.modules_assigned'),
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ],
                'modules' => $assignedModules,
                'module_count' => $assignedModules->count(),
            ],
        ]);
    }

    /**
     * Add single module to role.
     *
     * POST /api/roles/{roleId}/modules/{moduleId}
     */
    public function addModule(Request $request, $roleId, $moduleId)
    {
        $user = $request->user();
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.org_context_required'),
            ], 400);
        }

        $role = Role::where('id', $roleId)
            ->where('organization_id', $organizationId)
            ->first();

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.not_found_in_org'),
            ], 404);
        }

        $module = Module::find($moduleId);

        if (! $module) {
            return response()->json([
                'success' => false,
                'message' => __('messages.module.not_found'),
            ], 404);
        }

        // Check if user can manage roles
        if (! $user->hasRoleInOrganization(['owner', 'admin'], $organizationId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.manage_modules_denied'),
            ], 403);
        }

        // Verify module is enabled for organization
        $isEnabled = DB::table('organization_module')
            ->where('organization_id', $organizationId)
            ->where('module_id', $moduleId)
            ->where('is_enabled', true)
            ->exists();

        if (! $isEnabled) {
            return response()->json([
                'success' => false,
                'message' => __('messages.module.not_enabled'),
            ], 422);
        }

        // Add module to role
        $role->giveModuleAccess($moduleId, $user->id);

        return response()->json([
            'success' => true,
            'message' => __('messages.role.module_added', ['module' => $module->name, 'role' => $role->name]),
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ],
                'module' => [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                ],
            ],
        ]);
    }

    /**
     * Remove module from role.
     *
     * DELETE /api/roles/{roleId}/modules/{moduleId}
     */
    public function removeModule(Request $request, $roleId, $moduleId)
    {
        $user = $request->user();
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.org_context_required'),
            ], 400);
        }

        $role = Role::where('id', $roleId)
            ->where('organization_id', $organizationId)
            ->first();

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.not_found_in_org'),
            ], 404);
        }

        $module = Module::find($moduleId);

        if (! $module) {
            return response()->json([
                'success' => false,
                'message' => __('messages.module.not_found'),
            ], 404);
        }

        // Check if user can manage roles
        if (! $user->hasRoleInOrganization(['owner', 'admin'], $organizationId)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role.manage_modules_denied'),
            ], 403);
        }

        // Remove module from role
        $role->revokeModuleAccess($moduleId);

        return response()->json([
            'success' => true,
            'message' => __('messages.role.module_removed', ['module' => $module->name, 'role' => $role->name]),
        ]);
    }

    /**
     * Get all available modules for the organization.
     *
     * GET /api/organizations/{organizationId}/available-modules
     */
    public function availableModules(Request $request, $organizationId)
    {
        $user = $request->user();

        // Check if user belongs to organization
        if (! $user->organizations()->where('organizations.id', $organizationId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.no_org_access'),
            ], 403);
        }

        // Get organization's enabled modules
        $modules = DB::table('organization_module')
            ->join('modules', 'organization_module.module_id', '=', 'modules.id')
            ->where('organization_module.organization_id', $organizationId)
            ->where('organization_module.is_enabled', true)
            ->select('modules.id', 'modules.name', 'modules.slug', 'modules.category', 'modules.description')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'modules' => $modules,
                'count' => $modules->count(),
            ],
        ]);
    }
}
