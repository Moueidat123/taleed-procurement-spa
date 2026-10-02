<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AppUser;
use App\Models\AuditEvent;
use App\Services\StaffPortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Phase 2B — Analyst/Super Admin read endpoints (staff only).
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
            'stage' => ['nullable', 'in:no_profile,not_started,in_progress,submitted'],
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

    /** Export rows (effective submissions only). Super Admin or the canExport grant; audited. */
    public function export(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);
        /** @var AppUser $user */
        $user = $request->user();
        abort_unless($user->role === 'admin' || $user->can_export, 403);
        $data = $request->validate([
            'cycleId' => ['nullable', 'string', 'max:26'],
            'format' => ['required', 'in:csv,xlsx'],
            'band' => ['nullable', 'in:foundational,developing,advanced,best_in_class'],
            'search' => ['nullable', 'string', 'max:100'],
            'organizationId' => ['nullable', 'string', 'max:26'],
        ]);
        if (! empty($data['organizationId'])) {
            $rows = $this->service->organizationExportRows($data['organizationId']);
            AuditEvent::record(action: 'organization.exported', actorId: $user->id, targetType: 'organization', targetId: $data['organizationId'],
                organizationId: $data['organizationId'], metadata: ['format' => $data['format'], 'count' => count($rows)], ipAddress: $request->ip());

            return new JsonResponse(['data' => ['cycleId' => null, 'frameworkVersion' => null, 'rows' => $rows]]);
        }
        $cycle = $this->service->cycle($data['cycleId'] ?? null);
        $rows = $this->service->exportRows($cycle, $data);
        AuditEvent::record(action: 'portfolio.exported', actorId: $user->id, targetType: 'cycle', targetId: $cycle->id,
            metadata: ['format' => $data['format'], 'count' => count($rows)], ipAddress: $request->ip());

        return new JsonResponse(['data' => ['cycleId' => $cycle->id, 'frameworkVersion' => $cycle->frameworkVersion?->semantic_version, 'rows' => $rows]]);
    }

    private function authorizeStaff(Request $request): void
    {
        /** @var AppUser $user */
        $user = $request->user();
        $this->service->assertStaff($user);
    }
}
