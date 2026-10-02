<?php

namespace Tests\Feature\Assessments;

use App\Models\AppUser;
use App\Models\Assessment;
use App\Models\AssessmentRevision;
use App\Models\Organization;
use App\Models\OutboxEvent;
use App\Models\SubmissionDomainResult;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2B — Champion endpoints: start/resume, save with version, idempotent submit, result, history. */
class AssessmentFlowTest extends TestCase
{
    public function test_a_champion_without_a_company_has_an_empty_history(): void
    {
        $this->actingAs(AppUser::factory()->role('champion')->create())
            ->getJson('/api/procurement/v1/assessments/history')->assertOk()->assertExactJson(['data' => []]);
    }

    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();
    }

    private function champion(): AppUser
    {
        $org = Organization::factory()->create();
        $org->forceFill(['authority_confirmed_at' => now()])->save();
        $user = AppUser::factory()->role('champion')->create();
        $user->forceFill(['organization_id' => $org->id])->save();

        return $user;
    }

    /** @return array<string, string> all 40 answered: first $yes per domain "yes" */
    private function allAnswers(int $yes = 5): array
    {
        $answers = [];
        foreach ([1, 2, 3, 4] as $d) {
            foreach (range(1, 10) as $q) {
                $answers["{$d}.{$q}"] = $q <= $yes ? 'yes' : 'no';
            }
        }

        return $answers;
    }

    private function submit(string $id, int $version, string $key = 'key-1')
    {
        return $this->withHeader('Idempotency-Key', $key)
            ->postJson(self::API."/assessments/{$id}/submit", ['expectedVersion' => $version, 'declaration' => true]);
    }

    public function test_start_creates_a_draft_then_resumes_the_same_one(): void
    {
        $user = $this->champion();

        $first = $this->actingAs($user)->postJson(self::API.'/assessments')->assertCreated();
        $first->assertJsonPath('data.status', 'draft')->assertJsonPath('data.answeredCount', 0)->assertJsonPath('data.version', 0);
        $this->assertCount(40, $first->json('data.answers'));
        $this->assertNull($first->json('data.answers')['1.10']);

        $this->postJson(self::API.'/assessments')->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, Assessment::query()->count());
    }

    public function test_save_requires_the_current_version_and_null_is_not_no(): void
    {
        $user = $this->champion();
        $id = $this->actingAs($user)->postJson(self::API.'/assessments')->json('data.id');

        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.10' => 'yes', '1.1' => 'no']])
            ->assertOk()->assertJsonPath('data.version', 1)->assertJsonPath('data.answeredCount', 2);
        $answers = $this->getJson(self::API."/assessments/{$id}")->json('data.answers');
        $this->assertSame('yes', $answers['1.10']);
        $this->assertSame('no', $answers['1.1']);

        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.2' => 'yes']])
            ->assertStatus(409)->assertJsonPath('error.code', 'conflict');

        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 1, 'answers' => ['1.10' => null]])
            ->assertOk()->assertJsonPath('data.answeredCount', 1);
        $this->assertNull($this->getJson(self::API."/assessments/{$id}")->json('data.answers')['1.10']);
    }

    public function test_unknown_questions_and_bad_values_are_refused(): void
    {
        $user = $this->champion();
        $id = $this->actingAs($user)->postJson(self::API.'/assessments')->json('data.id');

        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.11' => 'yes']])->assertStatus(422);
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.1' => 'maybe']])->assertStatus(422);
        $this->assertSame(0, AssessmentRevision::query()->findOrFail($id)->lock_version);
    }

    public function test_incomplete_submission_and_missing_declaration_or_key_are_refused(): void
    {
        $user = $this->champion();
        $id = $this->actingAs($user)->postJson(self::API.'/assessments')->json('data.id');
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.1' => 'yes']]);

        $this->submit($id, 1)->assertStatus(422);
        $this->withHeader('Idempotency-Key', 'k')->postJson(self::API."/assessments/{$id}/submit", ['expectedVersion' => 1])->assertStatus(422);
        $this->withoutHeader('Idempotency-Key')->postJson(self::API."/assessments/{$id}/submit", ['expectedVersion' => 1, 'declaration' => true])->assertStatus(422);
        $this->assertSame('draft', AssessmentRevision::query()->findOrFail($id)->status);
    }

    public function test_submit_scores_snapshots_and_a_retry_returns_the_same_result(): void
    {
        $user = $this->champion();
        $id = $this->actingAs($user)->postJson(self::API.'/assessments')->json('data.id');
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => $this->allAnswers(5)]);

        $this->submit($id, 1)->assertOk()->assertJsonPath('data.status', 'submitted');
        $this->submit($id, 1)->assertOk()->assertJsonPath('data.id', $id);
        $this->submit($id, 1, 'other-key')->assertStatus(409);

        $result = $this->getJson(self::API."/assessments/{$id}/result")->assertOk();
        $this->assertSame(50, $result->json('data.snapshot.result.overall'));
        $this->assertSame('developing', $result->json('data.snapshot.result.band'));
        $this->assertSame(64, strlen($result->json('data.checksum')));

        $this->assertSame($id, Assessment::query()->sole()->current_submission_id);
        $this->assertSame(4, SubmissionDomainResult::query()->where('revision_id', $id)->count());
        $this->assertSame(1, OutboxEvent::query()->count());
        $this->assertDatabaseHas('audit_events', ['action' => 'assessment.submitted', 'target_id' => $id]);

        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 2, 'answers' => ['1.1' => 'no']])->assertStatus(409);
        $this->postJson(self::API.'/assessments')->assertStatus(409);
    }

    public function test_history_lists_own_revisions_with_the_effective_one(): void
    {
        $user = $this->champion();
        $id = $this->actingAs($user)->postJson(self::API.'/assessments')->json('data.id');
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => $this->allAnswers(10)]);
        $this->submit($id, 1);

        $this->getJson(self::API.'/assessments/history')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.effective', true);
    }

    public function test_another_company_and_forged_ids_get_404(): void
    {
        $owner = $this->champion();
        $id = app(AssessmentService::class)->start($owner)->id;

        $other = $this->champion();
        $this->actingAs($other)->getJson(self::API."/assessments/{$id}")->assertNotFound();
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => ['1.1' => 'yes']])->assertNotFound();
        $this->getJson(self::API.'/assessments/01JZZZZZZZZZZZZZZZZZZZZZZZ')->assertNotFound();
    }

    public function test_staff_cannot_start(): void
    {
        $analyst = AppUser::factory()->role('analyst')->create();
        $this->actingAs($analyst)->postJson(self::API.'/assessments')->assertForbidden();
    }

    public function test_a_company_without_confirmed_authority_cannot_start(): void
    {
        $user = $this->champion();
        $user->organization->forceFill(['authority_confirmed_at' => null])->save();
        $this->actingAs($user->fresh())->postJson(self::API.'/assessments')->assertForbidden();
    }
}
