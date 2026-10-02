<?php

namespace Tests\Feature\Assessments;

use App\Models\AppUser;
use App\Models\Assessment;
use App\Models\AssessmentCycle;
use App\Models\AssessmentRevision;
use App\Models\Organization;
use App\Models\SubmissionSnapshot;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2B — Super Admin corrections: reason, after cycle close, effective pointer, audit. */
class CorrectionTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    private AppUser $champion;

    private string $submittedId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();

        $org = Organization::factory()->create();
        $org->forceFill(['authority_confirmed_at' => now()])->save();
        $this->champion = AppUser::factory()->role('champion')->create();
        $this->champion->forceFill(['organization_id' => $org->id])->save();

        $service = app(AssessmentService::class);
        $draft = $service->start($this->champion);
        $service->saveAnswers($this->champion, $draft->id, 0, $this->answers('no'));
        $this->submittedId = $service->submit($this->champion, $draft->id, 1, true, 'seed')->id;
    }

    /** @return array<string, string> */
    private function answers(string $value): array
    {
        $a = [];
        foreach ([1, 2, 3, 4] as $d) {
            foreach (range(1, 10) as $q) {
                $a["{$d}.{$q}"] = $value;
            }
        }

        return $a;
    }

    private function admin(): AppUser
    {
        return AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
    }

    private function open(AppUser $actor, string $reason = 'Respondent reported a data entry error')
    {
        return $this->actingAs($actor)->postJson(self::API."/staff/assessments/{$this->submittedId}/corrections", ['reason' => $reason]);
    }

    public function test_super_admin_opens_a_correction_copying_the_answers(): void
    {
        $res = $this->open($this->admin())->assertCreated()
            ->assertJsonPath('data.status', 'draft')->assertJsonPath('data.revisionNumber', 2)
            ->assertJsonPath('data.isCorrection', true)->assertJsonPath('data.answeredCount', 40);

        $draft = AssessmentRevision::query()->findOrFail($res->json('data.id'));
        $this->assertSame($this->submittedId, $draft->parent_revision_id);
        $this->assertSame($this->submittedId, Assessment::query()->sole()->current_submission_id, 'prior stays effective');
        $this->assertDatabaseHas('audit_events', ['action' => 'assessment.correction_opened', 'target_id' => $draft->id]);
    }

    public function test_reason_must_be_at_least_ten_characters(): void
    {
        $this->open($this->admin(), 'too short')->assertStatus(422);
        $this->assertSame(1, AssessmentRevision::query()->count());
    }

    public function test_an_analyst_is_refused(): void
    {
        $this->open(AppUser::factory()->role('analyst')->twoFactorConfirmed()->create())->assertForbidden();
        $this->assertSame(1, AssessmentRevision::query()->count());
    }

    public function test_a_super_admin_without_two_factor_is_refused(): void
    {
        $this->open(AppUser::factory()->role('admin')->create())
            ->assertForbidden()->assertJsonPath('error.code', 'two_factor_required');
        $this->assertSame(1, AssessmentRevision::query()->count());
    }

    public function test_a_second_open_correction_and_correcting_a_stale_revision_are_409(): void
    {
        $admin = $this->admin();
        app(AssessmentService::class)->openCorrection($admin, $this->submittedId, 'First correction reason');

        $this->open($admin)->assertStatus(409);
        $this->assertSame(2, AssessmentRevision::query()->count());
    }

    public function test_correction_works_after_the_cycle_closes_and_advances_the_pointer_on_submit(): void
    {
        $admin = $this->admin();
        $service = app(AssessmentService::class);
        $draft = $service->openCorrection($admin, $this->submittedId, 'Correcting after the cycle closed');

        AssessmentCycle::query()->update(['status' => 'closed']);

        $service->saveAnswers($this->champion, $draft->id, 0, ['1.1' => 'yes']);
        $this->assertSame($this->submittedId, Assessment::query()->sole()->current_submission_id);

        $corrected = $service->submit($this->champion, $draft->id, 1, true, 'corr');
        $this->assertSame('submitted', $corrected->status);
        $this->assertSame($corrected->id, Assessment::query()->sole()->current_submission_id);

        $old = SubmissionSnapshot::query()->findOrFail($this->submittedId);
        $new = SubmissionSnapshot::query()->findOrFail($corrected->id);
        $this->assertSame(0, $old->overall_yes_count, 'original snapshot unchanged');
        $this->assertSame(1, $new->overall_yes_count);
        $this->assertSame('submitted', AssessmentRevision::query()->findOrFail($this->submittedId)->status);
    }

    public function test_forged_revision_ids_are_404(): void
    {
        $this->actingAs($this->admin())->postJson(self::API.'/staff/assessments/01JZZZZZZZZZZZZZZZZZZZZZZZ/corrections',
            ['reason' => 'A sufficiently long reason'])->assertNotFound();
    }
}
