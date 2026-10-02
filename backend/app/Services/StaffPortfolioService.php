<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCycle;
use App\Models\AssessmentRevision;
use App\Models\Organization;
use App\Models\SubmissionDomainResult;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 2B — staff read models (03-DATABASE-SCHEMA.md "Reporting"). Portfolio,
 * directory and comparison share one eligibility rule: the effective (latest
 * submitted) revision per non-test company in the chosen cycle. Drafts expose
 * only stage and answered count, never answers.
 */
class StaffPortfolioService
{
    private const BANDS = ['foundational', 'developing', 'advanced', 'best_in_class'];

    public function assertStaff(AppUser $user): void
    {
        if (! $user->isStaff() || ! $user->active) {
            throw new AuthorizationException;
        }
    }

    /** The requested cycle, or the most recent one. */
    public function cycle(?string $cycleId): AssessmentCycle
    {
        $query = AssessmentCycle::query()->with('frameworkVersion');

        return $cycleId !== null && $cycleId !== ''
            ? $query->whereKey($cycleId)->firstOrFail()
            : $query->orderByDesc('opens_at')->firstOrFail();
    }

    /** @return array<string, mixed> */
    public function portfolio(AssessmentCycle $cycle): array
    {
        $rows = $this->effective($cycle)->get([
            'assessments.current_submission_id as revision_id',
            'submission_snapshots.overall_percent',
            'submission_snapshots.band',
        ]);

        $bands = array_fill_keys(self::BANDS, 0);
        foreach ($rows as $row) {
            $bands[$row->band]++;
        }

        $domains = [];
        foreach ($this->domainResults($rows->pluck('revision_id')->all()) as $byKey) {
            foreach ($byKey as $key => $d) {
                $domains[$key] ??= ['key' => $key, 'name' => $d['name'], 'total' => 0, 'count' => 0, 'bands' => array_fill_keys(self::BANDS, 0)];
                $domains[$key]['total'] += $d['score'];
                $domains[$key]['count']++;
                $domains[$key]['bands'][$d['band']]++;
            }
        }

        $inProgress = Assessment::query()
            ->join('organizations', 'organizations.id', '=', 'assessments.organization_id')
            ->where('assessments.cycle_id', $cycle->id)
            ->where('organizations.is_test', false)
            ->whereNull('assessments.current_submission_id')
            ->count();

        $count = $rows->count();

        return [
            'cycle' => $this->presentCycle($cycle),
            'submittedCount' => $count,
            'inProgressCount' => $inProgress,
            'averageOverall' => $count > 0 ? round((float) $rows->avg(fn ($r) => (float) $r->overall_percent), 2) : null,
            'bands' => $bands,
            'domains' => array_values(array_map(fn (array $d) => [
                'key' => $d['key'],
                'name' => $d['name'],
                'average' => round($d['total'] / $d['count'], 2),
                'bands' => $d['bands'],
            ], $domains)),
        ];
    }

