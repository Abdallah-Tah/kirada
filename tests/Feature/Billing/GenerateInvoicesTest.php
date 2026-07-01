<?php

namespace Tests\Feature\Billing;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GenerateInvoicesTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;
    private Property $property;
    private Unit $unit;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $this->property = Property::create([
            'landlord_id'    => $this->landlord->id,
            'name'           => 'Test Property',
            'type'           => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city'           => 'Djibouti',
            'is_active'      => true,
        ]);

        $this->unit = Unit::create([
            'property_id' => $this->property->id,
            'unit_number' => 'A1',
            'type'        => 'apartment',
            'monthly_rent'=> 50000,
            'status'      => 'occupied',
        ]);

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id'     => $tenantUser->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'phone'       => '+25377000001',
            'email'       => $tenantUser->email,
            'status'      => 'active',
        ]);
    }

    private function makeLease(array $overrides = []): Lease
    {
        return Lease::create(array_merge([
            'landlord_id'                        => $this->landlord->id,
            'property_id'                        => $this->property->id,
            'unit_id'                            => $this->unit->id,
            'tenant_id'                          => $this->tenant->id,
            'start_date'                         => now()->subMonth()->toDateString(),
            'monthly_rent'                       => 50000,
            'payment_due_day'                    => 5,
            'status'                             => 'active',
            'auto_generate_invoices'             => true,
            'invoice_generation_days_before_due' => 7,
            'grace_period_days'                  => 5,
            'late_fee_type'                      => 'none',
            'late_fee_frequency'                 => 'once',
        ], $overrides));
    }

    public function test_generates_invoice_when_within_generation_window(): void
    {
        // payment_due_day=5, generate 7 days before → window opens on 28th of previous month
        Carbon::setTestNow('2026-07-01'); // 4 days before July 5

        $lease   = $this->makeLease(['payment_due_day' => 5]);
        $service = app(RentInvoiceService::class);

        $invoice = $service->generateForLease($lease);

        $this->assertNotNull($invoice);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertEquals('2026-07-01', $invoice->invoice_month->format('Y-m-d'));
        $this->assertEquals('2026-07-05', $invoice->due_date->format('Y-m-d'));
        $this->assertTrue($invoice->is_auto_generated);

        Carbon::setTestNow();
    }

    public function test_skips_generation_when_too_early(): void
    {
        // 15 days before July 5 → outside 7-day window
        Carbon::setTestNow('2026-06-20');

        $lease   = $this->makeLease(['payment_due_day' => 5]);
        $service = app(RentInvoiceService::class);

        $invoice = $service->generateForLease($lease);

        $this->assertNull($invoice);
        $this->assertEquals(0, RentInvoice::count());

        Carbon::setTestNow();
    }

    public function test_prevents_duplicate_invoices(): void
    {
        Carbon::setTestNow('2026-07-01');

        $lease   = $this->makeLease(['payment_due_day' => 5]);
        $service = app(RentInvoiceService::class);

        $first  = $service->generateForLease($lease);
        $second = $service->generateForLease($lease);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(1, RentInvoice::count());

        Carbon::setTestNow();
    }

    public function test_skips_inactive_leases(): void
    {
        Carbon::setTestNow('2026-07-01');

        $lease   = $this->makeLease(['status' => 'ended', 'payment_due_day' => 5]);
        $service = app(RentInvoiceService::class);

        $invoice = $service->generateForLease($lease);

        $this->assertNull($invoice);
        $this->assertEquals(0, RentInvoice::count());

        Carbon::setTestNow();
    }

    public function test_skips_leases_with_auto_generate_disabled(): void
    {
        Carbon::setTestNow('2026-07-01');

        $lease   = $this->makeLease(['payment_due_day' => 5, 'auto_generate_invoices' => false]);
        $service = app(RentInvoiceService::class);

        $invoice = $service->generateForLease($lease);

        $this->assertNull($invoice);
        $this->assertEquals(0, RentInvoice::count());

        Carbon::setTestNow();
    }

    public function test_artisan_command_generates_invoices(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-01');

        $this->makeLease(['payment_due_day' => 5]);

        $this->artisan('kirada:generate-rent-invoices')->assertSuccessful();

        $this->assertEquals(1, RentInvoice::count());

        Carbon::setTestNow();
    }
}
