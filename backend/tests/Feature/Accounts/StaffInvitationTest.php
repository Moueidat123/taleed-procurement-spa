<?php

namespace Tests\Feature\Accounts;

use App\Models\AppUser;
use App\Models\StaffInvitation;
use App\Notifications\StaffInvitationNotification;
use App\Services\StaffInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Phase 2A — staff invitations (decisions.md D-10): 72h, single-use, hashed
 * token; Super Admin issues, anyone with the token accepts. MySQL-backed.
 */
class StaffInvitationTest extends TestCase
{
    use RefreshDatabase;

    private const ISSUE = '/api/procurement/v1/staff/invitations';

    private const ACCEPT = '/api/procurement/v1/auth/invitations/accept';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
    }

    public function test_super_admin_issues_an_invitation_and_token_is_emailed_not_stored(): void
    {
        Notification::fake();
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();

        $this->actingAs($admin)->postJson(self::ISSUE, [
            'email' => 'new.analyst@taleed.test', 'role' => 'analyst', 'canExport' => true,
        ])->assertCreated()->assertJsonPath('data.role', 'analyst');

        $invitation = StaffInvitation::query()->firstOrFail();
        $this->assertSame('new.analyst@taleed.test', $invitation->normalized_email);
        $this->assertSame(64, strlen($invitation->token_hash));
        $this->assertTrue($invitation->can_export);
        Notification::assertSentOnDemand(StaffInvitationNotification::class);
        $this->assertDatabaseHas('audit_events', ['action' => 'staff.invited']);
    }

    public function test_a_champion_cannot_issue(): void
    {
        Notification::fake();
        $champion = AppUser::factory()->role('champion')->create();

        $this->actingAs($champion)->postJson(self::ISSUE, [
            'email' => 'x@taleed.test', 'role' => 'analyst',
        ])->assertStatus(403);

        $this->assertDatabaseCount('staff_invitations', 0);
    }

    public function test_an_analyst_cannot_issue(): void
    {
        Notification::fake();
        $analyst = AppUser::factory()->role('analyst')->create();

        $this->actingAs($analyst)->postJson(self::ISSUE, [
            'email' => 'x@taleed.test', 'role' => 'analyst',
        ])->assertStatus(403);

        $this->assertDatabaseCount('staff_invitations', 0);
    }

    public function test_a_guest_cannot_issue(): void
    {
        $this->postJson(self::ISSUE, ['email' => 'guest@taleed.test', 'role' => 'analyst'])
            ->assertUnauthorized();
    }

    public function test_accepting_a_valid_token_creates_a_signed_in_staff_user(): void
    {
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $token = $this->issueToken($admin, 'analyst@taleed.test', 'analyst', true);

        $this->postJson(self::ACCEPT, [
            'token' => $token,
            'name' => 'New Analyst',
            'password' => 'Str0ngPassphrase!',
            'password_confirmation' => 'Str0ngPassphrase!',
        ])->assertCreated()->assertJsonPath('data.role', 'analyst');

        $user = AppUser::query()->where('email', 'analyst@taleed.test')->firstOrFail();
        $this->assertSame('analyst', $user->role);
        $this->assertTrue($user->active);
        $this->assertTrue($user->can_export);
        $this->assertNotNull($user->email_verified_at, 'accepting proves inbox control');
        $this->assertNotNull(StaffInvitation::query()->firstOrFail()->accepted_at);
        $this->assertDatabaseHas('audit_events', ['action' => 'staff.invitation_accepted']);
    }

    public function test_role_and_email_come_from_the_invitation_not_from_input(): void
    {
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $token = $this->issueToken($admin, 'analyst@taleed.test', 'analyst', false);

        $this->postJson(self::ACCEPT, [
            'token' => $token,
            'name' => 'New Analyst',
            'password' => 'Str0ngPassphrase!',
            'password_confirmation' => 'Str0ngPassphrase!',
            'email' => 'attacker@evil.test',
            'role' => 'admin',
            'can_export' => true,
        ])->assertCreated();

        $user = AppUser::query()->where('email', 'analyst@taleed.test')->firstOrFail();
        $this->assertSame('analyst', $user->role, 'role must come from the invitation');
        $this->assertFalse($user->can_export, 'export must come from the invitation');
        $this->assertNull(AppUser::query()->where('email', 'attacker@evil.test')->first());
    }

    public function test_a_token_cannot_be_used_twice(): void
    {
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $token = $this->issueToken($admin, 'analyst@taleed.test', 'analyst', false);

        $accept = fn () => $this->postJson(self::ACCEPT, [
            'token' => $token, 'name' => 'A B',
            'password' => 'Str0ngPassphrase!', 'password_confirmation' => 'Str0ngPassphrase!',
        ]);

        $accept()->assertCreated();
        $accept()->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
        $this->assertSame(1, AppUser::query()->where('email', 'analyst@taleed.test')->count());
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $token = $this->issueToken($admin, 'analyst@taleed.test', 'analyst', false);

        // Age the invitation past its 72h lifetime.
        StaffInvitation::query()->update(['expires_at' => Carbon::now()->subHour()]);

        $this->postJson(self::ACCEPT, [
            'token' => $token, 'name' => 'A B',
            'password' => 'Str0ngPassphrase!', 'password_confirmation' => 'Str0ngPassphrase!',
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

        $this->assertNull(AppUser::query()->where('email', 'analyst@taleed.test')->first());
    }

    public function test_a_random_token_is_rejected_without_enumeration(): void
    {
        $this->postJson(self::ACCEPT, [
            'token' => str_repeat('a', 64), 'name' => 'A B',
            'password' => 'Str0ngPassphrase!', 'password_confirmation' => 'Str0ngPassphrase!',
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_duplicate_pending_invitation_is_refused(): void
    {
        Notification::fake();
        $admin = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();

        $this->actingAs($admin)->postJson(self::ISSUE, [
            'email' => 'dup@taleed.test', 'role' => 'analyst',
        ])->assertCreated();

        $this->actingAs($admin)->postJson(self::ISSUE, [
            'email' => 'dup@taleed.test', 'role' => 'analyst',
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

        $this->assertSame(1, StaffInvitation::query()->count());
    }

    /** Issue an invitation through the service and return the plaintext token. */
    private function issueToken(AppUser $admin, string $email, string $role, bool $canExport): string
    {
        $token = null;
        Notification::fake();
        app(StaffInvitationService::class)->issue($admin, $email, $role, $canExport);
        Notification::assertSentOnDemand(
            StaffInvitationNotification::class,
            function ($notification) use (&$token) {
                $token = (fn () => $this->token)->call($notification);

                return true;
            }
        );

        return (string) $token;
    }
}
