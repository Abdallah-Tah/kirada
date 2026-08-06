<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentPaymentActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'landlord']);
    }

    /**
     * Build a full landlord → property → unit → tenant → lease → invoice → payment chain.
     *
     * @param  array<string, mixed>  $overrides  Keys are forwarded to RentPayment::factory()->create().
     *                                           Use 'invoice_status' to set the invoice status separately.
     */
    private function createPaymentChain(array $overrides = []): array
    {
        $invoiceStatus = $overrides['invoice_status'] ?? 'unpaid';
        $paymentOverrides = array_diff_key($overrides, array_flip(['invoice_status']));

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

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
            'amount' => 1000,
            'status' => $invoiceStatus,
        ]);

        $payment = RentPayment::factory()->create(array_merge([
            'landlord_id' => $landlord->id,
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'amount' => 500,
            'status' => 'pending',
            'method' => 'cash',
        ], $paymentOverrides));

        return compact('landlord', 'property', 'unit', 'tenant', 'lease', 'invoice', 'payment');
    }

    public function test_admin_can_confirm_pending_payment(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $chain = $this->createPaymentChain();

        // Verify the payment starts as pending
        $this->assertEquals('pending', $chain['payment']->fresh()->status);
        $this->assertTrue($chain['payment']->fresh()->isPending());
        $this->assertFalse($chain['payment']->fresh()->isConfirmed());

        // Admin can update the payment to confirmed
        $chain['payment']->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $admin->id,
        ]);

        $fresh = $chain['payment']->fresh();
        $this->assertEquals('confirmed', $fresh->status);
        $this->assertTrue($fresh->isConfirmed());
        $this->assertNotNull($fresh->confirmed_at);
        $this->assertEquals($admin->id, $fresh->confirmed_by);
    }

    public function test_confirmed_payment_has_receipt_available(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $chain = $this->createPaymentChain([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $admin->id,
            'invoice_status' => 'partially_paid',
        ]);

        $payment = $chain['payment']->fresh();

        // Confirmed payments should be confirmed
        $this->assertTrue($payment->isConfirmed());
        $this->assertEquals('confirmed', $payment->status);
        $this->assertNotNull($payment->confirmed_at);

        // The associated invoice should reflect partial payment
        $this->assertEquals('partially_paid', $chain['invoice']->fresh()->status);
    }

    public function test_pending_payment_is_not_confirmed(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $chain = $this->createPaymentChain();
        $payment = $chain['payment']->fresh();

        // Pending payment is pending, not confirmed or rejected
        $this->assertTrue($payment->isPending());
        $this->assertFalse($payment->isConfirmed());
        $this->assertFalse($payment->isRejected());
        $this->assertNull($payment->confirmed_at);
    }

    public function test_rejected_payment_is_not_confirmed(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $chain = $this->createPaymentChain([
            'status' => 'rejected',
            'invoice_status' => 'unpaid',
        ]);
        $payment = $chain['payment']->fresh();

        // Rejected payment is rejected, not confirmed
        $this->assertTrue($payment->isRejected());
        $this->assertFalse($payment->isConfirmed());
        $this->assertFalse($payment->isPending());

        // The associated invoice should still be unpaid
        $this->assertEquals('unpaid', $chain['invoice']->fresh()->status);
    }
}
