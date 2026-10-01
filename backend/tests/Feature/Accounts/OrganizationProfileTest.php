<?php

namespace Tests\Feature\Accounts;

use App\Models\AppUser;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2A — company profile (decisions.md D-31): one Champion per company,
 * approved fields only (no sector), duplicate company blocked. MySQL-backed.
 */
class OrganizationProfileTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/procurement/v1/organization';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
    }

    /** @return array<string, string> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'displayName' => 'Acme Procurement Ltd',
            'countryCode' => 'SA',
            'sizeBand' => '51-250',
            'registrationId' => 'CR-12345',
        ], $overrides);
    }

    public function test_verified_champion_creates_a_profile_and_is_linked(): void
    {
        $champion = AppUser::factory()->create();

        $this->actingAs($champion)->patchJson(self::URL, $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.displayName', 'Acme Procurement Ltd')
            ->assertJsonPath('data.countryCode', 'SA');

        $champion->refresh();
        $this->assertNotNull($champion->organization_id);
        $this->assertDatabaseHas('organizations', [
            'id' => $champion->organization_id,
            'normalized_name' => 'acme procurement ltd',
            'country_code' => 'SA',
        ]);
        $this->assertDatabaseHas('audit_events', ['action' => 'organization.created']);
    }

    public function test_get_returns_profile_or_404_before_creation(): void
    {
        $champion = AppUser::factory()->create();

        $this->actingAs($champion)->getJson(self::URL)
            ->assertNotFound()->assertJsonPath('error.code', 'not_found');

        $this->actingAs($champion)->patchJson(self::URL, $this->payload())->assertCreated();

        $this->actingAs($champion->refresh())->getJson(self::URL)
            ->assertOk()->assertJsonPath('data.displayName', 'Acme Procurement Ltd');
    }

    public function test_update_edits_in_place_without_creating_a_second_company(): void
    {
        $champion = AppUser::factory()->create();
        $this->actingAs($champion)->patchJson(self::URL, $this->payload())->assertCreated();
        $firstId = $champion->refresh()->organization_id;

        $this->actingAs($champion)->patchJson(self::URL, $this->payload(['sizeBand' => '1-50']))
            ->assertOk()->assertJsonPath('data.sizeBand', '1-50');

        $this->assertSame($firstId, $champion->refresh()->organization_id);
        $this->assertSame(1, Organization::query()->count());
    }

    public function test_duplicate_company_name_in_same_country_is_blocked(): void
    {
        Organization::factory()->create([
            'display_name' => 'Acme Procurement Ltd',
            'normalized_name' => Organization::normalize('Acme Procurement Ltd'),
            'country_code' => 'SA',
        ]);

        $champion = AppUser::factory()->create();
        $this->actingAs($champion)->patchJson(self::URL, $this->payload(['registrationId' => null]))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');

        $this->assertNull($champion->refresh()->organization_id);
    }

    public function test_duplicate_registration_id_is_blocked(): void
    {
        Organization::factory()->withRegistration('CR-12345')->create([
            'country_code' => 'SA',
        ]);

        $champion = AppUser::factory()->create();
        $this->actingAs($champion)->patchJson(self::URL, $this->payload())
            ->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_unverified_champion_cannot_set_a_profile(): void
    {
        $champion = AppUser::factory()->unverified()->create();

        $this->actingAs($champion)->patchJson(self::URL, $this->payload())
            ->assertStatus(403);
    }

    public function test_staff_cannot_use_the_champion_profile_endpoint(): void
    {
        $analyst = AppUser::factory()->role('analyst')->create();

        $this->actingAs($analyst)->patchJson(self::URL, $this->payload())
            ->assertStatus(403);
    }

    public function test_sector_and_privileged_fields_are_never_persisted(): void
    {
        $champion = AppUser::factory()->create();

        $this->actingAs($champion)->patchJson(self::URL, $this->payload([
            'sector' => 'Defense',
            'active' => false,
            'is_test' => true,
        ]))->assertCreated();

        $org = Organization::query()->firstOrFail();
        $this->assertTrue($org->active, 'active must not be settable from input');
        $this->assertFalse($org->is_test, 'is_test must not be settable from input');
        $this->assertArrayNotHasKey('sector', $org->getAttributes());
    }
}
