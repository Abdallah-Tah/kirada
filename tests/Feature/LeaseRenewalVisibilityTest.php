<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeaseRenewalVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_active_leases_are_classified_for_renewal_follow_up(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01'));

        $landlord = User::factory()->create();
        $soon = $this->lease($landlord, Carbon::today()->addDays(15));
        $pipeline = $this->lease($landlord, Carbon::today()->addDays(75));
        $expired = $this->lease($landlord, Carbon::today()->subDays(3));
        $openEnded = $this->lease($landlord, null);
        $ended = $this->lease($landlord, Carbon::today()->addDays(10), 'ended');

        $this->assertSame([$soon->id], Lease::forLandlord($landlord->id)->expiringWithin(30)->pluck('id')->all());
        $this->assertSame([$soon->id, $pipeline->id], Lease::forLandlord($landlord->id)->expiringWithin(90)->pluck('id')->all());
        $this->assertSame([$expired->id], Lease::forLandlord($landlord->id)->expired()->pluck('id')->all());

        $this->assertTrue($soon->isExpiringWithin(30));
        $this->assertSame(15, $soon->days_until_end);
        $this->assertTrue($expired->isExpiredTerm());
        $this->assertSame(-3, $expired->days_until_end);
        $this->assertFalse($openEnded->isExpiringWithin(90));
        $this->assertFalse($ended->isExpiredTerm());
    }

    private function lease(User $landlord, ?Carbon $endDate, string $status = 'active'): Lease
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => fake()->unique()->company(),
            'type' => 'residential',
            'address_line_1' => '1 Main Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'U-'.$property->id,
            'type' => 'apartment',
            'monthly_rent' => 120000,
            'security_deposit' => 120000,
            'status' => $status === 'active' ? 'occupied' : 'vacant',
            'is_active' => true,
        ]);

        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'first_name' => 'Test',
            'last_name' => 'Tenant '.$property->id,
            'phone' => '+253 77 000 00'.$property->id,
            'status' => 'active',
        ]);

        return Lease::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => Carbon::today()->subYear(),
            'end_date' => $endDate,
            'monthly_rent' => 120000,
            'security_deposit' => 120000,
            'payment_due_day' => 5,
            'status' => $status,
        ]);
    }
}
