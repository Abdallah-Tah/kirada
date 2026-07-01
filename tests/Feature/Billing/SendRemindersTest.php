<?php

namespace Tests\Feature\Billing;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RentReminderDue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendRemindersTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;
    private User $tenantUser;
    private Lease $lease;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $this->tenantUser = User::factory()->create();
        $this->tenantUser->assignRole('tenant');

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
            'user_id'     => $this->tenantUser->id,
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'phone'       => '+25377000001',
            'email'       => $this->tenantUser->email,
            'status'      => 'active',
        ]);

        $this->lease = Lease::create([
            'landlord_id'                        => $this->landlord->id,
            'property_id'                        => $property->id,
            'unit_id'                            => $unit->id,
            'tenant_id'                          => $this->tenant->id,
            'start_date'                         => now()->subMonth()->toDateString(),
            'monthly_rent'                       => 50000,
            'payment_due_day'                    => 10,
            'status'                             => 'active',
            'auto_generate_invoices'             => true,
            'invoice_generation_days_before_due' => 7,
            'grace_period_days'                  => 5,
            'late_fee_type'                      => 'none',
            'late_fee_frequency'                 => 'once',
            'reminder_schedule'                  => ['before_due_7', 'before_due_3', 'before_due_1', 'overdue_1'],
        ]);
    }

    private function makeInvoice(string $dueDate, string $status = 'unpaid'): RentInvoice
    {
        return RentInvoice::create([
            'landlord_id'      => $this->landlord->id,
            'lease_id'         => $this->lease->id,
            'property_id'      => $this->lease->property_id,
            'unit_id'          => $this->lease->unit_id,
            'tenant_id'        => $this->tenant->id,
            'invoice_number'   => 'INV-TEST-' . rand(1000, 9999),
            'invoice_month'    => now()->startOfMonth()->toDateString(),
            'due_date'         => $dueDate,
            'amount'           => 50000,
            'status'           => $status,
            'is_auto_generated'=> true,
        ]);
    }

    public function test_sends_before_due_7_reminder_on_correct_day(): void
    {
        Notification::fake();

        // Due date is July 10, so "before_due_7" fires on July 3
        Carbon::setTestNow('2026-07-03');
        $invoice = $this->makeInvoice('2026-07-10');

        $this->artisan('kirada:send-rent-reminders')->assertSuccessful();

        Notification::assertSentTo($this->tenantUser, RentReminderDue::class,
            fn ($n) => $n->reminderKey === 'before_due_7'
        );

        // Check it's recorded so it won't fire again
        $this->assertTrue($invoice->fresh()->reminderWasSent('before_due_7'));

        Carbon::setTestNow();
    }

    public function test_does_not_send_reminder_twice(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-07-03');
        $invoice = $this->makeInvoice('2026-07-10');

        $this->artisan('kirada:send-rent-reminders');
        $this->artisan('kirada:send-rent-reminders'); // second run same day

        Notification::assertSentToTimes($this->tenantUser, RentReminderDue::class, 1);

        Carbon::setTestNow();
    }

    public function test_skips_paid_invoices(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-07-03');
        $this->makeInvoice('2026-07-10', 'paid');

        $this->artisan('kirada:send-rent-reminders')->assertSuccessful();

        Notification::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_sends_overdue_reminder_on_correct_day(): void
    {
        Notification::fake();

        // Due July 10, overdue_1 fires on July 11
        Carbon::setTestNow('2026-07-11');
        $invoice = $this->makeInvoice('2026-07-10', 'overdue');

        $this->artisan('kirada:send-rent-reminders')->assertSuccessful();

        Notification::assertSentTo($this->tenantUser, RentReminderDue::class,
            fn ($n) => $n->reminderKey === 'overdue_1'
        );

        Carbon::setTestNow();
    }
}
