<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\AssessmentCycle;
use App\Models\FrameworkVersion;
use App\Services\AssessmentService;
use App\Services\OrganizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

/**
 * Local only: synthetic dataset for the browser journeys (tests/e2e-local).
 * Every run uses a unique suffix, so runs never collide. Prints JSON (secrets
 * included) for the test process only; never runs outside local/testing.
 */
class SeedLocalE2e extends Command
{
    protected $signature = 'procurement:dev:seed-e2e {--run= : Unique suffix} {--password= : Password for all synthetic users}';

    protected $description = 'Local only: seed a synthetic run-specific dataset for browser tests';

    public function handle(AssessmentService $assessments, OrganizationService $orgs): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Refused: this command only runs when APP_ENV is local or testing.');

            return self::FAILURE;
        }
        $run = strtolower((string) ($this->option('run') ?: Str::lower(Str::random(6))));
        $password = (string) $this->option('password');
        if (! preg_match('/^[a-z0-9]{3,12}$/', $run) || strlen($password) < 12) {
            $this->error('Use --run=[a-z0-9]{3,12} and a --password of at least 12 characters.');

            return self::FAILURE;
        }

        if (! FrameworkVersion::query()->where('semantic_version', '1.0.0')->where('status', 'published')->exists()) {
            if (! FrameworkVersion::query()->where('semantic_version', '1.0.0')->exists()) {
                $this->callSilently('procurement:framework:import');
            }
            $this->callSilently('procurement:framework:publish', ['version' => '1.0.0', '--approval-ref' => 'LOCAL-E2E-ONLY']);
        }
        if (! AssessmentCycle::query()->where('status', 'open')->get()->contains(fn (AssessmentCycle $c) => $c->isOpen())) {
            $this->callSilently('procurement:cycle:create', ['--title' => 'Local test cycle', '--framework' => '1.0.0',
                '--opens' => now('Asia/Riyadh')->subDay()->toDateString(), '--closes' => now('Asia/Riyadh')->addMonths(3)->toDateString()]);
        }

        $user = function (string $key, string $role, bool $export = false) use ($run, $password): AppUser {
            $u = new AppUser(['name' => 'E2E '.ucfirst($key), 'email' => "e2e-{$key}-{$run}@example.test", 'job_title' => 'Synthetic tester']);
            $u->password = $password;
            $u->role = $role;
            $u->can_export = $export;
            $u->active = true;
            $u->email_verified_at = now();
            $u->save();

            return $u;
        };
        $champion = function (string $key, string $name) use ($user, $orgs, $run): AppUser {
            $u = $user($key, 'champion');
            $orgs->upsertForChampion($u, ['displayName' => "{$name} {$run}", 'countryCode' => 'SA', 'sizeBand' => '11-50',
                'registrationId' => strtoupper("E2E-{$key}-{$run}"), 'authorityConfirmed' => true]);

            return $u->refresh();
        };
        $answers = function (array $yesPerDomain): array {
            $a = [];
            foreach ($yesPerDomain as $d => $yes) {
                foreach (range(1, 10) as $q) {
                    $a[($d + 1).".{$q}"] = $q <= $yes ? 'yes' : 'no';
                }
            }

            return $a;
        };
        $submitted = function (AppUser $u, array $yes) use ($assessments, $answers): string {
            $r = $assessments->start($u);
            $r = $assessments->saveAnswers($u, $r->id, $r->lock_version, $answers($yes));

            return $assessments->submit($u, $r->id, $r->lock_version, true, (string) Str::uuid())->id;
        };

        $alpha = $champion('alpha', 'E2E Alpha');
        $bravo = $champion('bravo', 'E2E Bravo');
        $charlie = $champion('charlie', 'E2E Charlie');
        $fresh = $champion('fresh', 'E2E Fresh');
        $alphaRev = $submitted($alpha, [10, 9, 8, 10]);
        $bravoRev = $submitted($bravo, [6, 4, 8, 6]);
        $charlieRev = $submitted($charlie, [2, 3, 1, 4]);

        $g = new Google2FA;
        $staff = [];
        foreach (['analyst' => ['analyst', false], 'exporter' => ['analyst', true], 'admin' => ['admin', false]] as $key => [$role, $export]) {
            $u = $user($key, $role, $export);
            $secret = $g->generateSecretKey();
            $u->forceFill([
                'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
                'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode([Str::random(10).'-'.Str::random(10)])),
                'two_factor_confirmed_at' => now(),
            ])->save();
            $staff[$key] = ['email' => $u->email, 'totpSecret' => $secret];
        }

        $this->line((string) json_encode([
            'run' => $run,
            'champions' => [
                'alpha' => ['email' => $alpha->email, 'company' => "E2E Alpha {$run}", 'orgId' => $alpha->organization_id, 'revisionId' => $alphaRev],
                'bravo' => ['email' => $bravo->email, 'company' => "E2E Bravo {$run}", 'orgId' => $bravo->organization_id, 'revisionId' => $bravoRev],
                'charlie' => ['email' => $charlie->email, 'company' => "E2E Charlie {$run}", 'orgId' => $charlie->organization_id, 'revisionId' => $charlieRev],
                'fresh' => ['email' => $fresh->email, 'company' => "E2E Fresh {$run}", 'orgId' => $fresh->organization_id],
            ],
            'staff' => $staff,
        ], JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
