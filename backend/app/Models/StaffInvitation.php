<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A single-use invitation for a new staff member (Analyst or Super Admin).
 * 72-hour lifetime (decisions.md D-10). Only the SHA-256 token hash is stored;
 * the plaintext token is emailed once and never persisted. Privileged fields
 * are set explicitly by admin-authorized code, never mass assigned.
 *
 * @property string $id
 * @property string $email
 * @property string $normalized_email
 * @property string $role
 * @property bool $can_export
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property string|null $accepted_user_id
 * @property Carbon|null $revoked_at
 */
class StaffInvitation extends Model
{
    use HasUlids;

    /** @var list<string> */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'can_export' => 'boolean',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<AppUser, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'inviter_id');
    }

    /** SHA-256 hash of a plaintext token, matching how invitations are stored. */
    public static function hashToken(string $plaintext): string
    {
        return hash('sha256', $plaintext);
    }

    public function isPending(Carbon $now): bool
    {
        return $this->accepted_at === null
            && $this->revoked_at === null
            && $this->expires_at->greaterThan($now);
    }
}
