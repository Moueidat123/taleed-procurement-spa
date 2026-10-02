<?php

namespace App\Http\Controllers\Procurement;

use App\Models\AssessmentCycle;
use App\Models\FrameworkVersion;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;

/**
 * Phase 2B — open cycles and published framework content for signed-in users.
 * Draft framework versions are never exposed (404).
 */
class FrameworkContentController
{
    public function cycles(): JsonResponse
    {
        $cycles = AssessmentCycle::query()->with('frameworkVersion')->where('status', 'open')->orderBy('opens_at')->get()
            ->filter(fn (AssessmentCycle $c) => $c->isOpen() && $c->frameworkVersion?->isPublished())
            ->map(fn (AssessmentCycle $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'frameworkVersion' => $c->frameworkVersion->semantic_version,
                'opensAt' => $c->opens_at->toISOString(),
                'closesAt' => $c->closes_at->toISOString(),
                'timezone' => $c->business_timezone,
            ])->values();

        return new JsonResponse(['data' => $cycles]);
    }

    public function show(AssessmentService $service, string $version): JsonResponse
    {
        $framework = FrameworkVersion::query()->where('semantic_version', $version)->where('status', 'published')->firstOrFail();
        $content = $service->framework($framework->id);
        foreach ($content['domains'] as &$domain) {
            foreach ($domain['questions'] as &$question) {
                unset($question['dbId']);
            }
        }

        return new JsonResponse(['data' => $content]);
    }
}
