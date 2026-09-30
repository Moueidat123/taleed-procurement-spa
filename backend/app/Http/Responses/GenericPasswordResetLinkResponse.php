<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

/**
 * Identical acknowledgement whether or not the email belongs to an active
 * application user, and whether or not the request was throttled, so the
 * endpoint cannot be used to discover accounts (05-ACCEPTANCE.md).
 */
class GenericPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse, SuccessfulPasswordResetLinkRequestResponse
{
    public function __construct(protected string $status = '') {}

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'data' => ['message' => 'If an account exists for this email, a reset link has been sent.'],
        ], 202);
    }
}
