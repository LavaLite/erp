<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_team_id',
        'name',
        'slug',
        'description',
        'avatar',
        'color',
        'created_by',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'joined_at' => 'datetime',
    ];

    /**
     * The organization that owns the team.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The parent team (for hierarchical teams).
     */
    public function parentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    /**
     * Child teams (sub-teams).
     */
    public function subTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    /**
     * All child teams recursively.
     */
    public function allSubTeams()
    {
        return $this->subTeams()->with('allSubTeams');
    }

    /**
     * The user who created the team.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Team leaders only.
     */
    public function leaders(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'leader');
    }

    /**
     * Team owners (Super Admin level).
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Team admins (High-level access).
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Team managers (Partial admin access).
     */
    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    /**
     * Team members (excluding leaders).
     */
    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    /**
     * Team viewers (read-only access).
     */
    public function viewers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'viewer');
    }

    /**
     * Billing/Finance role members.
     */
    public function billingMembers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'billing');
    }

    /**
     * Get all leadership roles (owner, admin, manager).
     */
    public function leadershipTeam(): BelongsToMany
    {
        return $this->users()->whereIn('team_user.role', ['owner', 'admin', 'manager']);
    }

    /**
     * Modules accessible by this team.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Active modules only.
     */
    public function activeModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_active', true);
    }

    /**
     * Check if user is a member of this team.
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is a leader of this team.
     */
    public function hasLeader(User $user): bool
    {
        return $this->leadershipTeam()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is owner.
     */
    public function hasOwner(User $user): bool
    {
        return $this->owners()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is admin.
     */
    public function hasAdmin(User $user): bool
    {
        return $this->admins()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user can manage team (owner, admin, or manager).
     */
    public function canManage(User $user): bool
    {
        return $this->leadershipTeam()->where('user_id', $user->id)->exists();
    }

    /**
     * Add user to team with role.
     */
    public function addMember(User $user, string $role = 'member', ?User $invitedBy = null): void
    {
        $this->users()->syncWithoutDetaching([
            $user->id => [
                'role' => $role,
                'invited_by' => $invitedBy?->id,
                'joined_at' => now(),
            ],
        ]);
    }

    /**
     * Remove user from team.
     */
    public function removeMember(User $user): void
    {
        $this->users()->detach($user->id);
    }

    /**
     * Update member role.
     */
    public function updateMemberRole(User $user, string $role): void
    {
        $this->users()->updateExistingPivot($user->id, ['role' => $role]);
    }

    /**
     * Get team member count.
     */
    public function getMemberCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Scope to active teams only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to teams in a specific organization.
     */
    public function scopeInOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to root teams (no parent).
     */
    public function scopeRootTeams($query)
    {
        return $query->whereNull('parent_team_id');
    }
}
