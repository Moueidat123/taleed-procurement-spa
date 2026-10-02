<?php

namespace Tests\Feature\Assessments;

use App\Models\AppUser;
use App\Notifications\VerifyEmailCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Phase 2B — full journeys through real sessions (no actingAs): a Champion
 * registers, verifies, sets up the company, answers and submits; a Super Admin
 * signs in with password + TOTP and opens a correction the Champion submits.
 */
class EndToEndJourneyTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/procurement/v1';

    private const PASSWORD = 'Str0ngPassphrase!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', (string) config('app.url'));
        $this->artisan('procurement:framework:import')->assertSuccessful();
        $this->artisan('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-TEST'])->assertSuccessful();
        $this->artisan('procurement:cycle:create', ['--title' => '2026', '--framework' => '1.0.0',
            '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonth()->toDateString()])->assertSuccessful();
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

    private function logout(): void
    {
        $this->postJson(self::API.'/auth/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();
    }

    /** Register, verify by emailed code, create the profile, answer and submit. */
    private function championSubmits(): string
    {
        Notification::fake();
        $this->postJson(self::API.'/auth/register', [
            'name' => 'Dana Champion', 'email' => 'dana@newco.test', 'job_title' => 'Procurement Lead',
            'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD, 'consent' => true,
        ])->assertCreated();

        $user = AppUser::query()->where('email', 'dana@newco.test')->firstOrFail();
        $code = null;
        Notification::assertSentTo($user, VerifyEmailCode::class, function (VerifyEmailCode $n) use (&$code) {
            $code = (new ReflectionProperty($n, 'code'))->getValue($n);

            return true;
        });
        $this->postJson(self::API.'/auth/email/verify/confirm', ['code' => $code])->assertSuccessful();

        $this->patchJson(self::API.'/organization', ['displayName' => 'NewCo', 'countryCode' => 'SA',
            'sizeBand' => '51-250', 'authorityConfirmed' => true])->assertSuccessful()
            ->assertJsonPath('data.authorityConfirmed', true);

        $id = $this->postJson(self::API.'/assessments')->assertCreated()->json('data.id');
        $this->patchJson(self::API."/assessments/{$id}/answers", ['expectedVersion' => 0, 'answers' => $this->answers(7)])->assertOk();
        $this->withHeader('Idempotency-Key', 'journey-1')
            ->postJson(self::API."/assessments/{$id}/submit", ['expectedVersion' => 1, 'declaration' => true])
            ->assertOk()->assertJsonPath('data.status', 'submitted');

        $this->getJson(self::API."/assessments/{$id}/result")->assertOk()
            ->assertJsonPath('data.snapshot.result.overall', 70)
            ->assertJsonPath('data.snapshot.result.band', 'advanced')
            ->assertJsonPath('data.snapshot.company.name', 'NewCo');

        return $id;
    }

    public function test_champion_journey_from_registration_to_result(): void
    {
        $id = $this->championSubmits();

        $this->getJson(self::API.'/assessments/history')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id)->assertJsonPath('data.0.effective', true);
    }

    public function test_super_admin_with_totp_opens_a_correction_the_champion_submits(): void
    {
        $original = $this->championSubmits();
        $this->logout();

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();
        $admin = AppUser::factory()->role('admin')->create(['email' => 'owner@example.test']);
        $admin->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['r1'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->postJson(self::API.'/auth/login', ['email' => 'owner@example.test', 'password' => 'correct horse battery staple'])
            ->assertOk()->assertJsonPath('two_factor', true);
        $this->getJson(self::API.'/staff/portfolio')->assertUnauthorized();

        $this->postJson(self::API.'/auth/two-factor-challenge', ['code' => $google2fa->getCurrentOtp($secret)])->assertSuccessful();
        $this->getJson(self::API.'/staff/portfolio')->assertOk()->assertJsonPath('data.submittedCount', 1);

        $draft = $this->postJson(self::API."/staff/assessments/{$original}/corrections",
            ['reason' => 'Respondent reported a data entry error'])->assertCreated()->json('data.id');
        $this->logout();

        $this->postJson(self::API.'/auth/login', ['email' => 'dana@newco.test', 'password' => self::PASSWORD])->assertOk();
        $this->postJson(self::API.'/assessments')->assertOk()->assertJsonPath('data.id', $draft);
        $this->patchJson(self::API."/assessments/{$draft}/answers", ['expectedVersion' => 0, 'answers' => ['1.10' => 'yes']])->assertOk();
        $this->withHeader('Idempotency-Key', 'journey-2')
            ->postJson(self::API."/assessments/{$draft}/submit", ['expectedVersion' => 1, 'declaration' => true])->assertOk();

        $history = collect($this->getJson(self::API.'/assessments/history')->assertOk()->json('data'))->keyBy('id');
        $this->assertFalse($history[$original]['effective']);
        $this->assertTrue($history[$draft]['effective']);
        $this->getJson(self::API."/assessments/{$original}/result")->assertOk()->assertJsonPath('data.snapshot.result.overall', 70);
        $this->getJson(self::API."/assessments/{$draft}/result")->assertOk()->assertJsonPath('data.snapshot.result.yes', 29);
    }
}
