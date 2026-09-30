<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\User;
use Statamic\Statamic;

use function Laravel\Prompts\password;

/**
 * Creates THE single Statamic Core control-panel administrator (decisions.md D-04).
 *
 * Unlike `please make:user`, this never offers to enable Pro and refuses when a
 * CMS user already exists. Application users are never created here.
 */
class CreateCmsAdministrator extends Command
{
    protected $signature = 'procurement:cms:create-admin
        {email : Email of the single CMS administrator}
        {--name=CMS Administrator : Display name}
        {--password= : Local/testing only; production prompts without echo}';

    protected $description = 'Create the single Statamic Core CMS administrator (refuses if one exists)';

    public function handle(): int
    {
        if (Statamic::pro()) {
            $this->error('Statamic Pro is enabled; this project is Core-only. Set STATAMIC_PRO_ENABLED=false.');

            return self::FAILURE;
        }

        if (User::all()->isNotEmpty()) {
            $this->error('A CMS administrator already exists. Statamic Core permits exactly one.');

            return self::FAILURE;
        }

        $email = strtolower((string) $this->argument('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');

            return self::FAILURE;
        }

        $secret = $this->option('password');
        if ($secret !== null && ! app()->environment('local', 'testing')) {
            $this->error('--password is only accepted locally. Omit it to be prompted.');

            return self::FAILURE;
        }
        $secret ??= password(label: 'CMS administrator password', required: true, validate: fn (string $v) => strlen($v) < 12 ? 'Use at least 12 characters.' : null);

        User::make()
            ->email($email)
            ->data(['name' => (string) $this->option('name')])
            ->password($secret)
            ->makeSuper()
            ->save();

        $this->info('CMS administrator created. It has no access to the procurement application.');

        return self::SUCCESS;
    }
}
