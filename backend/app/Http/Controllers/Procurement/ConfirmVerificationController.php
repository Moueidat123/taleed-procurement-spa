<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Resources\CurrentUserResource;
use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;

/**
 * Confirm the signed-in user's email with a six-digit code (decisions.md D-30).
 * Throttled by the 'verify' limiter. The service counts failed attempts and
 * enforces the 15-minute / 5-attempt window.
 */
class ConfirmVerificationController
{
    public function __invoke(Request $request, EmailVerificationService $verification): CurrentUserResource
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        /** @var AppUser $user */
        $user = $request->user();

        $verification->confirm($user, $validated['code']);

        AuditEvent::record(
            action: 'email.verified',
            actorId: $user->id,
            targetType: 'app_user',
            targetId: $user->id,
            ipAddress: $request->ip(),
        );

        return new CurrentUserResource($user->refresh());
    }
}
