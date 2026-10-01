<?php

namespace App\Models;

use Database\Factories\AppUserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * A procurement application user (Champion, Analyst or Super Admin).
 *
 * Authenticated only through the `web` guard. Never a Statamic user and never
 * granted control-panel access (decisions.md D-03).
 *
 * @property string $id
 * @property string $role
 */
class AppUser extends Authenticatable
{
    /** @use HasFactory<AppUserFactory> */
    use HasFactory, HasUlids, Notifiable, TwoFactorAuthenticatable;

    public const ROLES = ['champion', 'analyst', 'admin'];

    protected $table = 'app_users';

    /**
     * Only profile fields are mass assignable. Role, organization, export
     * permission, active and verification state are set explicitly by
     * authorized server code (never from request input).
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'job_title'];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'can_export' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function isStaff(): bool
    {
        return $this->role !== 'champion';
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /** @return HasMany<EmailVerificationChallenge, $this> */
    public function emailVerificationChallenges(): HasMany
    {
        return $this->hasMany(EmailVerificationChallenge::class, 'app_user_id');
    }
}
