<?php

namespace App\Console\Commands;

use App\Domain\Framework\FrameworkImporter;
use App\Models\FrameworkVersion;
use Illuminate\Console\Command;
use Throwable;

/** Phase 2B — publish a draft framework version (approval reference required, audited). */
class PublishFramework extends Command
{
    protected $signature = 'procurement:framework:publish
        {version : Semantic version, e.g. 1.0.0}
        {--approval-ref= : Content approval reference (required, D-19)}';

    protected $description = 'Publish a draft framework version; it becomes immutable';

    public function handle(FrameworkImporter $importer): int
    {
        $ref = trim((string) $this->option('approval-ref'));
        if ($ref === '') {
            $this->error('--approval-ref is required.');

            return self::INVALID;
        }

        $version = FrameworkVersion::query()->where('semantic_version', (string) $this->argument('version'))->first();
        if ($version === null) {
            $this->error('Framework version not found.');

            return self::FAILURE;
        }

        try {
            $importer->publish($version, $ref);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Published framework {$version->semantic_version} (approval: {$ref}).");

        return self::SUCCESS;
    }
}
