<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AppUser;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Phase 2B — Super Admin opens a correction draft (behind the staff TOTP gate). */
class OpenCorrectionController
{
    public function __invoke(Request $request, AssessmentService $service, string $revisionId): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        /** @var AppUser $admin */
        $admin = $request->user();
        $draft = $service->openCorrection($admin, $revisionId, (string) $data['reason']);

        return new JsonResponse(['data' => $service->present($draft)], 201);
    }
}
