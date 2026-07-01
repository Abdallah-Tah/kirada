<?php

namespace Tests\Feature\Billing;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentInvoiceLineItem;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\RentInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplyLateFeesTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;
    private Lease $lease;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $property = Property::create([
            'landlord_id'    => $this->landlord->id,
            'name'           => 'Test Property',
            'type'           => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city'           => 'Djibouti',
            'is_active'      => true,
        ]);

        $unit = Unit::create([
            'property_id'  => $property->id,
            'unit_number'  => 'A1',
            'type'         => 'apartment',
            'monthly_rent' => 50000,
            'status'       => 'occupied',
        ]);

        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id'     => $tenantUser->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'phone'       => '+25377000001',
            'email'       => $tenantUser->email,
            'status'      => 'active',
        ]);

        $this->lease = Lease::create([
            'landlord_id'                        => $this->landlord->id,
            'property_id'                        => $property->id,
            'unit_id'                            => $unit->id,
            'tenant_id'                          => $this->tenant->id,
            'start_date'                         => now()->subMonths(2)->toDateString(),
            'monthly_rent'                       => 50000,
            'payment_due_day'                    => 1,
            'status'                             => 'active',
            'auto_generate_invoices'             => true,
            'invoice_generation_days_before_due' => 7,
            'grace_period_days'                  => 5,
            'late_fee_type'                      => 'fixed',
            'late_fee_amount'                    => 2000,
            'late_fee_frequency'                 => 'once',
        ]);
    }

    private function makeOverdueInvoice(string $dueDate): RentInvoice
    {
        return RentInvoice::create([
            'landlord_id'   => $this->landlord->id,
            'lease_id'      => $this->lease->id,
            'property_id'   => $this->lease->property_id,
            'unit_id'       => $this->lease->unit_id,
            'tenant_id'     => $this->tenant->id,
            'invoice_number'=> 'INV-TEST-' . rand(1000, 9999),
            'invoice_month' => '2026-06-01',
            'due_date'      => $dueDate,
            'amount'        => 50000,
            'status'        => 'overdue',
        ]);
    }

    public function test_applies_fixed_late_fee_after_grace_period(): void
    {
        Notification::fake();

        // Due June 1, grace 5 days → grace ends June 6
        // Today is June 10 → past grace
        Carbon::setTestNow('2026-06-10');
        $invoice = $this->makeOverdueInvoice('2026-06-01');

        $service = app(RentInvoiceService::class);
        $result  = $service->applyLateFee($invoice);

        $this->assertTrue($result);
        $this->assertEquals(1, RentInvoiceLineItem::where('rent_invoice_id', $invoice->id)
            ->where('type', 'late_fee')->count());
        $this->assertEquals(2000, RentInvoiceLineItem::first()->amount);
        $this->assertEquals(52000, $invoice->fresh()->totalDue());

        Carbon::setTestNow();
    }

    public function test_respects_grace_period(): void
    {
        Notification::fake();

        // Due June 1, grace 5 days → grace ends June 6
        // Today is June 4 → still within grace
        Carbon::setTestNow('2026-06-04');
        $invoice = $this->makeOverdueInvoice('2026-06-01');

        $service = app(RentInvoiceService::class);
        $result  = $service->applyLateFee($invoice);

        $this->assertFalse($result);
        $this->assertEquals(0, RentInvoiceLineItem::count());

        Carbon::setTestNow();
    }

    public function test_applies_fee_only_once_when_frequency_is_once(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-06-10');
        $invoice = $this->makeOverdueInvoice('2026-06-01');
        $service = app(RentInvoiceService::class);

        $first  = $service->applyLateFee($invoice);
        $second = $service->applyLateFee($invoice);

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertEquals(1, RentInvoiceLineItem::count());

        Carbon::setTestNow();
    }

    public function test_applies_weekly_fee_again_after_7_days(): void
    {
        Notification::fake();

        $this->lease->update(['late_fee_frequency' => 'weekly']);

        Carbon::setTestNow('2026-06-10');
        $invoice = $this->makeOverdueInvoice('2026-06-01');
        $service = app(RentInvoiceService::class);

        $service->applyLateFee($invoice); // first fee applied June 10

        Carbon::setTestNow('2026-06-17'); // 7 days later
        $service->applyLateFee($invoice->fresh()); // second fee

        $this->assertEquals(2, RentInvoiceLineItem::count());

        Carbon::setTestNow();
    }

    public function test_applies_percentage_late_fee_correctly(): void
    {
        Notification::fake();

        $this->lease->update(['late_fee_type' => 'percentage', 'late_fee_amount' => 5]);

        Carbon::setTestNow('2026-06-10');
        $invoice = $this->makeOverdueInvoice('2026-06-01');
        $service = app(RentInvoiceService::class);
        $service->applyLateFee($invoice);

        // 5% of 50000 = 2500
        $this->assertEquals(2500.0, RentInvoiceLineItem::first()->amount);

        Carbon::setTestNow();
    }

    public function test_skips_paid_invoices(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-06-10');
        $invoice = $this->makeOverdueInvoice('2026-06-01');
        $invoice->update(['status' => 'paid']);

        $this->artisan('kirada:apply-late-fees')->assertSuccessful();
        $this->assertEquals(0, RentInvoiceLineItem::count());

        Carbon::setTestNow();
    }

    public function test_artisan_command_applies_fees(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-06-10');
        $this->makeOverdueInvoice('2026-06-01');

        $this->artisan('kirada:apply-late-fees')->assertSuccessful();

        $this->assertEquals(1, RentInvoiceLineItem::count());

        Carbon::setTestNow();
    }
}
