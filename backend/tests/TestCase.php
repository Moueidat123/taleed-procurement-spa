<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Hard stop: tests may only ever touch a dedicated *_test schema.
        $database = (string) DB::connection()->getDatabaseName();
        if (! str_ends_with($database, '_test')) {
            throw new RuntimeException("Refusing to run tests against database [{$database}].");
        }

        // Isolated Statamic users directory (never the real CMS admin file).
        $users = (string) config('statamic.stache.stores.users.directory');
        if (! str_contains($users, 'framework/testing')) {
            throw new RuntimeException("Refusing to run tests against Statamic users directory [{$users}].");
        }
        File::ensureDirectoryExists($users);
        File::cleanDirectory($users);
    }
}
