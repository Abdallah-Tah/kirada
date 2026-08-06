<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'landlord_id' => User::factory(),
            'user_id' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'national_id' => fake()->optional()->uuid(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
