<?php

namespace Tests\Feature;

use App\Livewire\MaintenanceRequests\Show;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\MaintenanceQuoteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_assigned_provider_can_submit_an_itemized_quote_through_its_full_status_lifecycle(): void
    {
        [$landlord, $provider, $request] = $this->workOrder('Lifecycle');
        $service = app(MaintenanceQuoteService::class);

        $quote = $service->submitQuote($request, $provider, [
            ['description' => 'Replacement part', 'quantity' => 2, 'unit_price' => 40],
            ['description' => 'Labour', 'quantity' => 1.5, 'unit_price' => 30],
        ], 10, 'Valid for seven days.');

        $this->assertSame('125.00', $quote->subtotal);
        $this->assertSame('12.50', $quote->tax_amount);
        $this->assertSame('137.50', $quote->total);
        $this->assertSame('pending', $quote->status);

        $quote = $service->approve($quote);
        $this->assertSame('approved', $quote->status);

        $quote = $service->markInvoiced($quote);
        $this->assertSame('invoiced', $quote->status);
        $this->assertNotNull($quote->invoiced_at);

        $quote = $service->markPaid($quote);
        $this->assertSame('paid', $quote->status);
        $this->assertNotNull($quote->paid_at);
        $this->assertSame($landlord->id, $request->landlord_id);
    }

    public function test_unassigned_provider_cannot_submit_a_quote(): void
    {
        [, , $request] = $this->workOrder('Assigned');
        $otherProvider = User::factory()->create(['email_verified_at' => now()]);
        $otherProvider->assignRole('maintenance');

        $this->expectException(\DomainException::class);

        app(MaintenanceQuoteService::class)->submitQuote($request, $otherProvider, [
            ['description' => 'Unauthorized work', 'quantity' => 1, 'unit_price' => 50],
        ]);
    }

    public function test_landlord_cannot_approve_a_quote_through_an_unrelated_work_order(): void
    {
        [$firstLandlord, , $firstRequest] = $this->workOrder('First');
        [, $secondProvider, $secondRequest] = $this->workOrder('Second');

        $foreignQuote = app(MaintenanceQuoteService::class)->submitQuote($secondRequest, $secondProvider, [
            ['description' => 'Foreign quote', 'quantity' => 1, 'unit_price' => 75],
        ]);

        try {
            Livewire::actingAs($firstLandlord)
                ->test(Show::class, ['maintenanceRequest' => $firstRequest])
                ->call('approveQuote', $foreignQuote->id);

            $this->fail('A quote from another work order must not be actionable.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }

        $this->assertSame('pending', $foreignQuote->fresh()->status);
    }

    /**
     * @return array{User, User, MaintenanceRequest}
     */
    private function workOrder(string $suffix): array
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $provider = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');
        $provider->assignRole('maintenance');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => "{$suffix} Property",
            'type' => 'residential',
            'address_line_1' => '1 Service Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);

        $request = MaintenanceRequest::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'title' => "{$suffix} repair",
            'description' => 'Maintenance work requiring a quote.',
            'status' => 'in_progress',
            'assigned_to' => $provider->id,
            'reported_by' => $landlord->id,
        ]);

        return [$landlord, $provider, $request];
    }
}
