<?php

namespace Tests\Feature\Assessments;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2B — open cycles and published framework content. */
class FrameworkContentTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
        $this->artisan('procurement:framework:import')->assertSuccessful();
    }

    private function publishAndOpen(): void
    {
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();
    }

    public function test_published_framework_content_is_returned_without_internal_ids(): void
    {
        $this->publishAndOpen();
        $res = $this->actingAs(AppUser::factory()->role('champion')->create())->getJson(self::API.'/frameworks/1.0.0')->assertOk()
            ->assertJsonPath('data.version', '1.0.0')->assertJsonCount(4, 'data.domains')->assertJsonCount(10, 'data.domains.0.questions')
            ->assertJsonCount(4, 'data.domains.0.recommendations.foundational');

        $this->assertSame('1.10', $res->json('data.domains.0.questions.9.id'));
        $this->assertStringNotContainsString('dbId', $res->getContent());
    }

    public function test_a_draft_framework_is_404(): void
    {
        $this->actingAs(AppUser::factory()->role('champion')->create())->getJson(self::API.'/frameworks/1.0.0')->assertNotFound();
    }

    public function test_open_cycles_are_listed_with_their_framework(): void
    {
        $user = AppUser::factory()->role('champion')->create();
        $this->actingAs($user)->getJson(self::API.'/cycles/available')->assertOk()->assertJsonCount(0, 'data');

        $this->publishAndOpen();
        $this->getJson(self::API.'/cycles/available')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.frameworkVersion', '1.0.0')->assertJsonPath('data.0.timezone', 'Asia/Riyadh');
    }

    public function test_guests_are_refused(): void
    {
        $this->getJson(self::API.'/cycles/available')->assertUnauthorized();
        $this->getJson(self::API.'/frameworks/1.0.0')->assertUnauthorized();
    }
}
