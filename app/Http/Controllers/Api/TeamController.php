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
            return response()->json(['error' => 'Organization context required (X-Organization-ID header)'], 400);
        }

        $teams = Team::inOrganization($organizationId)
            ->with(['users', 'creator', 'parentTeam', 'subTeams'])
            ->withCount('users')
            ->get();

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
            return response()->json(['error' => 'Organization context required (X-Organization-ID header)'], 400);
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
            return response()->json(['error' => 'Organization context required (X-Organization-ID header)'], 400);
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
                'error' => 'A team with this slug already exists in your organization',
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
            'message' => 'Team created successfully',
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
            return response()->json(['error' => 'Unauthorized'], 403);
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
            return response()->json(['error' => 'Only team leaders can update the team'], 403);
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
                    'error' => 'A team with this slug already exists in your organization',
                ], 422);
            }
        }

        $team->update($validated);

        return response()->json([
            'message' => 'Team updated successfully',
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
            return response()->json(['error' => 'Only organization admins can delete teams'], 403);
        }

        // Check if team has sub-teams
        if ($team->subTeams()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete team with sub-teams. Delete or reassign sub-teams first.',
            ], 422);
        }

        $team->delete();

        return response()->json(['message' => 'Team deleted successfully']);
    }

    /**
     * Add member to team.
     */
    public function addMember(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['owner', 'admin', 'manager', 'member', 'viewer', 'billing'])],
        ]);

        // Check if requester is team leader or org admin
        $requester = Auth::user();
        if (! $requester->isTeamLeader($team) && ! $requester->hasRoleInOrganization('admin', $team->organization_id)) {
            return response()->json(['error' => 'Only team leaders can add members'], 403);
        }

        $user = User::findOrFail($validated['user_id']);

        // Check if user belongs to the organization
        if (! $user->belongsToOrganization($team->organization_id)) {
            return response()->json(['error' => 'User is not a member of this organization'], 422);
        }

        // Check if already a member
        if ($team->hasMember($user)) {
            return response()->json(['error' => 'User is already a member of this team'], 422);
        }

        $team->addMember($user, $validated['role'], $requester);

        return response()->json([
            'message' => 'Member added successfully',
            'team' => $team->load('users'),
        ]);
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
            return response()->json(['error' => 'Only team leaders can remove members'], 403);
        }

        $user = User::findOrFail($validated['user_id']);

        // Prevent removing the last leader (owner/admin/manager)
        if ($user->isTeamLeader($team) && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => 'Cannot remove the last team leader'], 422);
        }

        $team->removeMember($user);

        return response()->json([
            'message' => 'Member removed successfully',
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
            return response()->json(['error' => 'Only team leaders can update member roles'], 403);
        }

        $user = User::findOrFail($validated['user_id']);

        // Check if user is a team member
        if (! $team->hasMember($user)) {
            return response()->json(['error' => 'User is not a member of this team'], 422);
        }

        // Prevent demoting the last leader (owner/admin/manager)
        $isCurrentlyLeader = $user->isTeamLeader($team);
        $isNewRoleLeader = in_array($validated['role'], ['owner', 'admin', 'manager']);

        if ($isCurrentlyLeader && ! $isNewRoleLeader && $team->leadershipTeam()->count() <= 1) {
            return response()->json(['error' => 'Cannot demote the last team leader'], 422);
        }

        $team->updateMemberRole($user, $validated['role']);

        return response()->json([
            'message' => 'Member role updated successfully',
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
            return response()->json(['error' => 'Only organization admins can assign modules'], 403);
        }

        // Sync modules
        $team->modules()->sync($validated['module_ids']);

        return response()->json([
            'message' => 'Modules assigned successfully',
            'team' => $team->load('modules'),
        ]);
    }
}
