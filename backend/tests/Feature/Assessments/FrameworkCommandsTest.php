<?php

namespace Tests\Feature\Assessments;

use App\Models\AssessmentCycle;
use App\Models\FrameworkVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2B — operator commands: import, publish (approval ref) and cycle create. */
class FrameworkCommandsTest extends TestCase
{
    use RefreshDatabase;

    private function importAndPublish(): void
    {
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
    }

    public function test_import_creates_a_draft_with_64_actions(): void
    {
        $this->artisan('procurement:framework:import')->assertSuccessful();

        $v = FrameworkVersion::query()->sole();
        $this->assertSame('draft', $v->status);
        $this->assertSame(64, $v->actions()->count());
    }

    public function test_import_fails_on_a_checksum_mismatch(): void
    {
        $this->artisan('procurement:framework:import', ['--source' => 'composer.json'])->assertFailed();
        $this->assertSame(0, FrameworkVersion::query()->count());
    }

    public function test_publish_requires_an_approval_reference(): void
    {
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0'])->assertFailed();
        $this->assertSame('draft', FrameworkVersion::query()->sole()->status);

        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->assertSame('LOCAL-TEST', FrameworkVersion::query()->sole()->approval_reference);
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'AGAIN'])->assertFailed();
    }

    public function test_cycle_create_uses_riyadh_business_dates_and_is_audited(): void
    {
        $this->importAndPublish();

        $this->artisan('procurement:cycle:create', ['--title' => '2026 annual', '--framework' => '1.0.0',
            '--opens' => '2026-01-01', '--closes' => '2026-12-31'])->assertSuccessful();

        $cycle = AssessmentCycle::query()->sole();
        $this->assertSame('Asia/Riyadh', $cycle->business_timezone);
        $this->assertSame('2025-12-31 21:00:00', $cycle->opens_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-31 20:59:59', $cycle->closes_at->utc()->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('audit_events', ['action' => 'cycle.created', 'target_id' => $cycle->id]);
    }

    public function test_cycle_create_refuses_a_draft_framework_bad_dates_and_a_second_open_cycle(): void
    {
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $args = ['--title' => 'C', '--framework' => '1.0.0', '--opens' => '2026-01-01', '--closes' => '2026-12-31'];
        $this->artisan('procurement:cycle:create', $args)->assertFailed();

        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', [...$args, '--closes' => '2025-12-31'])->assertFailed();
        $this->artisan('procurement:cycle:create', $args)->assertSuccessful();
        $this->artisan('procurement:cycle:create', $args)->assertFailed();

        $this->assertSame(1, AssessmentCycle::query()->count());
    }
}
