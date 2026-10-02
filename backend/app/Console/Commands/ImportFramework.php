<?php

namespace App\Console\Commands;

use App\Domain\Framework\FrameworkImporter;
use Illuminate\Console\Command;
use Throwable;

/** Phase 2B — import a framework JSON file as a draft version (audited). */
class ImportFramework extends Command
{
    protected $signature = 'procurement:framework:import
        {file=resources/framework/framework-1.0.0.json : Framework JSON, relative to the backend folder}
        {--source=resources/framework/source-workbook-v01.xlsx : Workbook whose checksum must match}';

    protected $description = 'Import a framework version as a draft';

    public function handle(FrameworkImporter $importer): int
    {
        $file = base_path((string) $this->argument('file'));
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        try {
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            $source = $this->option('source') ? base_path((string) $this->option('source')) : null;
            $version = $importer->import($data, $source);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported framework {$version->semantic_version} as draft ({$version->id}).");
        $this->line("Content checksum: {$version->content_sha256}");

        return self::SUCCESS;
    }
}
