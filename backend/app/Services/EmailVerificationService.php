<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\EmailVerificationChallenge;
use App\Notifications\VerifyEmailCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Six-digit email verification (decisions.md D-30): code valid 15 minutes,
 * 5 attempts, resend throttled to once per 60 seconds. Only the SHA-256 of the
 * code is stored; the plaintext is emailed once. Issuing a new challenge
 * invalidates earlier unconsumed ones for the user.
 */
class EmailVerificationService
{
    public const CODE_TTL_MINUTES = 15;

    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Issue a fresh challenge and email the code. Prior unconsumed challenges
     * for the user are invalidated first so only one code is ever live.
     */
    public function issue(AppUser $user, ?Carbon $now = null): EmailVerificationChallenge
    {
        $now ??= Carbon::now();

        // Enforce resend cooldown against the latest live challenge.
        $latest = $user->emailVerificationChallenges()
            ->whereNull('consumed_at')->latest('last_sent_at')->first();
        $lastSentAt = $latest?->last_sent_at;
        if ($lastSentAt !== null
            && $lastSentAt->diffInSeconds($now, true) < self::RESEND_COOLDOWN_SECONDS) {
            throw ValidationException::withMessages([
                'email' => __('Please wait before requesting another code.'),
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $challenge = DB::transaction(function () use ($user, $code, $now) {
            // Invalidate prior unconsumed challenges.
            $user->emailVerificationChallenges()->whereNull('consumed_at')
                ->update(['consumed_at' => $now]);

            return $user->emailVerificationChallenges()->create([
                'challenge_hash' => EmailVerificationChallenge::hashCode($code),
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes(self::CODE_TTL_MINUTES),
                'last_sent_at' => $now,
                'sent_count' => 1,
            ]);
        });

        $user->notify(new VerifyEmailCode($code));

        return $challenge;
    }

    /**
     * Confirm a submitted code. Counts a failed attempt against the live
     * challenge; marks the user verified on success. Returns true on success.
     */
    public function confirm(AppUser $user, string $code, ?Carbon $now = null): bool
    {
        $now ??= Carbon::now();

        /** @var EmailVerificationChallenge|null $challenge */
        $challenge = $user->emailVerificationChallenges()
            ->whereNull('consumed_at')
            ->latest('created_at')->first();

        if (! $challenge || ! $challenge->isUsable($now)) {
            throw ValidationException::withMessages([
                'code' => __('This code has expired. Request a new one.'),
            ]);
        }

        if (! hash_equals($challenge->challenge_hash, EmailVerificationChallenge::hashCode($code))) {
            // Persist the failed attempt immediately — it must survive the
            // thrown validation error (a transaction here would roll it back).
            $challenge->increment('attempts');

            throw ValidationException::withMessages([
                'code' => __('That code is incorrect.'),
            ]);
        }

        // Correct code: consume the challenge and mark the user verified
        // atomically under a row lock.
        return DB::transaction(function () use ($user, $challenge, $now): bool {
            $locked = EmailVerificationChallenge::query()
                ->whereKey($challenge->getKey())
                ->lockForUpdate()->first();

            if (! $locked || $locked->consumed_at !== null) {
                throw ValidationException::withMessages([
                    'code' => __('This code has expired. Request a new one.'),
                ]);
            }

            $locked->update(['consumed_at' => $now]);
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => $now])->save();
            }

            return true;
        });
    }
}
