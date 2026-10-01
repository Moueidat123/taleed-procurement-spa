<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\AuditEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Create (or promote) the first Super Admin so a fresh deployment has exactly
 * one account able to administer the platform (decisions.md D-10). Safe to run
 * repeatedly: an existing user with this email is promoted and reactivated
 * rather than duplicated, and the action is always audited.
 */
class BootstrapAdministrator extends Command
{
    protected $signature = 'procurement:admin:bootstrap
        {email}
        {--name=Super Admin}
        {--password= : Defaults to a random value that is printed once}';

    protected $description = 'Create or promote the first Super Admin for this deployment';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('A valid email address is required.');

            return self::INVALID;
        }

        $generated = $this->option('password') === null;
        $password = $generated ? bin2hex(random_bytes(10)).'Aa1' : (string) $this->option('password');

        $result = DB::transaction(function () use ($email, $password, $generated): array {
            $existing = AppUser::query()->where('email', $email)->lockForUpdate()->first();

            if ($existing !== null) {
                $existing->role = 'admin';
                $existing->active = true;
                $existing->can_export = true;
                if ($generated === false) {
                    $existing->password = $password;
                }
                if ($existing->email_verified_at === null) {
                    $existing->email_verified_at = now();
                }
                $existing->save();
                $action = 'admin.promoted';
                $user = $existing;
            } else {
                $user = new AppUser(['name' => (string) $this->option('name'), 'email' => $email]);
                $user->password = $password;
                $user->role = 'admin';
                $user->can_export = true;
                $user->active = true;
                $user->email_verified_at = now();
                $user->save();
                $action = 'admin.bootstrapped';
            }

            AuditEvent::record(
                action: $action,
                actorId: $user->id,
                targetType: 'app_user',
                targetId: $user->id,
                metadata: ['email' => $email],
            );

            return ['user' => $user, 'action' => $action];
        });

        /** @var AppUser $user */
        $user = $result['user'];
        $verb = $result['action'] === 'admin.promoted' ? 'Promoted existing user to' : 'Created';
        $this->info("{$verb} Super Admin {$email} ({$user->id}).");

        if ($generated) {
            $this->line("Password (shown once): {$password}");
        }

        $this->warn('Super Admins must enable two-factor authentication before performing staff actions.');

        return self::SUCCESS;
    }
}
