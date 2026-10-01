<?php

namespace Tests\Feature\Accounts;

use App\Models\AppUser;
use App\Models\EmailVerificationChallenge;
use App\Notifications\VerifyEmailCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Phase 2A — Champion registration and six-digit email verification
 * (decisions.md D-30, D-31). Runs on MySQL via the dedicated *_test schema.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER = '/api/procurement/v1/auth/register';

    protected function setUp(): void
    {
        parent::setUp();
        // The SPA calls the API same-origin; emulate that so Sanctum starts a
        // session (otherwise login/regenerate has no session store).
        $this->withHeader('Origin', (string) config('app.url'));
    }

    /** @return array<string, string|bool> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dana Champion',
            'email' => 'dana@newco.test',
            'job_title' => 'Procurement Lead',
            'password' => 'Str0ngPassphrase!',
            'password_confirmation' => 'Str0ngPassphrase!',
            'consent' => true,
        ], $overrides);
    }

    public function test_champion_can_register_and_receives_a_code(): void
    {
        Notification::fake();

        $response = $this->postJson(self::REGISTER, $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.role', 'champion')
            ->assertJsonPath('data.verified', false)
            ->assertJsonPath('data.orgId', null);

        $user = AppUser::query()->where('email', 'dana@newco.test')->firstOrFail();
        $this->assertSame('champion', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->organization_id);
        $this->assertFalse($user->can_export);

        $this->assertSame(1, EmailVerificationChallenge::query()->where('app_user_id', $user->id)->count());
        Notification::assertSentTo($user, VerifyEmailCode::class);
        $this->assertDatabaseHas('audit_events', ['action' => 'champion.registered', 'actor_id' => $user->id]);
    }

    public function test_registration_never_sets_privileged_fields_from_input(): void
    {
        Notification::fake();

        $this->postJson(self::REGISTER, $this->validPayload([
            'role' => 'admin',
            'can_export' => true,
            'active' => true,
            'organization_id' => '01HZZZZZZZZZZZZZZZZZZZZZZZ',
            'email_verified_at' => now()->toISOString(),
        ]))->assertCreated();

        $user = AppUser::query()->where('email', 'dana@newco.test')->firstOrFail();
        $this->assertSame('champion', $user->role, 'role must not be mass assigned');
        $this->assertFalse($user->can_export, 'can_export must not be mass assigned');
        $this->assertNull($user->organization_id, 'organization must not be mass assigned');
        $this->assertNull($user->email_verified_at, 'verification must not be mass assigned');
    }

    public function test_duplicate_email_is_rejected_without_enumeration(): void
    {
        AppUser::factory()->create(['email' => 'dana@newco.test']);

        $this->postJson(self::REGISTER, $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_weak_password_and_missing_consent_are_rejected(): void
    {
        $this->postJson(self::REGISTER, $this->validPayload([
            'password' => 'short', 'password_confirmation' => 'short',
        ]))->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

        $this->postJson(self::REGISTER, $this->validPayload(['consent' => false]))
            ->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_confirming_the_correct_code_verifies_the_user(): void
    {
        Notification::fake();
        $this->postJson(self::REGISTER, $this->validPayload())->assertCreated();
        $user = AppUser::query()->where('email', 'dana@newco.test')->firstOrFail();

        // Capture the code from the issued notification.
        $code = null;
        Notification::assertSentTo($user, VerifyEmailCode::class, function ($notification) use (&$code) {
            $code = (fn () => $this->code)->call($notification);

            return true;
        });

        $this->actingAs($user)
            ->postJson('/api/procurement/v1/auth/email/verify/confirm', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('data.verified', true);

        $this->assertNotNull($user->refresh()->email_verified_at);
        $this->assertDatabaseHas('audit_events', ['action' => 'email.verified', 'actor_id' => $user->id]);
    }

    public function test_wrong_code_counts_an_attempt_and_locks_after_five(): void
    {
        Notification::fake();
        $this->postJson(self::REGISTER, $this->validPayload())->assertCreated();
        $user = AppUser::query()->where('email', 'dana@newco.test')->firstOrFail();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->postJson('/api/procurement/v1/auth/email/verify/confirm', ['code' => '000000'])
                ->assertStatus(422);
        }

        $challenge = EmailVerificationChallenge::query()->where('app_user_id', $user->id)->firstOrFail();
        $this->assertSame(5, $challenge->attempts);
        $this->assertNull($user->refresh()->email_verified_at);
    }
}
