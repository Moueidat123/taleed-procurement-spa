<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A six-digit email verification challenge (decisions.md D-30): valid 15
 * minutes, 5 attempts, resend throttled. Only the SHA-256 of the code is
 * stored; the code itself is emailed and never persisted. Issuing a new
 * challenge invalidates earlier unconsumed ones for the same user.
 *
 * @property string $id
 * @property int $attempts
 * @property \Illuminate\Support\Carbon $expires_at
 */
class EmailVerificationChallenge extends Model
{
    use HasUlids;

    public const MAX_ATTEMPTS = 5;

    /** @var array<string, mixed> */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'sent_count' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<AppUser, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    /** SHA-256 of a six-digit code, matching how challenges are stored. */
    public static function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }

    public function isUsable(Carbon $now): bool
    {
        return $this->consumed_at === null
            && $this->attempts < self::MAX_ATTEMPTS
            && $this->expires_at->greaterThan($now);
    }
}
