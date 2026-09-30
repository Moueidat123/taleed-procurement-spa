<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use Illuminate\Console\Command;

/**
 * LOCAL/TESTING ONLY: create a synthetic, verified application user.
 * Real accounts are created by registration and invitations (Phase 2).
 */
class CreateLocalAppUser extends Command
{
    protected $signature = 'procurement:dev:create-app-user
        {email}
        {--role=champion : champion|analyst|admin}
        {--name=Synthetic User}
        {--password= : Defaults to a random value that is printed once}
        {--can-export}';

    protected $description = 'Local only: create a synthetic verified application user';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Refused: this command only runs when APP_ENV is local or testing.');

            return self::FAILURE;
        }

        $role = (string) $this->option('role');
        if (! in_array($role, AppUser::ROLES, true)) {
            $this->error('Role must be one of: '.implode(', ', AppUser::ROLES));

            return self::FAILURE;
        }

        $email = strtolower((string) $this->argument('email'));
        if (AppUser::query()->where('email', $email)->exists()) {
            $this->error('An application user with this email already exists.');

            return self::FAILURE;
        }

        $password = (string) ($this->option('password') ?? bin2hex(random_bytes(10)).'Aa1');

        $user = new AppUser(['name' => (string) $this->option('name'), 'email' => $email]);
        $user->password = $password;
        $user->role = $role;
        $user->can_export = (bool) $this->option('can-export');
        $user->active = true;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Created synthetic {$role} {$email} ({$user->id}).");
        if ($this->option('password') === null) {
            $this->line("Password (shown once): {$password}");
        }

        return self::SUCCESS;
    }
}
