<?php

namespace Tests\Feature\Assessments;

use App\Models\AppUser;
use App\Models\Organization;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2B — staff portfolio, directory, company detail, submission view and comparison. */
class StaffPortfolioTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    /** @var array<string, Organization> */
    private array $orgs = [];

    /** @var array<string, string> */
    private array $revisions = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();

        $service = app(AssessmentService::class);
        // Alpha: submitted, all yes (100). Bravo: submitted, half yes (50).
        // Charlie: draft with 2 answers. Delta: not started. Test Co: submitted but is_test.
        foreach (['Alpha' => 10, 'Bravo' => 5, 'Charlie' => null, 'Delta' => false, 'Test Co' => 10] as $name => $yes) {
            $org = Organization::factory()->create(['display_name' => $name]);
            $org->forceFill(['authority_confirmed_at' => now(), 'is_test' => $name === 'Test Co'])->save();
            $this->orgs[$name] = $org;
            if ($yes === false) {
                continue;
            }
            $user = AppUser::factory()->role('champion')->create();
            $user->forceFill(['organization_id' => $org->id])->save();
            $draft = $service->start($user);
            if ($yes === null) {
                $service->saveAnswers($user, $draft->id, 0, ['1.1' => 'yes', '1.2' => 'no']);
                $this->revisions[$name] = $draft->id;

                continue;
            }
            $service->saveAnswers($user, $draft->id, 0, $this->answers($yes));
            $this->revisions[$name] = $service->submit($user, $draft->id, 1, true, "seed-{$name}")->id;
        }
    }

    /** @return array<string, string> */
    private function answers(int $yes): array
    {
        $a = [];
        foreach ([1, 2, 3, 4] as $d) {
            foreach (range(1, 10) as $q) {
                $a["{$d}.{$q}"] = $q <= $yes ? 'yes' : 'no';
            }
        }

        return $a;
    }

    private function analyst(): AppUser
    {
        return AppUser::factory()->role('analyst')->twoFactorConfirmed()->create();
    }

    public function test_portfolio_counts_effective_submissions_and_excludes_test_companies(): void
    {
        $this->actingAs($this->analyst())->getJson(self::API.'/staff/portfolio')->assertOk()
            ->assertJsonPath('data.submittedCount', 2)
            ->assertJsonPath('data.inProgressCount', 1)
            ->assertJsonPath('data.averageOverall', 75)
            ->assertJsonPath('data.bands.developing', 1)
            ->assertJsonPath('data.bands.best_in_class', 1)
            ->assertJsonCount(4, 'data.domains')
            ->assertJsonPath('data.domains.0.average', 75);
    }

    public function test_directory_shows_stage_and_answered_count_but_never_answers(): void
    {
        $res = $this->actingAs($this->analyst())->getJson(self::API.'/staff/organizations')->assertOk()
            ->assertJsonPath('meta.total', 4);

        $byName = collect($res->json('data'))->keyBy('name');
        $this->assertFalse($byName->has('Test Co'));
        $this->assertSame('submitted', $byName['Alpha']['stage']);
        $this->assertSame('in_progress', $byName['Charlie']['stage']);
        $this->assertSame(2, $byName['Charlie']['answeredCount']);
        $this->assertNull($byName['Charlie']['band']);
        $this->assertSame('not_started', $byName['Delta']['stage']);
        $this->assertStringNotContainsString('"answers"', $res->getContent());
    }

    public function test_directory_filters_and_paginates_on_the_server(): void
    {
        $analyst = $this->analyst();
        $this->actingAs($analyst)->getJson(self::API.'/staff/organizations?stage=submitted')
            ->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson(self::API.'/staff/organizations?band=best_in_class')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.name', 'Alpha');
        $this->getJson(self::API.'/staff/organizations?search=char')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.name', 'Charlie');
        $this->getJson(self::API.'/staff/organizations?perPage=2&page=2')
            ->assertOk()->assertJsonPath('meta.lastPage', 2)->assertJsonCount(2, 'data')->assertJsonPath('data.0.name', 'Charlie');
        $this->getJson(self::API.'/staff/organizations?stage=bogus')->assertStatus(422);
    }

    public function test_company_detail_lists_submissions_and_staff_cannot_open_drafts(): void
    {
        $analyst = $this->analyst();
        $this->actingAs($analyst)->getJson(self::API.'/staff/organizations/'.$this->orgs['Alpha']->id)->assertOk()
            ->assertJsonCount(1, 'data.submissions')->assertJsonPath('data.submissions.0.effective', true);

        $this->getJson(self::API.'/staff/assessments/'.$this->revisions['Alpha'])->assertOk()
            ->assertJsonPath('data.snapshot.result.overall', 100);
        $this->getJson(self::API.'/staff/assessments/'.$this->revisions['Charlie'])->assertNotFound();
        $this->getJson(self::API.'/staff/organizations/01JZZZZZZZZZZZZZZZZZZZZZZZ')->assertNotFound();
    }

    public function test_compare_two_to_four_companies_with_submissions(): void
    {
        $analyst = $this->analyst();
        $res = $this->actingAs($analyst)->postJson(self::API.'/staff/comparisons', [
            'organizationIds' => [$this->orgs['Alpha']->id, $this->orgs['Bravo']->id],
        ])->assertOk()->assertJsonCount(2, 'data.companies');
        $this->assertSame(100.0, (float) $res->json('data.companies.0.overall'));
        $this->assertCount(4, $res->json('data.companies.1.domains'));

        $this->postJson(self::API.'/staff/comparisons', ['organizationIds' => [$this->orgs['Alpha']->id]])->assertStatus(422);
        $this->postJson(self::API.'/staff/comparisons', [
            'organizationIds' => [$this->orgs['Alpha']->id, $this->orgs['Charlie']->id],
        ])->assertStatus(422);
        $this->postJson(self::API.'/staff/comparisons', [
            'organizationIds' => [$this->orgs['Alpha']->id, $this->orgs['Test Co']->id],
        ])->assertStatus(422);
    }

    public function test_export_is_refused_without_the_export_grant(): void
    {
        $this->actingAs($this->analyst())->postJson(self::API.'/staff/exports', ['format' => 'csv'])->assertForbidden();
        $this->assertDatabaseMissing('audit_events', ['action' => 'portfolio.exported']);
    }

    public function test_export_returns_filtered_effective_rows_and_is_audited(): void
    {
        $granted = AppUser::factory()->role('analyst')->twoFactorConfirmed()->create(['can_export' => true]);
        $res = $this->actingAs($granted)->postJson(self::API.'/staff/exports', ['format' => 'xlsx'])->assertOk();
        $this->assertSame(['Alpha', 'Bravo'], array_column($res->json('data.rows'), 'company'));
        $this->assertCount(4, $res->json('data.rows.0.domains'));
        $this->assertArrayNotHasKey('answers', $res->json('data.rows.0'));
        $this->assertDatabaseHas('audit_events', ['action' => 'portfolio.exported', 'actor_id' => $granted->id]);

        $this->actingAs($granted)->postJson(self::API.'/staff/exports', ['format' => 'csv', 'band' => 'developing'])
            ->assertOk()->assertJsonCount(1, 'data.rows')->assertJsonPath('data.rows.0.company', 'Bravo');
        $this->actingAs($granted)->postJson(self::API.'/staff/exports', ['format' => 'pdf'])->assertUnprocessable();
    }

    public function test_registered_champions_without_a_company_are_listed_for_staff(): void
    {
        $pending = AppUser::factory()->role('champion')->create(['name' => 'Pending Person', 'email' => 'pending@newco.test']);
        $res = $this->actingAs($this->analyst())->getJson(self::API.'/staff/champions/pending')->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.email', 'pending@newco.test');
        $this->assertArrayHasKey('verified', $res->json('data.0'));
        $this->getJson(self::API.'/staff/champions/pending?search=nobody')->assertOk()->assertJsonPath('meta.total', 0);
        $this->assertNotNull($pending->id);
    }

    public function test_champions_are_refused(): void
    {
        $champion = AppUser::factory()->role('champion')->create();
        $this->actingAs($champion)->getJson(self::API.'/staff/portfolio')->assertForbidden();
    }

    public function test_analysts_without_two_factor_are_refused(): void
    {
        $this->actingAs(AppUser::factory()->role('analyst')->create())->getJson(self::API.'/staff/organizations')
            ->assertForbidden()->assertJsonPath('error.code', 'two_factor_required');
    }
}
