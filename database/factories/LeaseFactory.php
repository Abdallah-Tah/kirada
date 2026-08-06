<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    public function definition(): array
    {
        $landlord = User::factory()->create();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $unit = Unit::factory()->create(['property_id' => $property->id]);
        $tenant = Tenant::factory()->create(['landlord_id' => $landlord->id]);

        return [
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
            'monthly_rent' => fake()->randomFloat(2, 200, 3000),
            'security_deposit' => fake()->randomFloat(2, 200, 3000),
            'payment_due_day' => fake()->numberBetween(1, 28),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
