<?php

namespace Tests\Feature\Concurrency;

use App\Models\AppUser;
use App\Models\Assessment;
use App\Models\AssessmentRevision;
use App\Models\Organization;
use App\Services\AssessmentService;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Phase 2B — assessment races on MySQL with committed rows and a second
 * connection, so row locks and unique constraints are real.
 */
class AssessmentRaceTest extends TestCase
{
    use DatabaseTruncation;

    private AppUser $champion;

    private AssessmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();

        $org = Organization::factory()->create();
        $org->forceFill(['authority_confirmed_at' => now()])->save();
        $this->champion = AppUser::factory()->role('champion')->create();
        $this->champion->forceFill(['organization_id' => $org->id])->save();
        $this->service = app(AssessmentService::class);
    }

    private function second(): Connection
    {
        $default = (string) config('database.default');
        config(['database.connections.race' => config("database.connections.{$default}")]);

        return DB::connection('race');
    }

    /** @return array<string, string> */
    private function allNo(): array
    {
        $a = [];
        foreach ([1, 2, 3, 4] as $d) {
            foreach (range(1, 10) as $q) {
                $a["{$d}.{$q}"] = 'no';
            }
        }

        return $a;
    }

    private function assertBlockedBy(Connection $other, string $table, string $id, callable $operation): void
    {
        $other->beginTransaction();
        $other->table($table)->where('id', $id)->lockForUpdate()->first();
        DB::statement('SET SESSION innodb_lock_wait_timeout = 1');

        try {
            $operation();
            $this->fail('Operation must wait for the concurrent lock.');
        } catch (QueryException $e) {
            $this->assertSame(1205, $e->errorInfo[1] ?? null, 'expected lock wait timeout');
        } finally {
            $other->rollBack();
            DB::statement('SET SESSION innodb_lock_wait_timeout = 50');
        }
    }

    public function test_the_database_refuses_a_second_assessment_and_a_second_open_draft(): void
    {
        $draft = $this->service->start($this->champion);
        $assessment = Assessment::query()->sole();

        try {
            DB::table('assessments')->insert(['id' => (string) Str::ulid(), 'organization_id' => $assessment->organization_id,
                'cycle_id' => $assessment->cycle_id, 'framework_version_id' => $assessment->framework_version_id,
                'created_at' => now(), 'updated_at' => now()]);
            $this->fail('Second assessment for the same company and cycle must be refused.');
        } catch (QueryException $e) {
            $this->assertSame(1062, $e->errorInfo[1] ?? null);
        }

        try {
            DB::table('assessment_revisions')->insert(['id' => (string) Str::ulid(), 'assessment_id' => $assessment->id,
                'framework_version_id' => $assessment->framework_version_id, 'revision_number' => 2, 'status' => 'draft',
                'lock_version' => 0, 'created_by' => $this->champion->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->fail('Second open draft must be refused.');
        } catch (QueryException $e) {
            $this->assertSame(1062, $e->errorInfo[1] ?? null);
        }

        $this->assertSame(1, AssessmentRevision::query()->count());
        $this->assertSame($draft->id, AssessmentRevision::query()->sole()->id);
    }

    public function test_a_save_waits_while_a_submit_holds_the_assessment_lock(): void
    {
        $draft = $this->service->start($this->champion);

        $this->assertBlockedBy($this->second(), 'assessments', $draft->assessment_id,
            fn () => $this->service->saveAnswers($this->champion, $draft->id, 0, ['1.1' => 'yes']));

        $this->assertSame(0, AssessmentRevision::query()->findOrFail($draft->id)->lock_version, 'nothing written while blocked');
    }

    public function test_a_save_after_a_committed_submit_is_a_conflict_not_success(): void
    {
        $draft = $this->service->start($this->champion);
        $this->service->saveAnswers($this->champion, $draft->id, 0, $this->allNo());
        $this->service->submit($this->champion, $draft->id, 1, true, 'first');

        try {
            $this->service->saveAnswers($this->champion, $draft->id, 1, ['1.1' => 'yes']); // stale tab
            $this->fail('Save after submit must be refused.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
    }

    public function test_crossing_submits_with_different_keys_create_one_submission(): void
    {
        $draft = $this->service->start($this->champion);
        $this->service->saveAnswers($this->champion, $draft->id, 0, $this->allNo());

        $this->service->submit($this->champion, $draft->id, 1, true, 'tab-a');
        try {
            $this->service->submit($this->champion, $draft->id, 1, true, 'tab-b');
            $this->fail('Second submit must be refused.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }

        $this->assertSame(1, DB::table('submission_snapshots')->count());
        $this->assertSame(1, DB::table('outbox_events')->count());
    }

    public function test_a_retry_with_the_same_key_waits_for_the_first_and_returns_its_result(): void
    {
        $draft = $this->service->start($this->champion);
        $this->service->saveAnswers($this->champion, $draft->id, 0, $this->allNo());
        $first = $this->service->submit($this->champion, $draft->id, 1, true, 'same');
        $receipt = DB::table('idempotency_requests')->where('idempotency_key', 'same')->value('id');

        $this->assertBlockedBy($this->second(), 'idempotency_requests', $receipt,
            fn () => $this->service->submit($this->champion, $draft->id, 1, true, 'same'));

        $this->assertSame($first->id, $this->service->submit($this->champion, $draft->id, 1, true, 'same')->id);
        $this->assertSame(1, DB::table('submission_snapshots')->count());
    }

    public function test_overlapping_corrections_open_only_one_draft(): void
    {
        $draft = $this->service->start($this->champion);
        $this->service->saveAnswers($this->champion, $draft->id, 0, $this->allNo());
        $submitted = $this->service->submit($this->champion, $draft->id, 1, true, 'seed');
        $a = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $b = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();

        $this->assertBlockedBy($this->second(), 'assessments', $submitted->assessment_id,
            fn () => $this->service->openCorrection($a, $submitted->id, 'Admin A correction reason'));

        $this->service->openCorrection($a, $submitted->id, 'Admin A correction reason');
        try {
            $this->service->openCorrection($b, $submitted->id, 'Admin B correction reason');
            $this->fail('Second correction must be refused.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }

        $this->assertSame(1, AssessmentRevision::query()->where('status', 'draft')->count());
        $this->assertSame($submitted->id, Assessment::query()->sole()->current_submission_id);
    }
}
