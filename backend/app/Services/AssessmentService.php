<?php

namespace App\Services;

use App\Domain\Scoring\ScoringEngine;
use App\Models\AppUser;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCycle;
use App\Models\AssessmentRevision;
use App\Models\AuditEvent;
use App\Models\FrameworkDomain;
use App\Models\FrameworkQuestion;
use App\Models\FrameworkVersion;
use App\Models\IdempotencyRequest;
use App\Models\OutboxEvent;
use App\Models\RecommendationAction;
use App\Models\SubmissionDomainResult;
use App\Models\SubmissionSnapshot;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Phase 2B — Champion assessment operations (03-DATABASE-SCHEMA.md "Atomic
 * operations"). Every write locks the assessment aggregate first, then the
 * revision, so concurrent requests serialize. Conflicts are 409, never success.
 */
class AssessmentService
{
    public const SCHEMA_VERSION = '1.0.0';

    /** @var array<string, array<string, mixed>> */
    private array $frameworks = [];

    /** Active, verified Champion of an active company with authority confirmed. */
    public function assertEligible(AppUser $user): void
    {
        $org = $user->organization;
        if ($user->role !== 'champion' || ! $user->active || $user->email_verified_at === null
            || $org === null || ! $org->active || $org->authority_confirmed_at === null) {
            throw new AuthorizationException;
        }
    }

    public function openCycle(): AssessmentCycle
    {
        $cycle = AssessmentCycle::query()->where('status', 'open')->first();
        if ($cycle === null || ! $cycle->isOpen()) {
            throw ValidationException::withMessages(['cycle' => __('No assessment cycle is open.')]);
        }

        return $cycle;
    }

    /** Create or resume the Champion's draft for the open cycle. */
    public function start(AppUser $user): AssessmentRevision
    {
        $this->assertEligible($user);
        $cycle = $this->openCycle();

        return DB::transaction(function () use ($user, $cycle) {
            Assessment::query()->insertOrIgnore([
                'id' => (string) Str::ulid(),
                'organization_id' => $user->organization_id,
                'cycle_id' => $cycle->id,
                'framework_version_id' => $cycle->framework_version_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /** @var Assessment $assessment */
            $assessment = Assessment::query()->where('organization_id', $user->organization_id)
                ->where('cycle_id', $cycle->id)->lockForUpdate()->firstOrFail();

            $draft = $assessment->revisions()->where('status', 'draft')->first();
            if ($draft !== null) {
                return $draft;
            }
            if ($assessment->current_submission_id !== null) {
                abort(409, 'Already submitted for this cycle.');
            }

            $revision = AssessmentRevision::create([
                'assessment_id' => $assessment->id,
                'framework_version_id' => $assessment->framework_version_id,
                'revision_number' => 1,
                'status' => 'draft',
                'lock_version' => 0,
                'created_by' => $user->id,
            ]);
            $this->seedAnswers($revision);

            AuditEvent::record(action: 'assessment.started', actorId: $user->id, targetType: 'assessment_revision',
                targetId: $revision->id, organizationId: $user->organization_id);

            return $revision;
        });
    }

    /**
     * Save Yes/No/clear answers keyed by source question ID ("1.10").
     *
     * @param  array<string, mixed>  $answers
     */
    public function saveAnswers(AppUser $user, string $revisionId, int $expectedVersion, array $answers): AssessmentRevision
    {
        $this->assertEligible($user);

        return DB::transaction(function () use ($user, $revisionId, $expectedVersion, $answers) {
            $revision = $this->lockOwnedRevision($user, $revisionId);
            $this->assertEditable($revision, $expectedVersion);

            $questions = $this->questionMap($revision->framework_version_id);
            $rows = [];
            foreach ($answers as $sourceId => $value) {
                $sourceId = (string) $sourceId;
                if (! isset($questions[$sourceId]) || ! in_array($value, ['yes', 'no', null], true)) {
                    throw ValidationException::withMessages(['answers' => __('Answers must be yes, no or null for questions in this framework.')]);
                }
                $rows[] = [
                    'revision_id' => $revision->id,
                    'framework_version_id' => $revision->framework_version_id,
                    'question_id' => $questions[$sourceId],
                    'answer' => $value === null ? null : $value === 'yes',
                    'updated_at' => now(),
                ];
            }
            if ($rows !== []) {
                AssessmentAnswer::query()->upsert($rows, ['revision_id', 'question_id'], ['answer', 'updated_at']);
            }

            $revision->lock_version++;
            $revision->save();

            return $revision;
        });
    }

    /** Atomic, idempotent submission (Idempotency-Key). A retry returns the original revision. */
    public function submit(AppUser $user, string $revisionId, int $expectedVersion, bool $declaration, string $key): AssessmentRevision
    {
        $this->assertEligible($user);
        if (! $declaration) {
            throw ValidationException::withMessages(['declaration' => __('Confirm the declaration before submitting.')]);
        }
        $hash = hash('sha256', $revisionId.'|'.$expectedVersion.'|1');

        return DB::transaction(function () use ($user, $revisionId, $expectedVersion, $key, $hash) {
            IdempotencyRequest::query()->insertOrIgnore([
                'id' => (string) Str::ulid(), 'actor_id' => $user->id, 'operation' => 'assessment.submit',
                'idempotency_key' => $key, 'request_hash' => $hash, 'status' => 'pending',
                'expires_at' => now()->addDay(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            /** @var IdempotencyRequest $receipt */
            $receipt = IdempotencyRequest::query()->where('actor_id', $user->id)->where('operation', 'assessment.submit')
                ->where('idempotency_key', $key)->lockForUpdate()->firstOrFail();

            if ($receipt->request_hash !== $hash) {
                throw ValidationException::withMessages(['idempotencyKey' => __('This key was used for a different request.')]);
            }
            if ($receipt->status === 'completed') {
                return AssessmentRevision::query()->findOrFail($receipt->response_reference);
            }

            $revision = $this->lockOwnedRevision($user, $revisionId);
            $assessment = $revision->assessment;
            $this->assertEditable($revision, $expectedVersion);
            if ($revision->parent_revision_id === null) {
                $this->openCycle(); // first submissions need an open cycle; corrections may follow close
            }

            $framework = $this->framework($revision->framework_version_id);
            $answers = $this->answerMap($revision);
            try {
                $result = ScoringEngine::score($answers, $framework);
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages(['answers' => __('Answer all 40 questions before submitting.')]);
            }

            $now = now();
            $org = $user->organization;
            $snapshot = [
                'schemaVersion' => self::SCHEMA_VERSION,
                'scoringVersion' => '1.0.0',
                'framework' => ['id' => $revision->framework_version_id, 'version' => $framework['version'],
                    'title' => $framework['title'], 'domains' => $framework['domains'], 'interpretations' => $framework['interpretations']],
                'company' => ['id' => $org->id, 'name' => $org->display_name, 'country' => $org->country_code,
                    'size' => $org->size_band, 'registrationId' => $org->registration_id],
                'respondent' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'jobTitle' => $user->job_title],
                'declaration' => true,
                'revision' => ['id' => $revision->id, 'number' => $revision->revision_number, 'parentId' => $revision->parent_revision_id,
                    'correctionReason' => $revision->correction_reason],
                'answers' => $answers,
                'result' => $result,
                'submittedAt' => $now->toISOString(),
            ];
            $canonical = json_encode(self::sortKeys($snapshot), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

            SubmissionSnapshot::create([
                'revision_id' => $revision->id,
                'overall_yes_count' => $result['yes'],
                'overall_percent' => $result['overallBasisPoints'] / 100,
                'band' => $result['band'],
                'schema_version' => self::SCHEMA_VERSION,
                'scoring_version' => '1.0.0',
                'snapshot' => $snapshot,
                'canonical_sha256' => hash('sha256', $canonical),
                'submitted_at' => $now,
            ]);

            $domainIds = FrameworkDomain::query()->where('framework_version_id', $revision->framework_version_id)->pluck('id', 'key');
            foreach ($result['domains'] as $d) {
                SubmissionDomainResult::create([
                    'revision_id' => $revision->id,
                    'framework_version_id' => $revision->framework_version_id,
                    'domain_id' => $domainIds[$d['key']],
                    'yes_count' => $d['yes'],
                    'score_percent' => $d['score'],
                    'band' => $d['band'],
                ]);
            }

            $revision->forceFill(['status' => 'submitted', 'submitted_by' => $user->id, 'submitted_at' => $now])->save();
            $assessment->forceFill(['current_submission_id' => $revision->id])->save();

            AuditEvent::record(action: 'assessment.submitted', actorId: $user->id, targetType: 'assessment_revision',
                targetId: $revision->id, organizationId: $org->id,
                metadata: ['revision' => $revision->revision_number, 'band' => $result['band'], 'correction' => $revision->parent_revision_id !== null]);
            OutboxEvent::create(['event_type' => 'assessment.submitted', 'aggregate_type' => 'assessment',
                'aggregate_id' => $assessment->id, 'payload' => ['revisionId' => $revision->id, 'organizationId' => $org->id]]);

            $receipt->forceFill(['status' => 'completed', 'response_reference' => $revision->id])->save();

            return $revision->refresh();
        });
    }

    /**
     * Super Admin opens a correction draft from the effective submission
     * (allowed after cycle close). The prior submission stays effective until
     * the Champion submits the correction.
     */
    public function openCorrection(AppUser $admin, string $revisionId, string $reason): AssessmentRevision
    {
        if ($admin->role !== 'admin' || ! $admin->active) {
            throw new AuthorizationException;
        }
        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['reason' => __('Give a reason of at least 10 characters.')]);
        }

        return DB::transaction(function () use ($admin, $revisionId, $reason) {
            $assessmentId = AssessmentRevision::query()->whereKey($revisionId)->value('assessment_id');
            /** @var Assessment $assessment */
            $assessment = Assessment::query()->whereKey($assessmentId)->lockForUpdate()->firstOrFail();
            $source = AssessmentRevision::query()->whereKey($revisionId)->firstOrFail();

            if ($assessment->current_submission_id !== $source->id) {
                abort(409, 'Only the effective submission can be corrected.');
            }
            if ($assessment->revisions()->where('status', 'draft')->exists()) {
                abort(409, 'A correction is already open.');
            }

            // Allocated under the aggregate lock, so MAX + 1 is safe here.
            $next = (int) AssessmentRevision::query()->where('assessment_id', $assessment->id)->max('revision_number') + 1;
            $draft = AssessmentRevision::create([
                'assessment_id' => $assessment->id,
                'framework_version_id' => $assessment->framework_version_id,
                'revision_number' => $next,
                'parent_revision_id' => $source->id,
                'status' => 'draft',
                'correction_reason' => $reason,
                'lock_version' => 0,
                'created_by' => $admin->id,
            ]);
            $this->seedAnswers($draft, $source);

            AuditEvent::record(action: 'assessment.correction_opened', actorId: $admin->id, targetType: 'assessment_revision',
                targetId: $draft->id, organizationId: $assessment->organization_id,
                metadata: ['parentRevisionId' => $source->id, 'reason' => $reason]);

            return $draft;
        });
    }

    /** Own-company revision (404 for anything else, including forged IDs). */
    public function ownedRevision(AppUser $user, string $revisionId): AssessmentRevision
    {
        return AssessmentRevision::query()->whereKey($revisionId)
            ->whereHas('assessment', fn ($q) => $q->where('organization_id', $user->organization_id))
            ->firstOrFail();
    }

    /** @return list<array<string, mixed>> */
    public function history(AppUser $user): array
    {
        return AssessmentRevision::query()
            ->whereHas('assessment', fn ($q) => $q->where('organization_id', $user->organization_id))
            ->with('assessment')->orderBy('created_at')->orderBy('revision_number')->get()
            ->map(fn (AssessmentRevision $r) => [
                'id' => $r->id,
                'cycleId' => $r->assessment->cycle_id,
                'revisionNumber' => $r->revision_number,
                'status' => $r->status,
                'effective' => $r->assessment->current_submission_id === $r->id,
                'isCorrection' => $r->parent_revision_id !== null,
                'submittedAt' => $r->submitted_at?->toISOString(),
            ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function present(AssessmentRevision $revision): array
    {
        $answers = $this->answerMap($revision);

        return [
            'id' => $revision->id,
            'assessmentId' => $revision->assessment_id,
            'cycleId' => $revision->assessment->cycle_id,
            'frameworkVersion' => $this->framework($revision->framework_version_id)['version'],
            'revisionNumber' => $revision->revision_number,
            'status' => $revision->status,
            'version' => $revision->lock_version,
            'isCorrection' => $revision->parent_revision_id !== null,
            'correctionReason' => $revision->correction_reason,
            'answers' => $answers,
            'answeredCount' => ScoringEngine::completion($answers),
            'submittedAt' => $revision->submitted_at?->toISOString(),
        ];
    }

    /** Lock the aggregate, then the revision; scoped to the user's company. */
    public function lockOwnedRevision(AppUser $user, string $revisionId): AssessmentRevision
    {
        $assessmentId = AssessmentRevision::query()->whereKey($revisionId)->value('assessment_id');
        Assessment::query()->whereKey($assessmentId)->where('organization_id', $user->organization_id)
            ->lockForUpdate()->firstOrFail();

        return AssessmentRevision::query()->whereKey($revisionId)->lockForUpdate()->firstOrFail();
    }

    public function seedAnswers(AssessmentRevision $revision, ?AssessmentRevision $copyFrom = null): void
    {
        $previous = $copyFrom
            ? AssessmentAnswer::query()->where('revision_id', $copyFrom->id)->pluck('answer', 'question_id')->all()
            : [];
        $rows = [];
        foreach ($this->questionMap($revision->framework_version_id) as $questionId) {
            $rows[] = [
                'revision_id' => $revision->id,
                'framework_version_id' => $revision->framework_version_id,
                'question_id' => $questionId,
                'answer' => $previous[$questionId] ?? null,
                'updated_at' => now(),
            ];
        }
        AssessmentAnswer::query()->insert($rows);
    }

    private function assertEditable(AssessmentRevision $revision, int $expectedVersion): void
    {
        if (! $revision->isDraft() || $revision->lock_version !== $expectedVersion) {
            abort(409, 'Stale or submitted revision.');
        }
    }

    /** @return array<string, string|null> source question ID => yes|no|null, in framework order */
    private function answerMap(AssessmentRevision $revision): array
    {
        $stored = AssessmentAnswer::query()->where('revision_id', $revision->id)->pluck('answer', 'question_id')->all();
        $map = [];
        foreach ($this->questionMap($revision->framework_version_id) as $sourceId => $questionId) {
            $value = $stored[$questionId] ?? null;
            $map[$sourceId] = $value === null ? null : ((bool) $value ? 'yes' : 'no');
        }

        return $map;
    }

    /** @return array<string, string> source question ID => question ULID, in framework order */
    private function questionMap(string $frameworkVersionId): array
    {
        $map = [];
        foreach ($this->framework($frameworkVersionId)['domains'] as $domain) {
            foreach ($domain['questions'] as $q) {
                $map[$q['id']] = $q['dbId'];
            }
        }

        return $map;
    }

    /** Framework in the shape ScoringEngine expects, loaded from the pinned version. */
    public function framework(string $frameworkVersionId): array
    {
        if (isset($this->frameworks[$frameworkVersionId])) {
            return $this->frameworks[$frameworkVersionId];
        }

        $version = FrameworkVersion::query()->findOrFail($frameworkVersionId);
        $questions = FrameworkQuestion::query()->where('framework_version_id', $version->id)->orderBy('position')->get()->groupBy('domain_id');
        $actions = RecommendationAction::query()->where('framework_version_id', $version->id)->orderBy('position')->get()->groupBy('domain_id');

        $domains = [];
        foreach (FrameworkDomain::query()->where('framework_version_id', $version->id)->orderBy('position')->get() as $d) {
            $recs = [];
            foreach (['foundational', 'developing', 'advanced', 'best_in_class'] as $band) {
                $recs[$band] = ($actions[$d->id] ?? collect())->where('band', $band)->values()
                    ->map(fn ($a) => ['id' => $a->source_action_id, 'text' => $a->text, 'sourceCell' => $a->source_cell])->all();
            }
            $domains[] = [
                'key' => $d->key,
                'name' => $d->title,
                'order' => $d->position,
                'questions' => ($questions[$d->id] ?? collect())->map(fn ($q) => [
                    'id' => $q->source_question_id, 'dbId' => $q->id, 'text' => $q->text, 'sourceCell' => $q->source_cell,
                ])->values()->all(),
                'recommendations' => $recs,
            ];
        }

        return $this->frameworks[$frameworkVersionId] = [
            'version' => $version->semantic_version,
            'title' => $version->title,
            'domains' => $domains,
            'interpretations' => $version->interpretations,
        ];
    }

    private static function sortKeys(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map([self::class, 'sortKeys'], $value);
    }
}
