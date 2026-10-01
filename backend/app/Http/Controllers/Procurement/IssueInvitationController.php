<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\IssueInvitationRequest;
use App\Models\AppUser;
use App\Services\StaffInvitationService;
use Illuminate\Http\JsonResponse;

/**
 * Super Admin issues a staff invitation (decisions.md D-10). The response is
 * deliberately minimal — the token is emailed, never returned in the API.
 */
class IssueInvitationController
{
    public function __invoke(IssueInvitationRequest $request, StaffInvitationService $service): JsonResponse
    {
        /** @var AppUser $inviter */
        $inviter = $request->user();

        $invitation = $service->issue(
            inviter: $inviter,
            email: $request->string('email')->trim()->value(),
            role: $request->string('role')->value(),
            canExport: $request->boolean('canExport'),
        );

        return new JsonResponse([
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expiresAt' => $invitation->expires_at->toISOString(),
            ],
        ], 201);
    }
}
