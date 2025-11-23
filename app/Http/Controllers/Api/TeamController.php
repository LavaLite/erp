<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Get all teams in current organization.
     */
    public function index(Request $request)
    {
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json(['error' => __('messages.auth.org_context_required')], 400);
        }

        $teams = Team::inOrganization($organizationId)
            ->with(['users', 'creator', 'parentTeam', 'subTeams'])
            ->withCount('users')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'organization_id' => $team->organization_id,
                    'parent_team_id' => $team->parent_team_id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'description' => $team->description,
                    'color' => $team->color,
                    'is_active' => $team->is_active,
                    'metadata' => $team->metadata,
                    'created_by' => $team->created_by,
                    'created_at' => $team->created_at,
                    'updated_at' => $team->updated_at,
                    'users_count' => $team->users_count,
                    'members_count' => $team->users_count, // Add this for frontend compatibility
                    'creator' => $team->creator,
                    'parent_team' => $team->parentTeam,
                    'sub_teams' => $team->subTeams,
                    'users' => $team->users,
                ];
            });

        return response()->json([
            'teams' => $teams,
            'total' => $teams->count(),
        ]);
    }

    /**
     * Get user's teams in current organization.
     */
    public function myTeams(Request $request)
    {
        $user = Auth::user();
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json(['error' => __('messages.auth.org_context_required')], 400);
        }

        $teams = $user->teamsInOrganization($organizationId)
            ->with(['users', 'parentTeam'])
            ->withCount('users')
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'description' => $team->description,
                    'color' => $team->color,
                    'my_role' => $team->pivot->role,
                    'joined_at' => $team->pivot->joined_at,
                    'members_count' => $team->users_count,
                ];
            });

        return response()->json(['teams' => $teams]);
    }

    /**
     * Create a new team.
     */
    public function store(Request $request)
    {
        $organizationId = $request->header('X-Organization-ID');

        if (! $organizationId) {
            return response()->json(['error' => __('messages.auth.org_context_required')], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-F]{6}$/i',
            'parent_team_id' => 'nullable|exists:teams,id',
            'metadata' => 'nullable|array',
        ]);

        // Auto-generate slug if not provided
        if (! isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Check slug uniqueness in organization
        $existingTeam = Team::where('organization_id', $organizationId)
            ->where('slug', $validated['slug'])
            ->first();

        if ($existingTeam) {
            return response()->json([
                'error' => __('messages.team.slug_exists'),
            ], 422);
        }

        $team = Team::create([
            ...$validated,
            'organization_id' => $organizationId,
            'created_by' => Auth::id(),
        ]);

        // Automatically add creator as team owner
        $team->addMember(Auth::user(), 'owner');

        return response()->json([
            'message' => __('messages.team.created'),
            'team' => $team->load('users'),
        ], 201);
    }

    /**
     * Get single team details.
     */
    public function show($id)
    {
        $team = Team::with(['users', 'creator', 'parentTeam', 'subTeams', 'modules'])
            ->withCount('users')
            ->findOrFail($id);

        // Check if user has access to this team's organization
        $user = Auth::user();
        if (! $user->belongsToOrganization($team->organization_id)) {
            return response()->json(['error' => __('messages.auth.unauthorized')], 403);
        }

        return response()->json(['team' => $team]);
    }

    /**
     * Update team.
     */
    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        // Check if user is team leader or org admin
        $user = Auth::user();
        if (! $user->isTeamLeader($team) && ! $user->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-F]{6}$/i',
            'is_active' => 'sometimes|boolean',
            'metadata' => 'nullable|array',
        ]);

        // Check slug uniqueness if updating slug
        if (isset($validated['slug']) && $validated['slug'] !== $team->slug) {
            $existingTeam = Team::where('organization_id', $team->organization_id)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $team->id)
                ->first();

            if ($existingTeam) {
                return response()->json([
                    'error' => __('messages.team.slug_exists'),
                ], 422);
            }
        }

        $team->update($validated);

        return response()->json([
            'message' => __('messages.team.updated'),
            'team' => $team->fresh(),
        ]);
    }

    /**
     * Delete team.
     */
    public function destroy($id)
    {
        $team = Team::findOrFail($id);

        // Check if user is org admin
        $user = Auth::user();
        if (! $user->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.only_admin_delete')], 403);
        }

        // Check if team has sub-teams
        if ($team->subTeams()->count() > 0) {
            return response()->json([
                'error' => __('messages.team.has_subteams'),
            ], 422);
        }

        $team->delete();

        return response()->json(['message' => __('messages.team.deleted')]);
    }

    /**
     * Get team members.
     */
    public function getMembers(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        // Check if user has access to this team's organization
        $user = Auth::user();
        if (! $user->belongsToOrganization($team->organization_id)) {
            return response()->json(['error' => __('messages.auth.unauthorized')], 403);
        }

        $members = $team->users()
            ->withPivot(['role', 'joined_at', 'invited_by'])
            ->with(['roles' => function ($query) use ($team) {
                $query->where('roles.organization_id', $team->organization_id)
                    ->orWhereNull('roles.organization_id');
            }])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'members' => $members->items(),
            'total' => $members->total(),
            'current_page' => $members->currentPage(),
            'per_page' => $members->perPage(),
            'last_page' => $members->lastPage(),
        ]);
    }

    /**
     * Add member to team.
     */
    public function addMember(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required_without:email|exists:users,id',
            'email' => 'required_without:user_id|email',
            'role' => ['nullable', Rule::in(['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])],
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        // Find user by user_id or email
        if (isset($validated['user_id'])) {
            $user = User::findOrFail($validated['user_id']);
        } else {
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                return response()->json(['error' => __('User with this email does not exist. Use invite endpoint instead.')], 404);
            }
        }

        // Check if user belongs to the organization
        if (! $user->belongsToOrganization($team->organization_id)) {
            return response()->json(['error' => __('messages.user.not_member')], 422);
        }

        // Check if already a member
        if ($team->hasMember($user)) {
            return response()->json(['error' => __('messages.team.already_member')], 422);
        }

        // Default role to 'member' if not provided
        $role = $validated['role'] ?? 'member';
        $team->addMember($user, $role, $requester);

        return response()->json([
            'message' => __('messages.team.member_added'),
            'team' => $team->load('users'),
        ]);
    }

    /**
     * Invite member to team by email or user_id.
     */
    public function inviteMember(Request $request, $id)
    {
        $team = Team::with('organization')->findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required_without:email|exists:users,id',
            'email' => 'required_without:user_id|email',
            'role' => ['nullable', Rule::in(['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])],
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        // Determine email
        $email = $validated['email'] ?? null;

        // Find user by user_id or email
        if (isset($validated['user_id'])) {
            $user = User::findOrFail($validated['user_id']);
            $email = $user->email;
        } else {
            $user = User::where('email', $validated['email'])->first();
        }

        $role = $validated['role'] ?? 'member';

        if ($user) {
            // User exists
            
            // Check if already a team member
            if ($team->hasMember($user)) {
                return response()->json([
                    'error' => __('messages.team.already_member'),
                ], 422);
            }

            // Check if user belongs to organization
            if (!$user->belongsToOrganization($team->organization)) {
                return response()->json([
                    'error' => __('User must be a member of the organization first'),
                ], 422);
            }

            // Add user directly to team
            $team->addMember($user, $role, $requester);

            // Send notification email
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\TeamInvitation(
                    \App\Models\TeamInvitation::create([
                        'team_id' => $team->id,
                        'invited_by' => $requester->id,
                        'email' => $email,
                        'token' => \App\Models\TeamInvitation::generateToken(),
                        'role' => $role,
                        'status' => 'accepted',
                        'expires_at' => now()->addDays(7),
                        'accepted_at' => now(),
                    ])
                )
            );

            return response()->json([
                'message' => __('User added to team successfully'),
                'user_exists' => true,
            ]);
        } else {
            // User doesn't exist - create invitation
            
            // Check for existing pending invitation
            $existingInvitation = \App\Models\TeamInvitation::where('email', $email)
                ->where('team_id', $team->id)
                ->where('status', 'pending')
                ->first();

            if ($existingInvitation && !$existingInvitation->isExpired()) {
                return response()->json([
                    'error' => __('An invitation has already been sent to this email address'),
                ], 422);
            }

            // Create new invitation
            $invitation = \App\Models\TeamInvitation::create([
                'team_id' => $team->id,
                'invited_by' => $requester->id,
                'email' => $email,
                'token' => \App\Models\TeamInvitation::generateToken(),
                'role' => $role,
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]);

            // Send invitation email
            \Illuminate\Support\Facades\Mail::to($email)->send(
                new \App\Mail\TeamInvitation($invitation)
            );

            return response()->json([
                'message' => __('Invitation sent to user email'),
                'user_exists' => false,
                'invitation_id' => $invitation->id,
            ]);
        }
    }

    /**
     * Remove member from team.
     */
    public function removeMember(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        $user = User::findOrFail($validated['user_id']);

        // Prevent removing the last leader (owner/admin/manager)
        if ($user->isTeamLeader($team) && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => __('messages.team.last_leader')], 422);
        }

        $team->removeMember($user);

        return response()->json([
            'message' => __('messages.team.member_removed'),
            'team' => $team->load('users'),
        ]);
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])],
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        $user = User::findOrFail($validated['user_id']);

        // Check if user is a team member
        if (! $team->hasMember($user)) {
            return response()->json(['error' => __('messages.team.not_member')], 422);
        }

        // Prevent demoting the last leader (owner/admin/manager)
        $isCurrentlyLeader = $user->isTeamLeader($team);
        $isNewRoleLeader = in_array($validated['role'], ['owner', 'admin', 'manager']);

        if ($isCurrentlyLeader && ! $isNewRoleLeader && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => __('messages.team.demote_last_leader')], 422);
        }

        $team->updateMemberRole($user, $validated['role']);

        return response()->json([
            'message' => __('messages.team.member_updated'),
            'team' => $team->load('users'),
        ]);
    }

    /**
     * Assign modules to team.
     */
    public function assignModules(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        // Check if requester is org admin
        $requester = Auth::user();
        if (! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.only_admin_assign')], 403);
        }

        // Sync modules
        $team->modules()->sync($validated['module_ids']);

        return response()->json([
            'message' => __('messages.team.modules_assigned'),
            'team' => $team->load('modules'),
        ]);
    }

    /**
     * Remove member from team (RESTful route with user ID in path).
     */
    public function removeMemberById(Request $request, $id, $userId)
    {
        $team = Team::findOrFail($id);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        $user = User::findOrFail($userId);

        // Prevent removing the last leader (owner/admin/manager)
        if ($user->isTeamLeader($team) && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => __('messages.team.last_leader')], 422);
        }

        $team->removeMember($user);

        return response()->json([
            'message' => __('messages.team.member_removed'),
            'team' => $team->load('users'),
        ]);
    }

    /**
     * Update member role (RESTful route with user ID in path).
     */
    public function updateMemberRoleById(Request $request, $id, $userId)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])],
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => __('messages.team.leader_required')], 403);
        }

        $user = User::findOrFail($userId);

        // Check if user is a team member
        if (! $team->hasMember($user)) {
            return response()->json(['error' => __('messages.team.not_member')], 422);
        }

        // Prevent demoting the last leader (owner/admin/manager)
        $isCurrentlyLeader = $user->isTeamLeader($team);
        $isNewRoleLeader = in_array($validated['role'], ['owner', 'admin', 'manager']);

        if ($isCurrentlyLeader && ! $isNewRoleLeader && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => __('messages.team.demote_last_leader')], 422);
        }

        $team->updateMemberRole($user, $validated['role']);

        return response()->json([
            'message' => __('messages.team.member_updated'),
            'team' => $team->load('users'),
        ]);
    }
}
