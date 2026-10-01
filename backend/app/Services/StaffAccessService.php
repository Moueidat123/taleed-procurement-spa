<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\AuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Super Admin changes to staff access (openapi-v1.yaml PATCH /staff/users/{id}/access).
 *
 * Two invariants are enforced atomically (decisions.md D-10):
 *  - A Super Admin can never change their own access (self-protection), so an
 *    account is never locked out or demoted by accident.
 *  - The last active Super Admin can never be deactivated, so the platform is
 *    never left with no one who can administer it.
 */
class StaffAccessService
{
    /**
     * Apply active / canExport changes to a staff user and audit the result.
     *
     * @param  array{active?: bool, canExport?: bool}  $changes
     */
    public function update(AppUser $actor, AppUser $target, array $changes): AppUser
    {
        if ($actor->id === $target->id) {
            throw ValidationException::withMessages([
                'id' => __('You cannot change your own access.'),
            ]);
        }

        if ($target->role === 'champion') {
            throw ValidationException::withMessages([
                'id' => __('Only staff access can be managed here.'),
            ]);
        }

        return DB::transaction(function () use ($actor, $target, $changes) {
            // Lock all active Super Admins plus the target in a stable order so
            // concurrent deactivations serialize and the last-admin check is safe.
            AppUser::query()
                ->where(fn ($q) => $q->where('role', 'admin')->where('active', true))
                ->orWhereKey($target->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            /** @var AppUser $target */
            $target = AppUser::query()->whereKey($target->id)->firstOrFail();

            $deactivating = array_key_exists('active', $changes) && $changes['active'] === false && $target->active;
            if ($deactivating && $target->role === 'admin' && $this->isLastActiveAdmin($target)) {
                throw ValidationException::withMessages([
                    'active' => __('The last active Super Admin cannot be deactivated.'),
                ])->status(409);
            }

            $metadata = [];

            if (array_key_exists('active', $changes)) {
                $target->active = $changes['active'];
                $metadata['active'] = $changes['active'];
            }

            // Export only applies to Analysts; Super Admins always can, Champions never do.
            if (array_key_exists('canExport', $changes) && $target->role === 'analyst') {
                $target->can_export = $changes['canExport'];
                $metadata['canExport'] = $changes['canExport'];
            }

            $target->save();

            AuditEvent::record(
                action: 'staff.access_changed',
                actorId: $actor->id,
                targetType: 'app_user',
                targetId: $target->id,
                metadata: $metadata,
            );

            return $target;
        });
    }

    /** True when this is the only remaining active Super Admin. */
    private function isLastActiveAdmin(AppUser $target): bool
    {
        return AppUser::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->whereKeyNot($target->id)
            ->doesntExist();
    }
}
