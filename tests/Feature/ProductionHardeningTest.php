<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Lease;
use App\Models\Plan;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\MaintenanceRequestService;
use App\Services\MessagingService;
use App\Services\RentInvoiceService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_invoice_creation_derives_related_entities_from_lease(): void
    {
        [$landlord, $property, $unit, $tenant, $lease] = $this->rentalGraph('A');
        [, , , $otherTenant] = $this->rentalGraph('B');

        $invoice = app(RentInvoiceService::class)->createInvoice([
            'landlord_id' => $landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $otherTenant->id,
            'invoice_month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'amount' => 12345,
            'status' => 'unpaid',
        ]);

        $this->assertSame($landlord->id, $invoice->landlord_id);
        $this->assertSame($property->id, $invoice->property_id);
        $this->assertSame($unit->id, $invoice->unit_id);
        $this->assertSame($tenant->id, $invoice->tenant_id);
    }

    public function test_messaging_service_rejects_cross_landlord_tenant(): void
    {
        [$landlord] = $this->rentalGraph('A');
        [, , , $otherTenant] = $this->rentalGraph('B');

        $this->expectException(\DomainException::class);

        app(MessagingService::class)->startConversation($landlord, [
            'landlord_id' => $landlord->id,
            'tenant_id' => $otherTenant->id,
            'subject' => 'Cross-account attempt',
        ]);
    }

    public function test_maintenance_workers_must_be_approved_for_landlord(): void
    {
        [$landlord, $property, $unit, $tenant] = $this->rentalGraph('A');
        $worker = User::factory()->create();
        $worker->assignRole('maintenance');

        $request = app(MaintenanceRequestService::class)->createRequest([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'title' => 'Scoped maintenance',
            'description' => 'Verify worker scoping.',
            'priority' => 'medium',
        ], $landlord);

        $this->expectException(\DomainException::class);
        app(MaintenanceRequestService::class)->assignRequest($request, $worker->id);
    }

    public function test_approved_maintenance_workers_are_assignable(): void
    {
        [$landlord, $property, $unit, $tenant] = $this->rentalGraph('A');
        $worker = User::factory()->create();
        $worker->assignRole('maintenance');
        $landlord->approvedMaintenanceUsers()->attach($worker->id, ['approved_at' => now()]);

        $request = app(MaintenanceRequestService::class)->createRequest([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'title' => 'Approved maintenance',
            'description' => 'Verify worker approval.',
            'priority' => 'medium',
        ], $landlord);

        $assigned = app(MaintenanceRequestService::class)->assignRequest($request, $worker->id);

        $this->assertSame($worker->id, $assigned->assigned_to);
    }

    public function test_subscription_limits_are_enforced(): void
    {
        [$landlord, $property, $unit, $tenant, $lease] = $this->rentalGraph('A');
        $plan = Plan::create([
            'name' => 'Tiny',
            'slug' => 'tiny',
            'monthly_price' => 1000,
            'max_active_units' => 1,
            'max_active_leases' => 1,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->expectException(\DomainException::class);
        app(SubscriptionService::class)->enforceActiveUnitLimit($landlord);

        $this->assertSame($tenant->id, $lease->tenant_id);
        $this->assertSame($property->id, $unit->property_id);
    }

    public function test_expired_active_subscription_is_not_active(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $plan = Plan::create(['name' => 'Expired', 'slug' => 'expired', 'monthly_price' => 1000, 'is_active' => true]);

        Subscription::create([
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($landlord->fresh()->hasActiveSubscription());
        $this->assertTrue($landlord->fresh()->needsSubscription());
    }

    /**
     * @return array{0: User, 1: Property, 2: Unit, 3: Tenant, 4: Lease}
     */
    private function rentalGraph(string $suffix): array
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        $tenantUser = User::factory()->create(['email_verified_at' => now()]);
        $tenantUser->assignRole('tenant');

        $currency = Currency::firstOrCreate(
            ['code' => 'DJF'],
            ['name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0, 'is_active' => true],
        );

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'currency_id' => $currency->id,
            'name' => "Property {$suffix}",
            'type' => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => "A{$suffix}",
            'type' => 'apartment',
            'monthly_rent' => 50000,
            'status' => 'vacant',
            'is_active' => true,
        ]);

        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'user_id' => $tenantUser->id,
            'first_name' => "Tenant {$suffix}",
            'last_name' => 'User',
            'phone' => '+25377000001',
            'email' => $tenantUser->email,
            'status' => 'active',
        ]);

        $lease = Lease::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 50000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);

        return [$landlord, $property, $unit, $tenant, $lease];
    }
}
