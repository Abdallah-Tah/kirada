<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'building_id' => null,
            'unit_number' => fake()->bothify('##??'),
            'floor' => (string) fake()->numberBetween(1, 10),
            'type' => 'apartment',
            'area_sqm' => fake()->optional()->randomFloat(2, 30, 200),
            'bedrooms' => fake()->numberBetween(0, 5),
            'bathrooms' => fake()->numberBetween(0, 3),
            'monthly_rent' => fake()->randomFloat(2, 200, 3000),
            'security_deposit' => fake()->randomFloat(2, 200, 3000),
            'status' => 'vacant',
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
