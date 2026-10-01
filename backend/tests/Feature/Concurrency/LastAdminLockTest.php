<?php

namespace Tests\Feature\Concurrency;

use App\Models\AppUser;
use App\Services\StaffAccessService;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Phase 2A — last-admin race (D-10). Uses committed rows and a second MySQL
 * connection so row locks are real (never SQLite, never a wrapping transaction).
 *
 * Proves that a deactivation must wait for the admin-row lock held by another
 * in-flight transaction, so two concurrent "deactivate the other admin" requests
 * serialize and the second one re-checks the committed state.
 */
class LastAdminLockTest extends TestCase
{
    use DatabaseTruncation;

    private function second(): Connection
    {
        $default = (string) config('database.default');
        config(['database.connections.race' => config("database.connections.{$default}")]);

        return DB::connection('race');
    }

    public function test_deactivation_waits_for_a_concurrent_admin_row_lock(): void
    {
        $a = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $b = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();

        $other = $this->second();
        $other->beginTransaction();
        $other->table('app_users')->where('role', 'admin')->where('active', true)->lockForUpdate()->get();

        DB::statement('SET SESSION innodb_lock_wait_timeout = 1');

        try {
            app(StaffAccessService::class)->update($a, $b, ['active' => false]);
            $this->fail('Deactivation must block on the concurrent lock.');
        } catch (QueryException $e) {
            $this->assertSame(1205, $e->errorInfo[1] ?? null, 'expected lock wait timeout');
        } finally {
            $other->rollBack();
        }

        $this->assertTrue($b->fresh()->active, 'nothing changed while blocked');
    }

    public function test_after_the_first_commit_the_second_crossing_deactivation_is_refused(): void
    {
        $a = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $b = AppUser::factory()->role('admin')->twoFactorConfirmed()->create();
        $service = app(StaffAccessService::class);

        // Request 1 (A deactivates B) wins the lock and commits first.
        $service->update($a, $b, ['active' => false]);

        // Request 2 (B deactivates A) was authorised with a stale B, then runs.
        try {
            $service->update($b, $a, ['active' => false]);
            $this->fail('The last active Super Admin must not be deactivated.');
        } catch (ValidationException $e) {
            $this->assertSame(409, $e->status);
        }

        $this->assertSame(1, AppUser::query()->where('role', 'admin')->where('active', true)->count());
    }
}
