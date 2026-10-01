<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Synthetic organizations for tests only. Sets the normalized columns the way
 * OrganizationService does, since they are guarded against mass assignment.
 *
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'display_name' => $name,
            'normalized_name' => Organization::normalize($name),
            'country_code' => 'SA',
            'size_band' => '51-250',
            'registration_id' => null,
            'normalized_registration_id' => null,
            'active' => true,
            'is_test' => false,
        ];
    }

    public function withRegistration(string $registrationId): static
    {
        return $this->state(fn () => [
            'registration_id' => $registrationId,
            'normalized_registration_id' => Organization::normalize($registrationId),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
