<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'landlord_id' => User::factory(),
            'name' => fake()->company().' Building',
            'type' => 'residential',
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'region' => fake()->optional()->state(),
            'postal_code' => fake()->optional()->postcode(),
            'country' => 'Djibouti',
            'description' => fake()->optional()->paragraph(),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'is_active' => true,
        ];
    }
}
