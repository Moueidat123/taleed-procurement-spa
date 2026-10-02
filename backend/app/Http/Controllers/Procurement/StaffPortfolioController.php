<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AppUser;
use App\Services\StaffPortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase 2B — Analyst/Super Admin read endpoints (behind the staff TOTP gate).
 * Server-side filtering and pagination; never returns draft answers.
 */
class StaffPortfolioController
{
    public function __construct(private StaffPortfolioService $service) {}

    public function portfolio(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate(['cycleId' => ['nullable', 'string', 'max:26']]);

        return new JsonResponse(['data' => $this->service->portfolio($this->service->cycle($data['cycleId'] ?? null))]);
    }

    public function organizations(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate([
            'cycleId' => ['nullable', 'string', 'max:26'],
            'search' => ['nullable', 'string', 'max:100'],
            'stage' => ['nullable', 'in:not_started,in_progress,submitted'],
            'band' => ['nullable', 'in:foundational,developing,advanced,best_in_class'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return new JsonResponse($this->service->organizations($this->service->cycle($data['cycleId'] ?? null), $data, (int) ($data['perPage'] ?? 25)));
    }

    public function organization(Request $request, string $organizationId): JsonResponse
    {
        $this->authorizeStaff($request);

        return new JsonResponse(['data' => $this->service->organization($organizationId)]);
    }

    public function submission(Request $request, string $revisionId): JsonResponse
    {
        $this->authorizeStaff($request);

        return new JsonResponse(['data' => $this->service->submission($revisionId)]);
    }

    public function compare(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate([
            'organizationIds' => ['required', 'array', 'min:2', 'max:4'],
            'organizationIds.*' => ['string', 'max:26'],
            'cycleId' => ['nullable', 'string', 'max:26'],
        ]);

        return new JsonResponse(['data' => $this->service->compare($data['organizationIds'], $this->service->cycle($data['cycleId'] ?? null))]);
    }

    private function authorizeStaff(Request $request): void
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->service->assertStaff($user);
    }
}
