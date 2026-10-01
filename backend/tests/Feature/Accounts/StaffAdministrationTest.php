<?php

namespace Tests\Feature\Accounts;

use App\Models\AppUser;
use App\Services\StaffAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Phase 2A — staff people & access administration (decisions.md D-10, D-17).
 *
 * Super Admins review all staff and toggle active / canExport, behind the TOTP
 * gate. Two invariants hold: a Super Admin never edits their own access, and the
 * last active Super Admin can never be deactivated.
 */
class StaffAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private const USERS = '/api/procurement/v1/staff/users';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
    }

    /** A Super Admin who has completed TOTP enrolment. */
    private function admin(): AppUser
    {
        return AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
    }

    private function accessUrl(AppUser $user): string
    {
        return self::USERS.'/'.$user->id.'/access';
    }

    public function test_super_admin_lists_staff_and_excludes_champions(): void
    {
        $admin = $this->admin();
        $analyst = AppUser::factory()->role('analyst')->create();
        AppUser::factory()->role('champion')->create();

        $response = $this->actingAs($admin)->getJson(self::USERS)->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($admin->id));
        $this->assertTrue($ids->contains($analyst->id));
        $this->assertCount(2, $ids, 'champions are organization users, not staff');
    }

    public function test_staff_without_confirmed_two_factor_are_blocked_by_the_gate(): void
    {
        $admin = AppUser::factory()->role('admin')->create(); // no 2FA

        $this->actingAs($admin)->getJson(self::USERS)->assertStatus(403);
    }

    public function test_a_champion_cannot_list_staff(): void
    {
        $champion = AppUser::factory()->role('champion')->create();

        $this->actingAs($champion)->getJson(self::USERS)->assertStatus(403);
    }

    public function test_a_guest_cannot_list_staff(): void
    {
        $this->getJson(self::USERS)->assertUnauthorized();
    }

    public function test_super_admin_toggles_analyst_export(): void
    {
        $admin = $this->admin();
        $analyst = AppUser::factory()->role('analyst')->create(['can_export' => false]);

        $this->actingAs($admin)->patchJson($this->accessUrl($analyst), [
            'canExport' => true,
        ])->assertOk()->assertJsonPath('data.canExport', true);

        $this->assertTrue($analyst->fresh()->can_export);
        $this->assertDatabaseHas('audit_events', ['action' => 'staff.access_changed']);
    }

    public function test_super_admin_deactivates_an_analyst(): void
    {
        $admin = $this->admin();
        $analyst = AppUser::factory()->role('analyst')->create();

        $this->actingAs($admin)->patchJson($this->accessUrl($analyst), [
            'active' => false,
        ])->assertOk()->assertJsonPath('data.active', false);

        $this->assertFalse($analyst->fresh()->active);
    }

    public function test_super_admin_cannot_change_their_own_access(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patchJson($this->accessUrl($admin), [
            'active' => false,
        ])->assertStatus(422);

        $this->assertTrue($admin->fresh()->active);
    }

    public function test_the_last_active_super_admin_cannot_be_deactivated(): void
    {
        // Defence-in-depth: through the API the actor is always an active admin,
        // so a target can never be the *only* active admin. We exercise the guard
        // directly on the service to prove the 409 invariant holds.
        $lastAdmin = AppUser::factory()->role('admin')->create(['active' => true]);
        $actor = AppUser::factory()->role('admin')->create(['active' => false]);

        try {
            app(StaffAccessService::class)->update($actor, $lastAdmin, ['active' => false]);
            $this->fail('Expected the last-admin guard to throw.');
        } catch (ValidationException $e) {
            $this->assertSame(409, $e->status);
            $this->assertTrue($lastAdmin->fresh()->active);
        }
    }

    public function test_a_champion_cannot_change_staff_access(): void
    {
        $champion = AppUser::factory()->role('champion')->create();
        $analyst = AppUser::factory()->role('analyst')->create();

        $this->actingAs($champion)->patchJson($this->accessUrl($analyst), [
            'active' => false,
        ])->assertStatus(403);

        $this->assertTrue($analyst->fresh()->active);
    }
}
