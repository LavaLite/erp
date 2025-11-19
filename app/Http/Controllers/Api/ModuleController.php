<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleResource;
use App\Models\Module;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    /**
     * Display a listing of all available modules.
     */
    public function index(Request $request)
    {
        $query = Module::query();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            if ($request->boolean('is_active')) {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by core modules
        if ($request->has('is_core')) {
            if ($request->boolean('is_core')) {
                $query->core();
            } else {
                $query->nonCore();
            }
        }

        $modules = $query->orderBy('display_order')->paginate($request->get('per_page', 15));

        return ModuleResource::collection($modules);
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:modules,slug|max:255',
            'code' => 'required|string|unique:modules,code|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer',
            'dependencies' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_core' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'requires_license' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $module = Module::create($validator->validated());

        return new ModuleResource($module);
    }

    /**
     * Display the specified module.
     */
    public function show($id)
    {
        $module = Module::findOrFail($id);

        return new ModuleResource($module);
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:modules,slug,'.$id.'|max:255',
            'code' => 'sometimes|string|unique:modules,code,'.$id.'|max:50',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer',
            'dependencies' => 'nullable|array',
            'metadata' => 'nullable|array',
            'is_core' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'requires_license' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $module->update($validator->validated());

        return new ModuleResource($module);
    }

    /**
     * Remove the specified module.
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return response()->json(['message' => __('messages.module.deleted')], 200);
    }

    /**
     * Get modules for a specific organization.
     */
    public function getOrganizationModules(Request $request, $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        $query = $organization->modules();

        // Filter by enabled status if provided
        if ($request->has('is_enabled')) {
            $query->wherePivot('is_enabled', $request->boolean('is_enabled'));
        }

        $modules = $query->orderBy('display_order')->paginate($request->get('per_page', 15));

        return ModuleResource::collection($modules);
    }

    /**
     * Enable modules for an organization (bulk or single).
     */
    public function enableModulesForOrganization(Request $request, $organizationId)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required_without:module_ids|string|exists:modules,id',
            'module_ids' => 'required_without:module_id|array',
            'module_ids.*' => 'string|exists:modules,id',
            'settings' => 'nullable|array',
            'limits' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization = Organization::findOrFail($organizationId);

        // Handle single module or multiple modules
        $moduleIds = $request->module_ids ?? [$request->module_id];

        foreach ($moduleIds as $moduleId) {
            $module = Module::findOrFail($moduleId);
            $organization->enableModule(
                $module,
                $request->settings ?? [],
                $request->limits ?? [],
                $request->expires_at
            );
        }

        return response()->json([
            'message' => count($moduleIds) === 1
                ? __('messages.module.enabled')
                : __('messages.module.enabled_plural'),
            'enabled_count' => count($moduleIds),
        ], 200);
    }

    /**
     * Enable a module for an organization.
     */
    public function enableForOrganization(Request $request, $organizationId, $moduleId)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'nullable|array',
            'limits' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization = Organization::findOrFail($organizationId);
        $module = Module::findOrFail($moduleId);

        $organization->enableModule(
            $module,
            $request->settings,
            $request->limits,
            $request->expires_at
        );

        return response()->json([
            'message' => __('messages.module.enabled'),
            'module' => new ModuleResource($module->fresh()),
        ], 200);
    }

    /**
     * Disable a module for an organization.
     */
    public function disableForOrganization($organizationId, $moduleId)
    {
        $organization = Organization::findOrFail($organizationId);
        $module = Module::findOrFail($moduleId);

        $organization->disableModule($module);

        return response()->json([
            'message' => __('messages.module.disabled'),
        ], 200);
    }
}
