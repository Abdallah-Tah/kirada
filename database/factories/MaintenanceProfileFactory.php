<?php

namespace Database\Factories;

use App\Models\MaintenanceProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceProfile>
 */
class MaintenanceProfileFactory extends Factory
{
    protected $model = MaintenanceProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'bio' => fake()->paragraph(),
            'trades' => fake()->randomElements(MaintenanceProfile::TRADES, 2),
            'service_areas' => [fake()->city()],
            'phone' => '+2537'.fake()->numerify('#######'),
            'years_experience' => fake()->numberBetween(1, 25),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => ['verified_at' => now()]);
    }
}
