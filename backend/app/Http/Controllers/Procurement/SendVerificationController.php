<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Resend a six-digit verification code to the signed-in, still-unverified user.
 * Throttled by the 'verify' limiter. Always returns a generic 202 so timing
 * and messaging do not reveal verification state beyond what the user owns.
 */
class SendVerificationController
{
    public function __invoke(Request $request, EmailVerificationService $verification): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();

        if ($user->email_verified_at === null) {
            $verification->issue($user);
            AuditEvent::record(
                action: 'email.verification_sent',
                actorId: $user->id,
                targetType: 'app_user',
                targetId: $user->id,
                ipAddress: $request->ip(),
            );
        }

        return new JsonResponse(['status' => 'sent'], 202);
    }
}
