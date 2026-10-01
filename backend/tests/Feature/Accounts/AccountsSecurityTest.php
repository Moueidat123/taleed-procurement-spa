<?php

namespace Tests\Feature\Accounts;

use App\Models\AppUser;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Facades\User as CmsUser;
use Tests\TestCase;

/**
 * Phase 2A — security cross-checks (PLAN-CONTRACT §2 "Phase 2A" test list):
 * staff MFA enforcement, organization pause/enable, forged IDs, mass-assignment
 * refusal, invitation-accept throttling, identical emails across providers and
 * the audited bootstrap command.
 */
class AccountsSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
    }

    private function admin(): AppUser
    {
        return AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
    }

    /** @return array<string, array{string}> */
    public static function staffRoles(): array
    {
        return ['analyst' => ['analyst'], 'super admin' => ['admin']];
    }

    #[DataProvider('staffRoles')]
    public function test_every_staff_route_requires_confirmed_two_factor(string $role): void
    {
        $user = AppUser::factory()->role($role)->create();
        $org = Organization::factory()->create();

        $this->actingAs($user)->getJson(self::API.'/staff/users')
            ->assertStatus(403)->assertJsonPath('error.code', 'two_factor_required');
        $this->actingAs($user)->postJson(self::API.'/staff/invitations', ['email' => 'x@example.test', 'role' => 'analyst'])
            ->assertStatus(403)->assertJsonPath('error.code', 'two_factor_required');
        $this->actingAs($user)->patchJson(self::API.'/staff/organizations/'.$org->id.'/access', ['active' => false])
            ->assertStatus(403)->assertJsonPath('error.code', 'two_factor_required');
        $this->assertTrue($org->fresh()->active);
    }

    public function test_champions_are_not_subject_to_the_mfa_gate_but_are_still_forbidden(): void
    {
        $champion = AppUser::factory()->role('champion')->create();

        $response = $this->actingAs($champion)->getJson(self::API.'/staff/users')->assertStatus(403);
        $this->assertNotSame('two_factor_required', $response->json('error.code'));
    }

    public function test_super_admin_pauses_and_enables_an_organization_with_audit(): void
    {
        $admin = $this->admin();
        $org = Organization::factory()->create();
        $url = self::API.'/staff/organizations/'.$org->id.'/access';

        $this->actingAs($admin)->patchJson($url, ['active' => false])->assertOk();
        $this->assertFalse($org->fresh()->active);
        $this->assertDatabaseHas('audit_events', ['action' => 'organization.paused', 'target_id' => $org->id]);

        $this->actingAs($admin)->patchJson($url, ['active' => true])->assertOk();
        $this->assertTrue($org->fresh()->active);
        $this->assertDatabaseHas('audit_events', ['action' => 'organization.enabled', 'target_id' => $org->id]);
    }

    public function test_an_analyst_with_two_factor_cannot_pause_an_organization(): void
    {
        $analyst = AppUser::factory()->role('analyst')->twoFactorConfirmed()->create();
        $org = Organization::factory()->create();

        $this->actingAs($analyst)->patchJson(self::API.'/staff/organizations/'.$org->id.'/access', ['active' => false])
            ->assertStatus(403);
        $this->assertTrue($org->fresh()->active);
    }

    public function test_forged_user_and_organization_ids_return_404(): void
    {
        $admin = $this->admin();
        $forged = '01JZZZZZZZZZZZZZZZZZZZZZZZ';

        $this->actingAs($admin)->patchJson(self::API.'/staff/users/'.$forged.'/access', ['active' => false])->assertNotFound();
        $this->actingAs($admin)->patchJson(self::API.'/staff/organizations/'.$forged.'/access', ['active' => false])->assertNotFound();
    }

    public function test_access_change_ignores_role_email_and_password_in_input(): void
    {
        $admin = $this->admin();
        $analyst = AppUser::factory()->role('analyst')->create();
        $hash = $analyst->password;

        $this->actingAs($admin)->patchJson(self::API.'/staff/users/'.$analyst->id.'/access', [
            'canExport' => true,
            'role' => 'admin',
            'email' => 'attacker@example.test',
            'password' => 'new-password-123456',
            'organization_id' => Organization::factory()->create()->id,
        ])->assertOk();

        $fresh = $analyst->fresh();
        $this->assertSame('analyst', $fresh->role);
        $this->assertSame($analyst->email, $fresh->email);
        $this->assertSame($hash, $fresh->password);
        $this->assertNull($fresh->organization_id);
        $this->assertTrue($fresh->can_export);
    }

    public function test_invitation_acceptance_is_rate_limited(): void
    {
        $statuses = [];
        for ($i = 0; $i < 7; $i++) {
            $statuses[] = $this->postJson(self::API.'/auth/invitations/accept', [
                'token' => str_repeat('a', 64), 'name' => 'X', 'password' => 'a-long-password-123',
                'password_confirmation' => 'a-long-password-123',
            ])->status();
        }

        $this->assertContains(429, $statuses);
    }

    public function test_identical_email_in_app_and_cms_are_independent_principals(): void
    {
        $email = 'shared@example.test';
        $cms = CmsUser::make()->email($email)->data(['name' => 'CMS'])->password('Cms-Password-12345');
        $cms->save();
        $app = AppUser::factory()->role('champion')->create(['email' => $email]);

        $this->postJson(self::API.'/auth/login', ['email' => $email, 'password' => 'Cms-Password-12345'])
            ->assertStatus(422);
        $this->assertGuest('web');

        $this->postJson(self::API.'/auth/login', ['email' => $email, 'password' => 'correct horse battery staple'])
            ->assertSuccessful();
        $this->assertAuthenticatedAs($app, 'web');
        $this->assertTrue(Hash::check('Cms-Password-12345', (string) CmsUser::findByEmail($email)->password()));

        $cms->delete();
    }

    public function test_bootstrap_command_creates_one_audited_super_admin_and_is_idempotent(): void
    {
        $this->artisan('procurement:admin:bootstrap', ['email' => 'Owner@Example.test', '--password' => 'Bootstrap-Pass-1234'])
            ->assertSuccessful();
        $this->artisan('procurement:admin:bootstrap', ['email' => 'owner@example.test'])
            ->assertSuccessful();

        $admins = AppUser::query()->where('email', 'owner@example.test')->get();
        $this->assertCount(1, $admins);
        $this->assertSame('admin', $admins[0]->role);
        $this->assertTrue($admins[0]->active);
        $this->assertNull($admins[0]->two_factor_confirmed_at, 'MFA must still be enrolled by the person');
        $this->assertTrue(Hash::check('Bootstrap-Pass-1234', $admins[0]->password), 'second run without --password keeps it');
        $this->assertDatabaseHas('audit_events', ['action' => 'admin.bootstrapped', 'target_id' => $admins[0]->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'admin.promoted', 'target_id' => $admins[0]->id]);
    }

    public function test_bootstrap_command_rejects_an_invalid_email(): void
    {
        $this->artisan('procurement:admin:bootstrap', ['email' => 'not-an-email'])->assertFailed();
        $this->assertSame(0, AppUser::query()->count());
    }
}
