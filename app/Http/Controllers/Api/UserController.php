<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Get tenant from JWT token for filtering
        $organizationId = null;
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $organizationId = $payload->get('organization_id');
        } catch (\Exception $e) {
            // JWT not available
        }

        $query = User::with(['roles' => function ($query) use ($organizationId) {
            if ($organizationId) {
                $query->wherePivot('organization_id', $organizationId);
            }
        }, 'organizations']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by organization
        if ($organizationId && $request->get('filter_by_organization', true)) {
            $query->whereHas('organizations', function ($q) use ($organizationId) {
                $q->where('organizations.id', $organizationId);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->get('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'bio' => 'nullable|string|max:1000',
            'send_welcome_email' => 'nullable|boolean',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'timezone' => $request->timezone ?? 'UTC',
            'language' => $request->language ?? 'en',
            'bio' => $request->bio,
        ]);

        // Optionally send welcome email
        if ($request->send_welcome_email) {
            // Add your welcome email logic here
        }

        return response()->json([
            'message' => __('User created successfully'),
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::with('roles.permissions', 'permissions')->findOrFail($id);

        // Get tenant from JWT token
        $allPermissions = [];
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $organizationId = $payload->get('organization_id');

            if ($organizationId) {
                $organization = Organization::find($organizationId);
                if ($organization) {
                    $allPermissions = $user->getAllPermissionsInTenant($organization);
                }
            }
        } catch (\Exception $e) {
            // JWT not available or invalid, return empty permissions
        }

        return response()->json([
            'user' => new UserResource($user->load('roles', 'organizations', 'teams')),
            'all_permissions' => $allPermissions,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'bio' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'date_of_birth',
            'gender',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'postal_code',
            'country',
            'timezone',
            'language',
            'bio',
            'is_active',
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => __('User updated successfully'),
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        $currentUser = auth('api')->user();
        if ($currentUser && $user->id === $currentUser->id) {
            return response()->json([
                'error' => __('You cannot delete your own account'),
            ], 422);
        }

        // Delete user avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'message' => __('User deleted successfully'),
        ]);
    }

    /**
     * Get current authenticated user with complete profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return new UserResource($user->load('roles', 'permissions', 'organizations'));
    }

    /**
     * Get current authenticated user's profile (alias for me()).
     */
    public function getProfile(Request $request)
    {
        return $this->me($request);
    }

    /**
     * Update the authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'bio' => 'nullable|string|max:1000',
            'preferences' => 'nullable|array',
        ]);

        $user->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'date_of_birth',
            'gender',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'postal_code',
            'country',
            'timezone',
            'language',
            'bio',
            'preferences',
        ]));

        return response()->json([
            'message' => __('messages.user.profile_updated'),
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $avatarPath]);

        return response()->json([
            'message' => __('messages.user.avatar_uploaded'),
            'avatar' => Storage::url($avatarPath),
        ]);
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'message' => __('messages.user.avatar_deleted'),
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => __('messages.password.current_incorrect'),
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => __('messages.user.password_changed'),
        ]);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
        ]);

        $user = $request->user();

        $user->update([
            'preferences' => array_merge($user->preferences ?? [], $request->preferences),
        ]);

        return response()->json([
            'message' => __('messages.user.preferences_updated'),
            'preferences' => $user->preferences,
        ]);
    }
}
