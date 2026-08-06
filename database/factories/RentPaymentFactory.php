<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentPayment>
 */
class RentPaymentFactory extends Factory
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
        $invoice = RentInvoice::factory()->create([
            'landlord_id' => $landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
        ]);

        return [
            'landlord_id' => $landlord->id,
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'payment_number' => 'PAY-'.fake()->unique()->numberBetween(10000, 99999),
            'payment_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 100, 3000),
            'method' => 'cash',
            'status' => 'pending',
            'reference_number' => fake()->optional()->uuid(),
            'proof_path' => null,
            'notes' => fake()->optional()->sentence(),
            'confirmed_at' => null,
            'confirmed_by' => null,
        ];
    }
}
