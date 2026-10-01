<?php

namespace Database\Factories;

use App\Models\AppUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Synthetic users for tests and explicit local commands only.
 *
 * @extends Factory<AppUser>
 */
class AppUserFactory extends Factory
{
    protected $model = AppUser::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => Str::lower(fake()->unique()->userName()).'@example.test',
            'job_title' => 'Procurement lead',
            'password' => 'correct horse battery staple',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (AppUser $user) {
            $user->role ??= 'champion';
            $user->active ??= true;
            $user->can_export ??= false;
            $user->email_verified_at ??= now();
        });
    }

    public function role(string $role): static
    {
        return $this->afterMaking(fn (AppUser $user) => $user->role = $role);
    }

    public function inactive(): static
    {
        return $this->afterMaking(fn (AppUser $user) => $user->active = false);
    }

    public function unverified(): static
    {
        // Runs after the configure() default, so it wins over the ??= now().
        return $this->afterMaking(fn (AppUser $user) => $user->email_verified_at = null);
    }
}
