<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AppUser;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase 2B — Champion assessment endpoints (openapi-v1.yaml /assessments*).
 * Server-side ownership on every call; forged IDs are 404.
 */
class AssessmentController
{
    public function __construct(private AssessmentService $service) {}

    public function start(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $revision = $this->service->start($user);

        return new JsonResponse(['data' => $this->service->present($revision)],
            $revision->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, string $revisionId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->service->assertEligible($user);

        return new JsonResponse(['data' => $this->service->present($this->service->ownedRevision($user, $revisionId))]);
    }

    public function saveAnswers(Request $request, string $revisionId): JsonResponse
    {
        $data = $request->validate([
            'expectedVersion' => ['required', 'integer', 'min:0'],
            'answers' => ['required', 'array', 'max:40'],
        ]);
        /** @var AppUser $user */
        $user = $request->user();
        $revision = $this->service->saveAnswers($user, $revisionId, (int) $data['expectedVersion'], $data['answers']);

        return new JsonResponse(['data' => $this->service->present($revision)]);
    }

    public function submit(Request $request, string $revisionId): JsonResponse
    {
        $data = $request->validate([
            'expectedVersion' => ['required', 'integer', 'min:0'],
            'declaration' => ['required', 'accepted'],
        ]);
        $key = (string) $request->header('Idempotency-Key', '');
        if ($key === '' || strlen($key) > 120) {
            return new JsonResponse(['error' => ['code' => 'validation_failed', 'message' => 'Idempotency-Key header is required.'],
                'requestId' => $request->attributes->get('request_id')], 422);
        }
        /** @var AppUser $user */
        $user = $request->user();
        $revision = $this->service->submit($user, $revisionId, (int) $data['expectedVersion'], true, $key);

        return new JsonResponse(['data' => $this->service->present($revision)]);
    }

    public function result(Request $request, string $revisionId): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->service->assertEligible($user);
        $revision = $this->service->ownedRevision($user, $revisionId);
        $snapshot = $revision->snapshot;
        abort_if($snapshot === null, 404);

        return new JsonResponse(['data' => [
            'revisionId' => $revision->id,
            'checksum' => $snapshot->canonical_sha256,
            'snapshot' => $snapshot->snapshot,
        ]]);
    }

    public function history(Request $request): JsonResponse
    {
        /** @var AppUser $user */
        $user = $request->user();
        // Read-only: a Champion without a company yet simply has no history.
        abort_unless($user->role === 'champion' && $user->active, 403);

        return new JsonResponse(['data' => $user->organization_id === null ? [] : $this->service->history($user)]);
    }
}
