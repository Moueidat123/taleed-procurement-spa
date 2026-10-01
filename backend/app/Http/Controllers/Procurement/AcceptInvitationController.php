<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Resources\CurrentUserResource;
use App\Services\StaffInvitationService;
use Illuminate\Support\Facades\Auth;

/**
 * Accept a staff invitation (decisions.md D-10). Public + throttled. On success
 * the new staff user is created and signed in so they can proceed to the
 * mandatory TOTP enrolment.
 */
class AcceptInvitationController
{
    public function __invoke(AcceptInvitationRequest $request, StaffInvitationService $service): CurrentUserResource
    {
        $user = $service->accept(
            token: $request->string('token')->value(),
            profile: [
                'name' => $request->string('name')->trim()->value(),
                'password' => $request->string('password')->value(),
            ],
        );

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return new CurrentUserResource($user);
    }
}
