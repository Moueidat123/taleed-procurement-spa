<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\UpdateOrganizationAccessRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\AuditEvent;
use App\Models\Organization;

/**
 * Super Admin pauses or re-enables an organization (openapi-v1.yaml
 * PATCH /staff/organizations/{id}/access). A paused organization is excluded
 * from starting or submitting assessments (enforced with the assessment flow).
 */
class UpdateOrganizationAccessController
{
    public function __invoke(UpdateOrganizationAccessRequest $request, Organization $organization): OrganizationResource
    {
        $active = $request->boolean('active');

        if ($organization->active !== $active) {
            $organization->active = $active;
            $organization->save();

            AuditEvent::record(
                action: $active ? 'organization.enabled' : 'organization.paused',
                actorId: $request->user()?->id,
                targetType: 'organization',
                targetId: $organization->id,
                organizationId: $organization->id,
            );
        }

        return new OrganizationResource($organization);
    }
}
