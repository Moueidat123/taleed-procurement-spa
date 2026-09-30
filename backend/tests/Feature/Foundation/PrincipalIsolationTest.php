<?php

namespace Tests\Feature\Foundation;

use App\Models\AppUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Password;
use Statamic\Contracts\Auth\User;
use Statamic\Facades\User as CmsUser;
use Tests\TestCase;

/**
 * decisions.md D-03 / 05-ACCEPTANCE.md "Foundation and free-Core boundary":
 * application users and the single CMS administrator are separate principals.
 */
class PrincipalIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function cmsAdmin(string $email = 'cms-admin@example.test'): User
    {
        $user = CmsUser::make()->email($email)->data(['name' => 'CMS Admin'])->password('Cms-Admin-Password-1')->makeSuper();
        $user->save();

        return $user;
    }

    public function test_application_user_logs_in_through_the_business_api(): void
    {
        $user = AppUser::factory()->role('analyst')->create(['email' => 'analyst@example.test']);

        $this->postJson('/api/procurement/v1/auth/login', ['email' => 'analyst@example.test', 'password' => 'correct horse battery staple'])
            ->assertOk();

        $this->assertAuthenticatedAs($user, 'web');
        $this->getJson('/api/procurement/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', 'analyst')
            ->assertJsonMissingPath('data.password');
    }

    public function test_inactive_and_wrong_password_fail_identically(): void
    {
        AppUser::factory()->inactive()->create(['email' => 'paused@example.test']);
        AppUser::factory()->create(['email' => 'active@example.test']);

        $inactive = $this->postJson('/api/procurement/v1/auth/login', ['email' => 'paused@example.test', 'password' => 'correct horse battery staple']);
        $wrong = $this->postJson('/api/procurement/v1/auth/login', ['email' => 'active@example.test', 'password' => 'not the password']);

        $inactive->assertStatus(422);
        $wrong->assertStatus(422);
        $this->assertSame($inactive->json('error.fields'), $wrong->json('error.fields'));
        $this->assertGuest('web');
    }

    public function test_cms_only_session_cannot_call_the_business_api(): void
    {
        $this->actingAs($this->cmsAdmin(), 'statamic');

        $this->getJson('/api/procurement/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_cms_administrator_can_reach_the_control_panel(): void
    {
        $this->actingAs($this->cmsAdmin(), 'statamic');

        $response = $this->get('/cp/dashboard');

        $this->assertNotSame(url('/cp/auth/login'), $response->headers->get('Location'));
        $response->assertSuccessful();
    }

    public function test_application_super_admin_has_no_control_panel_access(): void
    {
        $this->actingAs(AppUser::factory()->role('admin')->create(), 'web');

        $this->get('/cp/dashboard')->assertRedirect('/cp/auth/login');
    }

    public function test_password_reset_brokers_never_cross_providers(): void
    {
        $this->cmsAdmin('shared-name@example.test');
        AppUser::factory()->create(['email' => 'app-only@example.test']);

        $this->assertSame(Password::INVALID_USER, Password::broker('app_users')->sendResetLink(['email' => 'shared-name@example.test']));
        $this->assertSame(Password::INVALID_USER, Password::broker('statamic_resets')->sendResetLink(['email' => 'app-only@example.test']));

        $this->assertSame(Password::RESET_LINK_SENT, Password::broker('app_users')->sendResetLink(['email' => 'app-only@example.test']));
        $this->assertSame(Password::RESET_LINK_SENT, Password::broker('statamic_resets')->sendResetLink(['email' => 'shared-name@example.test']));

        $this->assertSame(['app-only@example.test'], DB::table('app_password_reset_tokens')->pluck('email')->all());
        $this->assertSame(['shared-name@example.test'], DB::table('cms_password_reset_tokens')->pluck('email')->all());
    }

    public function test_forgot_password_response_does_not_reveal_accounts(): void
    {
        AppUser::factory()->create(['email' => 'known@example.test']);
        $this->cmsAdmin('cms-only@example.test');

        $known = $this->postJson('/api/procurement/v1/auth/forgot-password', ['email' => 'known@example.test']);
        $unknown = $this->postJson('/api/procurement/v1/auth/forgot-password', ['email' => 'nobody@example.test']);
        $cms = $this->postJson('/api/procurement/v1/auth/forgot-password', ['email' => 'cms-only@example.test']);

        foreach ([$known, $unknown, $cms] as $response) {
            $response->assertStatus(202)->assertExactJson($known->json());
        }
        $this->assertSame(1, DB::table('app_password_reset_tokens')->count());
        $this->assertSame(0, DB::table('cms_password_reset_tokens')->count());
    }

    public function test_application_login_does_not_write_statamic_user_state(): void
    {
        AppUser::factory()->create(['email' => 'champion@example.test']);
        $dir = (string) config('statamic.stache.stores.users.directory');

        $this->postJson('/api/procurement/v1/auth/login', ['email' => 'champion@example.test', 'password' => 'correct horse battery staple'])->assertOk();

        $this->assertSame([], File::files($dir));
    }
}
