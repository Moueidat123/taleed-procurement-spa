<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\UpdateStaffAccessRequest;
use App\Http\Resources\StaffUserResource;
use App\Models\AppUser;
use App\Services\StaffAccessService;

/**
 * Super Admin changes a staff member's access (openapi-v1.yaml
 * PATCH /staff/users/{id}/access). The self and last-admin invariants are
 * enforced by StaffAccessService; the returned resource reflects the new state.
 */
class UpdateStaffAccessController
{
    public function __invoke(UpdateStaffAccessRequest $request, AppUser $user, StaffAccessService $service): StaffUserResource
    {
        /** @var AppUser $actor */
        $actor = $request->user();

        $changes = $request->only(array_keys($request->validated()));

        $updated = $service->update($actor, $user, $changes);

        return new StaffUserResource($updated);
    }
}