    /**
     * Champion and company directory: every registered Champion appears from
     * sign-up. A Champion without a company is stage no_profile; company rows
     * carry stage and answered count only, never answers.
     *
     * @param  array<string, mixed>  $filters
     * @return array{data: list<array<string, mixed>>, meta: array{total: int, page: int, perPage: int, lastPage: int}}
     */
    public function organizations(AssessmentCycle $cycle, array $filters, int $perPage): array
    {
        $companies = DB::table('organizations')
            ->where('organizations.is_test', false)
            ->leftJoin('assessments', fn ($join) => $join->on('assessments.organization_id', '=', 'organizations.id')
                ->where('assessments.cycle_id', '=', $cycle->id))
            ->leftJoin('submission_snapshots', 'submission_snapshots.revision_id', '=', 'assessments.current_submission_id')
            ->leftJoin('app_users as champion', fn ($join) => $join->on('champion.organization_id', '=', 'organizations.id')
                ->where('champion.role', '=', 'champion'))
            ->select([
                DB::raw("'company' as row_type"),
                'organizations.id as org_id', 'organizations.display_name as company_name',
                'organizations.country_code as country', 'organizations.size_band as size', 'organizations.active as company_active',
                'champion.id as user_id', 'champion.name as champion_name', 'champion.email as champion_email',
                'champion.email_verified_at as verified_at', 'champion.active as user_active',
                'assessments.id as assessment_id', 'assessments.current_submission_id',
                'submission_snapshots.overall_percent', 'submission_snapshots.band',
            ]);
        $pending = DB::table('app_users')
            ->where('role', 'champion')->whereNull('organization_id')
            ->select([
                DB::raw("'champion' as row_type"),
                DB::raw('null as org_id'), DB::raw('null as company_name'),
                DB::raw('null as country'), DB::raw('null as size'), DB::raw('null as company_active'),
                'id as user_id', 'name as champion_name', 'email as champion_email',
                'email_verified_at as verified_at', 'active as user_active',
                DB::raw('null as assessment_id'), DB::raw('null as current_submission_id'),
                DB::raw('null as overall_percent'), DB::raw('null as band'),
            ]);
        $query = DB::query()->fromSub($companies->unionAll($pending), 'd');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(fn ($q) => $q->where('d.company_name', 'like', $like)
                ->orWhere('d.champion_name', 'like', $like)->orWhere('d.champion_email', 'like', $like));
        }
        match ($filters['stage'] ?? null) {
            'submitted' => $query->whereNotNull('d.current_submission_id'),
            'in_progress' => $query->whereNotNull('d.assessment_id')->whereNull('d.current_submission_id'),
            'not_started' => $query->where('d.row_type', 'company')->whereNull('d.assessment_id'),
            'no_profile' => $query->where('d.row_type', 'champion'),
            default => null,
        };
        if (in_array($filters['band'] ?? null, self::BANDS, true)) {
            $query->where('d.band', $filters['band']);
        }

        $page = $query->orderByRaw('coalesce(d.company_name, d.champion_name)')
            ->orderBy('d.org_id')->orderBy('d.user_id')->paginate($perPage);

        $assessmentIds = $page->getCollection()->pluck('assessment_id')->filter()->values()->all();
        $answered = AssessmentAnswer::query()
            ->join('assessment_revisions', 'assessment_revisions.id', '=', 'assessment_answers.revision_id')
            ->where('assessment_revisions.status', 'draft')
            ->whereIn('assessment_revisions.assessment_id', $assessmentIds)
            ->whereNotNull('assessment_answers.answer')
            ->groupBy('assessment_revisions.assessment_id')
            ->selectRaw('assessment_revisions.assessment_id as aid, count(*) as answered')
            ->pluck('answered', 'aid');

        $items = $page->getCollection()->map(function (mixed $row) use ($answered): array {
            $r = (object) $row;
            $isCompany = $r->row_type === 'company';
            $stage = ! $isCompany ? 'no_profile'
                : ($r->current_submission_id !== null ? 'submitted' : ($r->assessment_id !== null ? 'in_progress' : 'not_started'));

            return [
                'id' => $isCompany ? $r->org_id : $r->user_id,
                'organizationId' => $isCompany ? $r->org_id : null,
                'name' => $isCompany ? $r->company_name : null,
                'country' => $r->country,
                'size' => $r->size,
                'active' => (bool) ($isCompany ? $r->company_active : $r->user_active),
                'champion' => $r->user_id !== null
                    ? ['name' => $r->champion_name, 'email' => $r->champion_email, 'verified' => $r->verified_at !== null]
                    : null,
                'stage' => $stage,
                'answeredCount' => match ($stage) {
                    'submitted' => 40,
                    'in_progress' => (int) ($answered[$r->assessment_id] ?? 0),
                    default => 0,
                },
                'overall' => $r->overall_percent !== null ? (float) $r->overall_percent : null,
                'band' => $r->band,
            ];
        })->values()->all();

