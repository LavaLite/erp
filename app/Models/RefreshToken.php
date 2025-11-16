<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'organization_id',
        'expires_at',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the refresh token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new refresh token.
     */
    public static function generate(
        User $user,
        ?string $organizationId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        // Revoke old refresh tokens for this user and tenant
        static::where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return static::create([
            'user_id' => $user->id,
            'token' => hash('sha256', Str::random(80)),
            'organization_id' => $organizationId,
            'expires_at' => now()->addDays(14), // 2 weeks
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Check if the token is valid (not expired and not revoked).
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && $this->revoked_at === null;
    }

    /**
     * Revoke the token.
     */
    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }
}
