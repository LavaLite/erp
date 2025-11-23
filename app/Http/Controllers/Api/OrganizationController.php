<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations for authenticated user.
     */
    public function index(Request $request)
    {
        $organizations = $request->user()->organizations()->with('roles', 'permissions')
            ->paginate($request->get('per_page', 15));

        return OrganizationResource::collection($organizations);
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations',
            'domain' => 'nullable|string|unique:organizations',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $organization = Organization::create($request->all());

        // Add creator as first member with admin role
        $request->user()->joinOrganization($organization);

        return new OrganizationResource($organization);
    }

    /**
     * Display the specified organization.
     */
    public function show(Request $request, string $id)
    {
        $organization = Organization::with('users', 'roles', 'permissions')->findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        return new OrganizationResource($organization);
    }

    /**
     * Update the specified organization.
     */
    public function update(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:organizations,slug,'.$id,
            'domain' => 'nullable|string|unique:organizations,domain,'.$id,
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'settings' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $organization->update($request->all());

        return new OrganizationResource($organization);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        $organization->delete();

        return response()->json(['message' => __('messages.organization.deleted')]);
    }

    /**
     * Add user to organization.
     */
    public function addUser(Request $request, string $organizationId)
    {
        $request->validate([
            'user_id' => 'required_without:email|exists:users,id',
            'email' => 'required_without:user_id|email',
            'role' => 'nullable|string|exists:roles,slug',
        ]);

        $organization = Organization::findOrFail($organizationId);
        
        // Find user by user_id or email
        if ($request->user_id) {
            $user = User::findOrFail($request->user_id);
        } else {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'message' => __('User with this email does not exist'),
                ], 404);
            }
        }

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        // Check if user is already in organization
        if ($user->belongsToOrganization($organization)) {
            return response()->json([
                'message' => __('User is already a member of this organization'),
            ], 422);
        }

        $user->joinOrganization($organization);

        // Assign role if provided, default to 'member'
        $roleSlug = $request->role ?? 'member';
        $role = \App\Models\Role::where('slug', $roleSlug)
            ->where(function ($query) use ($organization) {
                $query->where('organization_id', $organization->id)
                      ->orWhereNull('organization_id');
            })
            ->first();

        if ($role) {
            $user->assignRole($role, $organization);
        }

        return response()->json([
            'message' => __('messages.organization.user_added'),
            'organization' => new OrganizationResource($organization->load('users')),
        ]);
    }

    /**
     * Invite user to organization by email or user_id.
     */
    public function inviteUser(Request $request, string $organizationId)
    {
        $request->validate([
            'user_id' => 'required_without:email|exists:users,id',
            'email' => 'required_without:user_id|email',
            'role' => 'nullable|string|exists:roles,slug',
        ]);

        $organization = Organization::findOrFail($organizationId);

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        // Determine email
        $email = $request->email;
        
        // Find user by user_id or email
        if ($request->user_id) {
            $user = User::findOrFail($request->user_id);
            $email = $user->email;
        } else {
            $user = User::where('email', $request->email)->first();
        }

        if ($user) {
            // User exists - check if already in organization
            if ($user->belongsToOrganization($organization)) {
                return response()->json([
                    'message' => __('User is already a member of this organization'),
                ], 422);
            }

            // Add existing user to organization
            $user->joinOrganization($organization);

            // Assign role (default to 'member' if not provided)
            $roleSlug = $request->role ?? 'member';
            $role = \App\Models\Role::where('slug', $roleSlug)
                ->where(function ($query) use ($organization) {
                    $query->where('organization_id', $organization->id)
                          ->orWhereNull('organization_id');
                })
                ->first();

            if ($role) {
                $user->assignRole($role, $organization);
            }

            // Send notification email to existing user
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\OrganizationInvitation(
                    \App\Models\OrganizationInvitation::create([
                        'organization_id' => $organization->id,
                        'invited_by' => $request->user()->id,
                        'email' => $email,
                        'token' => \App\Models\OrganizationInvitation::generateToken(),
                        'role' => $roleSlug,
                        'status' => 'accepted',
                        'expires_at' => now()->addDays(7),
                        'accepted_at' => now(),
                    ])
                )
            );
            
            return response()->json([
                'message' => __('User added to organization successfully'),
                'user_exists' => true,
            ]);
        } else {
            // User doesn't exist - create invitation and send email
            
            // Check for existing pending invitation
            $existingInvitation = \App\Models\OrganizationInvitation::where('email', $email)
                ->where('organization_id', $organization->id)
                ->where('status', 'pending')
                ->first();

            if ($existingInvitation && !$existingInvitation->isExpired()) {
                return response()->json([
                    'message' => __('An invitation has already been sent to this email address'),
                ], 422);
            }

            // Create new invitation
            $invitation = \App\Models\OrganizationInvitation::create([
                'organization_id' => $organization->id,
                'invited_by' => $request->user()->id,
                'email' => $email,
                'token' => \App\Models\OrganizationInvitation::generateToken(),
                'role' => $request->role ?? 'member',
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]);

            // Send invitation email
            \Illuminate\Support\Facades\Mail::to($email)->send(
                new \App\Mail\OrganizationInvitation($invitation)
            );
            
            return response()->json([
                'message' => __('Invitation sent to user email'),
                'user_exists' => false,
                'invitation_id' => $invitation->id,
            ]);
        }
    }

    /**
     * Get users in organization.
     */
    public function getUsers(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        $users = $organization->users()
            ->with(['roles' => function ($query) use ($organization) {
                $query->where('roles.organization_id', $organization->id)
                    ->orWhereNull('roles.organization_id');
            }])
            ->paginate($request->get('per_page', 15));

        return \App\Http\Resources\UserResource::collection($users);
    }

    /**
     * Remove user from organization.
     */
    public function removeUser(Request $request, string $organizationId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $user = User::findOrFail($request->user_id);

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        $user->leaveOrganization($organization);

        return response()->json([
            'message' => __('messages.organization.user_removed'),
        ]);
    }

    /**
     * Get current organization context.
     */
    public function current(Request $request)
    {
        $organization = $request->attributes->get('organization') ?? app('organization');

        if (! $organization) {
            return response()->json(['message' => __('messages.auth.no_org_context')], 404);
        }

        return new OrganizationResource($organization->load('roles', 'permissions'));
    }

    /**
     * Get user's roles and permissions in a specific organization.
     */
    public function userContext(Request $request, string $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => __('messages.auth.unauthorized')], 403);
        }

        $roles = $request->user()->rolesInOrganization($organization)->get();
        $permissions = $request->user()->getAllPermissionsInOrganization($organization);

        return response()->json([
            'organization' => new OrganizationResource($organization),
            'roles' => RoleResource::collection($roles),
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }
}
