<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentInvoice>
 */
class RentInvoiceFactory extends Factory
{
    public function definition(): array
    {
        $landlord = User::factory()->create();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $unit = Unit::factory()->create(['property_id' => $property->id]);
        $tenant = Tenant::factory()->create(['landlord_id' => $landlord->id]);
        $lease = Lease::factory()->create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
        ]);

        return [
            'landlord_id' => $landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(10000, 99999),
            'invoice_month' => fake()->date('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 200, 3000),
            'status' => 'unpaid',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
