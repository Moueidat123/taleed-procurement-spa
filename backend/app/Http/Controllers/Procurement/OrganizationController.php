<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\OrganizationProfileRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in Champion's company profile (decisions.md D-31).
 *
 *  - GET returns the Champion's organization, or 404 if none exists yet.
 *  - PATCH creates it on first submit, or updates it in place, with duplicate
 *    company blocking handled by OrganizationService.
 */
class OrganizationController
{
    public function show(Request $request): OrganizationResource|JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $org = $user->organization;

        if ($org === null) {
            return new JsonResponse([
                'error' => ['code' => 'not_found', 'message' => 'No company profile yet.'],
                'requestId' => $request->attributes->get('request_id'),
            ], 404);
        }

        return new OrganizationResource($org);
    }

    public function update(OrganizationProfileRequest $request, OrganizationService $service): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        $isNew = $user->organization_id === null;

        $org = $service->upsertForChampion($user, [
            'displayName' => $request->string('displayName')->trim()->value(),
            'countryCode' => $request->string('countryCode')->value(),
            'sizeBand' => $request->string('sizeBand')->value(),
            'registrationId' => $request->filled('registrationId')
                ? $request->string('registrationId')->trim()->value()
                : null,
        ]);

        AuditEvent::record(
            action: $isNew ? 'organization.created' : 'organization.updated',
            actorId: $user->id,
            targetType: 'organization',
            targetId: $org->id,
            organizationId: $org->id,
            ipAddress: $request->ip(),
        );

        return (new OrganizationResource($org))
            ->response()
            ->setStatusCode($isNew ? 201 : 200);
    }
}
