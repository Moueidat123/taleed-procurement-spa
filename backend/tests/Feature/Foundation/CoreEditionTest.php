<?php

namespace Tests\Feature\Foundation;

use Statamic\Facades\User as CmsUser;
use Statamic\Statamic;
use Tests\TestCase;

/** Core-only boundary: no Pro, no paid headless APIs, exactly one CMS user. */
class CoreEditionTest extends TestCase
{
    public function test_statamic_runs_as_core_with_headless_apis_disabled(): void
    {
        $this->assertFalse((bool) Statamic::pro());
        $this->assertFalse((bool) config('statamic.api.enabled'));
        $this->assertFalse((bool) config('statamic.graphql.enabled'));
        $this->assertSame('statamic', config('statamic.users.guards.cp'));
        $this->assertSame('web', config('auth.defaults.guard'));
        $this->assertSame(['web'], config('sanctum.guard'));
    }

    public function test_cms_admin_command_creates_exactly_one_user_and_never_enables_pro(): void
    {
        $this->artisan('procurement:cms:create-admin', ['email' => 'first@example.test', '--password' => 'First-Admin-Password-1'])
            ->assertSuccessful();

        $this->artisan('procurement:cms:create-admin', ['email' => 'second@example.test', '--password' => 'Second-Admin-Password-1'])
            ->assertFailed();

        $this->assertSame(1, CmsUser::query()->count());
        $this->assertFalse((bool) Statamic::pro());
    }
}
