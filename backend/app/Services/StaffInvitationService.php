<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Models\StaffInvitation;
use App\Notifications\StaffInvitationNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Staff invitations (decisions.md D-10): 72-hour, single-use, hashed token.
 * Only a Super Admin issues them; only the SHA-256 token hash is stored and the
 * plaintext is emailed once. Accepting creates (or re-activates) a staff user.
 */
class StaffInvitationService
{
    public const TTL_HOURS = 72;

    /**
     * Issue an invitation for an Analyst or Super Admin and email the token.
     * A pending invitation or an existing active user for the same email is
     * refused so one address never has two live paths in.
     *
     * @param  'analyst'|'admin'  $role
     */
    public function issue(AppUser $inviter, string $email, string $role, bool $canExport, ?Carbon $now = null): StaffInvitation
    {
        $now ??= Carbon::now();
        $normalized = Str::lower(trim($email));

        return DB::transaction(function () use ($inviter, $email, $normalized, $role, $canExport, $now) {
            if (AppUser::query()->where('email', $normalized)->where('active', true)->exists()) {
                throw ValidationException::withMessages([
                    'email' => __('An active account already exists for this email.'),
                ]);
            }

            $hasPending = StaffInvitation::query()
                ->where('normalized_email', $normalized)
                ->whereNull('accepted_at')->whereNull('revoked_at')
                ->where('expires_at', '>', $now)->exists();
            if ($hasPending) {
                throw ValidationException::withMessages([
                    'email' => __('A pending invitation already exists for this email.'),
                ]);
            }

            $token = Str::random(64);

            $invitation = StaffInvitation::query()->create([
                'email' => $email,
                'normalized_email' => $normalized,
                'role' => $role,
                // Export is only ever meaningful for Analysts; Super Admins always can.
                'can_export' => $role === 'analyst' ? $canExport : false,
                'token_hash' => StaffInvitation::hashToken($token),
                'inviter_id' => $inviter->id,
                'expires_at' => $now->copy()->addHours(self::TTL_HOURS),
            ]);

            Notification::route('mail', $email)
                ->notify(new StaffInvitationNotification($token, $role));

            AuditEvent::record(
                action: 'staff.invited',
                actorId: $inviter->id,
                targetType: 'staff_invitation',
                targetId: $invitation->id,
                metadata: ['role' => $role],
            );

            return $invitation;
        });
    }

    /**
     * Accept an invitation by plaintext token, creating the staff user. The
     * token lookup is by hash; a single-use, unexpired, unrevoked invitation is
     * required. The invitation and new user are written atomically.
     *
     * @param  array{name:string,password:string}  $profile
     */
    public function accept(string $token, array $profile, ?Carbon $now = null): AppUser
    {
        $now ??= Carbon::now();
        $hash = StaffInvitation::hashToken($token);

        return DB::transaction(function () use ($hash, $profile, $now) {
            /** @var StaffInvitation|null $invitation */
            $invitation = StaffInvitation::query()
                ->where('token_hash', $hash)
                ->lockForUpdate()->first();

            if ($invitation === null || ! $invitation->isPending($now)) {
                throw ValidationException::withMessages([
                    'token' => __('This invitation is invalid or has expired.'),
                ]);
            }

            if (AppUser::query()->where('email', $invitation->normalized_email)->where('active', true)->exists()) {
                throw ValidationException::withMessages([
                    'token' => __('This invitation is invalid or has expired.'),
                ]);
            }

            $user = new AppUser;
            $user->fill([
                'name' => $profile['name'],
                'email' => $invitation->normalized_email,
                'job_title' => $invitation->role === 'admin' ? 'Super Admin' : 'Analyst',
            ]);
            $user->password = $profile['password'];
            $user->role = $invitation->role;
            $user->can_export = $invitation->can_export;
            $user->active = true;
            // Accepting the emailed invitation proves control of the inbox.
            $user->email_verified_at = $now;
            $user->save();

            $invitation->forceFill([
                'accepted_at' => $now,
                'accepted_user_id' => $user->id,
            ])->save();

            AuditEvent::record(
                action: 'staff.invitation_accepted',
                actorId: $user->id,
                targetType: 'staff_invitation',
                targetId: $invitation->id,
                metadata: ['role' => $invitation->role],
            );

            return $user;
        });
    }
}
