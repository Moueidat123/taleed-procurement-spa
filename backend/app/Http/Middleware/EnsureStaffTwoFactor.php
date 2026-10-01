<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Privileged MFA gate (decisions.md D-17). Staff (Analysts and Super Admins)
 * must have confirmed TOTP two-factor authentication before they may perform
 * privileged staff actions. Champions are never subject to this gate.
 *
 * A staff user without confirmed 2FA receives a 403 with a stable
 * `two_factor_required` code so the SPA can route them to enrolment.
 */
class EnsureStaffTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof AppUser && $user->isStaff() && $user->two_factor_confirmed_at === null) {
            return new JsonResponse([
                'error' => [
                    'code' => 'two_factor_required',
                    'message' => 'Two-factor authentication must be enabled for staff accounts.',
                ],
                'requestId' => $request->attributes->get('request_id'),
            ], 403);
        }

        return $next($request);
    }
}