        return [
            'data' => $items,
            'meta' => ['total' => $page->total(), 'page' => $page->currentPage(), 'perPage' => $page->perPage(), 'lastPage' => $page->lastPage()],
        ];
    }

    /** @return array<string, mixed> */
    public function organization(string $id): array
    {
        $org = Organization::query()->findOrFail($id);

        $submissions = AssessmentRevision::query()
            ->where('status', 'submitted')
            ->whereHas('assessment', fn ($q) => $q->where('organization_id', $org->id))
            ->with(['assessment', 'snapshot'])
            ->orderBy('submitted_at')->orderBy('revision_number')
            ->get()
            ->map(fn (AssessmentRevision $r) => [
                'id' => $r->id,
                'cycleId' => $r->assessment->cycle_id,
                'revisionNumber' => $r->revision_number,
                'effective' => $r->assessment->current_submission_id === $r->id,
                'isCorrection' => $r->parent_revision_id !== null,
                'overall' => $r->snapshot ? (float) $r->snapshot->overall_percent : null,
                'band' => $r->snapshot?->band,
                'submittedAt' => $r->submitted_at?->toISOString(),
            ])->values()->all();

        return [
            'id' => $org->id,
            'name' => $org->display_name,
            'country' => $org->country_code,
            'size' => $org->size_band,
            'registrationId' => $org->registration_id,
            'active' => (bool) $org->active,
            'isTest' => (bool) $org->is_test,
            'submissions' => $submissions,
        ];
    }

    /**
     * A submitted snapshot (drafts are 404 for staff).
     *
     * @return array<string, mixed>
     */
    public function submission(string $revisionId): array
    {
        $revision = AssessmentRevision::query()->whereKey($revisionId)->where('status', 'submitted')
            ->with(['snapshot', 'assessment'])->firstOrFail();
        abort_if($revision->snapshot === null, 404);

        return [
            'revisionId' => $revision->id,
            'organizationId' => $revision->assessment->organization_id,
            'effective' => $revision->assessment->current_submission_id === $revision->id,
            'checksum' => $revision->snapshot->canonical_sha256,
            'snapshot' => $revision->snapshot->snapshot,
        ];
    }

    /**
     * Compare 2–4 companies on their effective submissions in one cycle.
     *
     * @param  list<mixed>  $organizationIds
     * @return array<string, mixed>
     */
    public function compare(array $organizationIds, AssessmentCycle $cycle): array
    {
        $ids = array_values(array_unique(array_map('strval', $organizationIds)));
        if (count($ids) < 2 || count($ids) > 4) {
            throw ValidationException::withMessages(['organizationIds' => __('Choose 2 to 4 companies.')]);
        }

        $rows = $this->effective($cycle)
            ->whereIn('assessments.organization_id', $ids)
            ->get([
                'assessments.organization_id',
                'organizations.display_name',
                'assessments.current_submission_id as revision_id',
                'assessments.framework_version_id',
                'submission_snapshots.overall_percent',
                'submission_snapshots.band',
            ])->keyBy('organization_id');

        if ($rows->count() !== count($ids)) {
            throw ValidationException::withMessages(['organizationIds' => __('Every company must have a submitted assessment in this cycle.')]);
        }
        if ($rows->pluck('framework_version_id')->unique()->count() !== 1) {
            throw ValidationException::withMessages(['organizationIds' => __('Companies must use the same framework version.')]);
        }

        $domains = $this->domainResults($rows->pluck('revision_id')->all());

        return [
            'cycle' => $this->presentCycle($cycle),
            'companies' => array_map(function (string $id) use ($rows, $domains) {
                $row = $rows[$id];
                $byKey = $domains[$row->revision_id] ?? [];

                return [
                    'id' => $id,
                    'name' => $row->display_name,
                    'revisionId' => $row->revision_id,
                    'overall' => (float) $row->overall_percent,
                    'band' => $row->band,
                    'domains' => array_map(fn ($key, $d) => ['key' => $key] + $d, array_keys($byKey), $byKey),
                ];
            }, $ids),
        ];
    }

    /**
     * Rows for CSV/XLSX export: effective submissions only, filtered server-side.
     *
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function exportRows(AssessmentCycle $cycle, array $filters): array
    {
        $query = $this->effective($cycle)
            ->join('assessment_revisions', 'assessment_revisions.id', '=', 'assessments.current_submission_id');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where('organizations.display_name', 'like', '%'.addcslashes($search, '%_\\').'%');
        }
        if (in_array($filters['band'] ?? null, self::BANDS, true)) {
            $query->where('submission_snapshots.band', $filters['band']);
        }
        $rows = $query->orderBy('organizations.display_name')->get([
            'organizations.display_name', 'organizations.country_code', 'organizations.size_band',
            'assessments.current_submission_id as revision_id', 'assessment_revisions.revision_number',
            'assessment_revisions.submitted_at', 'submission_snapshots.overall_percent', 'submission_snapshots.band',
        ]);
        $domains = $this->domainResults($rows->pluck('revision_id')->all());

        return $rows->map(function ($r) use ($domains) {
            $byKey = $domains[$r->revision_id] ?? [];

            return [
                'company' => $r->display_name,
                'country' => $r->country_code,
                'size' => $r->size_band,
                'revisionId' => $r->revision_id,
                'revisionNumber' => (int) $r->revision_number,
                'submittedAt' => $r->submitted_at !== null ? Carbon::parse($r->submitted_at)->toISOString() : null,
                'overall' => (float) $r->overall_percent,
                'band' => $r->band,
                'domains' => array_map(fn ($k, $d) => ['key' => $k, 'name' => $d['name'], 'score' => $d['score']], array_keys($byKey), $byKey),
            ];
        })->values()->all();
    }

    /**
     * Every submitted revision of one company (all cycles), oldest first.
     *
     * @return list<array<string, mixed>>
     */
    public function organizationExportRows(string $organizationId): array
    {
        $org = Organization::query()->findOrFail($organizationId);
        $revisions = AssessmentRevision::query()->where('status', 'submitted')
            ->whereHas('assessment', fn ($q) => $q->where('organization_id', $org->id))
            ->with(['snapshot', 'assessment'])->orderBy('submitted_at')->orderBy('revision_number')->get();
        $domains = $this->domainResults($revisions->pluck('id')->all());

        return $revisions->map(function (AssessmentRevision $r) use ($org, $domains) {
            $byKey = $domains[$r->id] ?? [];

            return [
                'company' => $org->display_name,
                'country' => $org->country_code,
                'size' => $org->size_band,
                'revisionId' => $r->id,
                'revisionNumber' => $r->revision_number,
                'submittedAt' => $r->submitted_at?->toISOString(),
                'overall' => (float) ($r->snapshot->overall_percent ?? 0),
                'band' => $r->snapshot?->band,
                'domains' => array_map(fn ($k, $d) => ['key' => $k, 'name' => $d['name'], 'score' => $d['score']], array_keys($byKey), $byKey),
            ];
        })->values()->all();
    }

    /** Effective submissions of non-test companies in a cycle. */
    private function effective(AssessmentCycle $cycle): Builder
    {
        return Assessment::query()
            ->join('organizations', 'organizations.id', '=', 'assessments.organization_id')
            ->join('submission_snapshots', 'submission_snapshots.revision_id', '=', 'assessments.current_submission_id')
            ->where('assessments.cycle_id', $cycle->id)
            ->where('organizations.is_test', false)
            ->toBase();
    }

    /**
     * @param  list<string>  $revisionIds
     * @return array<string, array<string, array{name: string, score: int, band: string}>>
     */
    private function domainResults(array $revisionIds): array
    {
        $out = [];
        SubmissionDomainResult::query()
            ->join('framework_domains', 'framework_domains.id', '=', 'submission_domain_results.domain_id')
            ->whereIn('submission_domain_results.revision_id', $revisionIds)
            ->orderBy('framework_domains.position')
            ->get([
                'submission_domain_results.revision_id',
                'framework_domains.key',
                'framework_domains.title',
                'submission_domain_results.score_percent',
                'submission_domain_results.band',
            ])
            ->each(function ($r) use (&$out) {
                $out[$r->revision_id][$r->key] = ['name' => $r->title, 'score' => (int) $r->score_percent, 'band' => $r->band];
            });

        return $out;
    }

    /** @return array<string, mixed> */
    private function presentCycle(AssessmentCycle $cycle): array
    {
        return [
            'id' => $cycle->id,
            'title' => $cycle->title,
            'status' => $cycle->status,
            'frameworkVersion' => $cycle->frameworkVersion?->semantic_version,
        ];
    }
}
