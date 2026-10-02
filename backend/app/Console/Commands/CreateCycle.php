<?php

namespace App\Console\Commands;

use App\Models\AssessmentCycle;
use App\Models\AuditEvent;
use App\Models\FrameworkVersion;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Throwable;

/**
 * Phase 2B — open an annual assessment cycle pinned to a published framework.
 * Dates are business dates in Asia/Riyadh: the cycle opens at 00:00 on --opens
 * and closes at 23:59:59 on --closes. Only one cycle may be open (DB-enforced).
 */
class CreateCycle extends Command
{
    public const TIMEZONE = 'Asia/Riyadh';

    protected $signature = 'procurement:cycle:create
        {--title= : Cycle title, e.g. "2026 annual assessment"}
        {--framework= : Published framework semantic version, e.g. 1.0.0}
        {--opens= : Opening date YYYY-MM-DD (Asia/Riyadh)}
        {--closes= : Closing date YYYY-MM-DD (Asia/Riyadh)}';

    protected $description = 'Open an assessment cycle pinned to a published framework (audited)';

    public function handle(): int
    {
        $title = trim((string) $this->option('title'));
        if ($title === '' || ! $this->option('framework') || ! $this->option('opens') || ! $this->option('closes')) {
            $this->error('--title, --framework, --opens and --closes are required.');

            return self::INVALID;
        }

        try {
            $opens = CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->option('opens'), self::TIMEZONE)->startOfDay();
            $closes = CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->option('closes'), self::TIMEZONE)->endOfDay();
        } catch (Throwable) {
            $this->error('Dates must be YYYY-MM-DD.');

            return self::INVALID;
        }
        if ($closes <= $opens) {
            $this->error('--closes must be after --opens.');

            return self::INVALID;
        }

        $framework = FrameworkVersion::query()->where('semantic_version', (string) $this->option('framework'))->first();
        if ($framework === null || ! $framework->isPublished()) {
            $this->error('The framework version must exist and be published.');

            return self::FAILURE;
        }

        try {
            $cycle = AssessmentCycle::create([
                'framework_version_id' => $framework->id,
                'title' => $title,
                'status' => 'open',
                'opens_at' => $opens->utc(),
                'closes_at' => $closes->utc(),
                'business_timezone' => self::TIMEZONE,
            ]);
        } catch (UniqueConstraintViolationException) {
            $this->error('Another cycle is already open. Close it first.');

            return self::FAILURE;
        }

        AuditEvent::record(action: 'cycle.created', targetType: 'assessment_cycle', targetId: $cycle->id,
            metadata: ['title' => $title, 'framework' => $framework->semantic_version,
                'opens' => (string) $this->option('opens'), 'closes' => (string) $this->option('closes'), 'timezone' => self::TIMEZONE]);

        $this->info("Opened cycle \"{$title}\" ({$cycle->id}) on framework {$framework->semantic_version}.");

        return self::SUCCESS;
    }
}
