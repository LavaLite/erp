<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasMultiOrganizationRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasMultiOrganizationRolesAndPermissions, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
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
        'is_active',
        'is_super_admin',
        'last_login_at',
        'last_login_ip',
        'email_verification_token',
        'email_verification_sent_at',
        'two_factor_enabled',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_sent_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'preferences' => 'array',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name, 0, 1));
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Check if the user has the global admin role.
     */
    public function isGlobalAdmin(): bool
    {
        // Check if user has the 'admin' role with organization_id = 'global'
        return $this->roles()
            ->where('roles.organization_id', 'global')
            ->where('slug', 'admin')
            ->exists();
    }

    /**
     * Check if the user can manage global roles and permissions.
     * Super admins and users with global admin role can manage global roles/permissions.
     */
    public function canManageGlobalRoles(): bool
    {
        // Super admins can manage global roles
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->isGlobalAdmin();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Teams where user is a leader.
     */
    public function leadingTeams(): BelongsToMany
    {
        return $this->teams()->whereIn('team_user.role', ['owner', 'admin', 'manager']);
    }

    /**
     * Teams where user is owner.
     */
    public function ownedTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('role', 'owner');
    }

    /**
     * Teams where user is admin.
     */
    public function adminTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('role', 'admin');
    }

    /**
     * Teams where user is manager.
     */
    public function managedTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('role', 'manager');
    }

    /**
     * Get teams in a specific organization.
     */
    public function teamsInOrganization($organizationId): BelongsToMany
    {
        return $this->teams()->where('teams.organization_id', $organizationId);
    }

    /**
     * Check if user belongs to a team.
     */
    public function belongsToTeam(Team|int $team): bool
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        return $this->teams()->where('team_id', $teamId)->exists();
    }

    /**
     * Check if user is a leader of a team.
     */
    public function isTeamLeader(Team|int $team): bool
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        return $this->teams()
            ->where('team_id', $teamId)
            ->whereIn('team_user.role', ['owner', 'admin', 'manager'])
            ->exists();
    }

    /**
     * Check if user is team owner.
     */
    public function isTeamOwner(Team|int $team): bool
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        return $this->teams()
            ->where('team_id', $teamId)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Check if user can manage team (owner/admin/manager).
     */
    public function canManageTeam(Team|int $team): bool
    {
        return $this->isTeamLeader($team);
    }

    /**
     * Get user's role in a team.
     */
    public function getTeamRole(Team|int $team): ?string
    {
        $teamId = $team instanceof Team ? $team->id : $team;
        $membership = $this->teams()
            ->where('team_id', $teamId)
            ->first();

        return $membership?->pivot->role;
    }

    // ===================================
    // Module Access Methods (Hybrid)
    // ===================================

    /**
     * Get all accessible module slugs for user in an organization.
     * Hybrid approach: Team-based + Role-based fallback.
     *
     * @param  int|string  $organizationId
     * @return array Array of module slugs
     */
    public function getAccessibleModules($organizationId): array
    {
        // 1. Get organization's enabled modules
        $orgModules = Organization::find($organizationId)
            ?->enabledModules()
            ->pluck('slug')
            ->toArray() ?? [];

        if (empty($orgModules)) {
            return [];
        }

        // 2. Super admins and organization owners/admins get all org modules
        if ($this->is_super_admin || $this->hasRoleInOrganization(['owner', 'admin'], $organizationId)) {
            return $orgModules;
        }

        // 3. Get modules from teams (primary source)
        $teamModules = $this->getModulesFromTeams($organizationId);

        // 4. Get modules from roles (fallback)
        $roleModules = $this->getModulesFromRoles($organizationId);

        // 5. Merge team and role modules
        $userModules = array_unique(array_merge($teamModules, $roleModules));

        // 6. If user has no specific restrictions, give all org modules
        if (empty($userModules)) {
            return $orgModules;
        }

        // 7. Return intersection (only modules that org has AND user can access)
        return array_values(array_intersect($userModules, $orgModules));
    }

    /**
     * Get modules from user's teams in organization.
     *
     * @param  int|string  $organizationId
     * @return array Array of module slugs
     */
    public function getModulesFromTeams($organizationId): array
    {
        $teams = $this->teamsInOrganization($organizationId)->get();

        if ($teams->isEmpty()) {
            return [];
        }

        $moduleIds = [];
        foreach ($teams as $team) {
            // If team has no module restrictions, user gets all org modules
            if ($team->modules()->count() === 0) {
                return Organization::find($organizationId)
                    ?->enabledModules()
                    ->pluck('slug')
                    ->toArray() ?? [];
            }

            // Add team's assigned modules
            $teamModuleIds = $team->modules()->pluck('modules.id')->toArray();
            $moduleIds = array_merge($moduleIds, $teamModuleIds);
        }

        if (empty($moduleIds)) {
            return [];
        }

        // Convert IDs to slugs
        return Module::whereIn('id', array_unique($moduleIds))
            ->pluck('slug')
            ->toArray();
    }

    /**
     * Get modules from user's roles in organization.
     *
     * @param  int|string  $organizationId
     * @return array Array of module slugs
     */
    public function getModulesFromRoles($organizationId): array
    {
        $roles = $this->rolesInOrganization($organizationId)->get();

        if ($roles->isEmpty()) {
            return [];
        }

        $moduleIds = [];
        foreach ($roles as $role) {
            // Get modules assigned to this role in this organization
            $roleModuleIds = DB::table('role_module')
                ->where('role_id', $role->id)
                ->where('organization_id', $organizationId)
                ->where('has_access', true)
                ->pluck('module_id')
                ->toArray();

            $moduleIds = array_merge($moduleIds, $roleModuleIds);
        }

        if (empty($moduleIds)) {
            return [];
        }

        // Convert IDs to slugs
        return Module::whereIn('id', array_unique($moduleIds))
            ->pluck('slug')
            ->toArray();
    }

    /**
     * Check if user can access a specific module in organization.
     *
     * @param  int|string  $organizationId
     */
    public function canAccessModule(string $moduleSlug, $organizationId): bool
    {
        $accessibleModules = $this->getAccessibleModules($organizationId);

        return in_array($moduleSlug, $accessibleModules);
    }

    /**
     * Check if user has role in organization.
     *
     * @param  string|array  $roleNames
     * @param  int|string  $organizationId
     */
    public function hasRoleInOrganization($roleNames, $organizationId): bool
    {
        $roleNames = is_array($roleNames) ? $roleNames : [$roleNames];

        return $this->rolesInOrganization($organizationId)
            ->whereIn('name', $roleNames)
            ->exists();
    }

    /**
     * Generate email verification token.
     */
    public function generateEmailVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->update([
            'email_verification_token' => hash('sha256', $token),
            'email_verification_sent_at' => now(),
        ]);

        return $token;
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->email_verified_at) {
            return false; // Already verified
        }

        $hashedToken = hash('sha256', $token);

        if ($this->email_verification_token !== $hashedToken) {
            return false; // Invalid token
        }

        // Check if token expired (24 hours)
        if ($this->email_verification_sent_at->addHours(24)->isPast()) {
            return false; // Expired
        }

        $this->email_verified_at = now();
        $this->email_verification_token = null;
        $this->email_verification_sent_at = null;
        $this->save();

        return true;
    }

    /**
     * Check if email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(string $secret, array $recoveryCodes): void
    {
        $this->two_factor_enabled = true;
        $this->two_factor_secret = encrypt($secret);
        $this->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $this->two_factor_confirmed_at = now();
        $this->save();
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(): void
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->two_factor_confirmed_at = null;
        $this->save();
    }

    /**
     * Get two-factor secret.
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    /**
     * Get two-factor recovery codes.
     */
    public function getTwoFactorRecoveryCodes(): array
    {
        if (! $this->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }

    /**
     * Use a recovery code.
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getTwoFactorRecoveryCodes();

        if (! in_array($code, $codes)) {
            return false;
        }

        $codes = array_values(array_diff($codes, [$code]));

        $this->two_factor_recovery_codes = encrypt(json_encode($codes));
        $this->save();

        return true;
    }
}
