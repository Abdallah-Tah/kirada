<?php

namespace Tests\Feature;

use App\Livewire\RentPayments\Submit;
use App\Models\Currency;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\TenantPaymentSubmitted;
use App\Services\RentInvoiceService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class Phase1MoneyLoopTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private Property $property;

    private Unit $unit;

    private User $tenantUserA;

    private Tenant $tenantA;

    private User $tenantUserB;

    private Tenant $tenantB;

    private RentInvoice $invoiceA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $currency = Currency::create([
            'code' => 'DJF',
            'name' => 'Djiboutian Franc',
            'symbol' => 'Fdj',
            'decimals' => 0,
            'is_active' => true,
        ]);

        $this->property = Property::create([
            'landlord_id' => $this->landlord->id,
            'currency_id' => $currency->id,
            'name' => 'Money Loop Property',
            'type' => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'property_id' => $this->property->id,
            'unit_number' => 'A1',
            'type' => 'apartment',
            'monthly_rent' => 50000,
            'status' => 'occupied',
        ]);

        [$this->tenantUserA, $this->tenantA] = $this->makeTenant('Amina', 'One');
        [$this->tenantUserB, $this->tenantB] = $this->makeTenant('Yusuf', 'Two');

        $this->invoiceA = $this->makeInvoice($this->tenantA, 'INV-202607-0001');
    }

    /** @return array{0: User, 1: Tenant} */
    private function makeTenant(string $first, string $last): array
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $user->id,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => '+2537700000'.random_int(0, 9),
            'email' => $user->email,
            'status' => 'active',
        ]);

        return [$user, $tenant];
    }

    private function makeInvoice(Tenant $tenant, string $number): RentInvoice
    {
        $lease = Lease::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 50000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);

        return app(RentInvoiceService::class)->createInvoice([
            'landlord_id' => $this->landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $tenant->id,
            'invoice_month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'amount' => 50000,
            'status' => 'unpaid',
        ]);
    }

    // ── Tenant "I paid" submission ───────────────────────

    public function test_tenant_can_submit_a_payment_and_landlord_is_notified(): void
    {
        Notification::fake();

        Livewire::actingAs($this->tenantUserA)
            ->test(Submit::class, ['rentInvoice' => $this->invoiceA])
            ->set('amount', '50000')
            ->set('method', 'mobile_money')
            ->set('reference_number', 'WAAFI-123456')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('rent-invoices.index'));

        $payment = RentPayment::where('rent_invoice_id', $this->invoiceA->id)->firstOrFail();

        $this->assertSame('pending', $payment->status);
        $this->assertSame('mobile_money', $payment->method);
        $this->assertSame('WAAFI-123456', $payment->reference_number);
        $this->assertSame($this->landlord->id, $payment->landlord_id);
        $this->assertSame($this->property->currency_id, $payment->currency_id);

        Notification::assertSentTo($this->landlord, TenantPaymentSubmitted::class);
    }

    public function test_another_tenant_cannot_open_the_submit_form(): void
    {
        $this->actingAs($this->tenantUserB)
            ->get(route('rent-payments.submit', $this->invoiceA))
            ->assertForbidden();
    }

    public function test_landlord_cannot_open_the_submit_form(): void
    {
        $this->actingAs($this->landlord)
            ->get(route('rent-payments.submit', $this->invoiceA))
            ->assertForbidden();
    }

    public function test_submit_form_rejects_paid_invoices(): void
    {
        $this->invoiceA->update(['status' => 'paid']);

        $this->actingAs($this->tenantUserA)
            ->get(route('rent-payments.submit', $this->invoiceA))
            ->assertForbidden();
    }

    public function test_submitting_more_than_the_remaining_amount_is_rejected(): void
    {
        Notification::fake();

        Livewire::actingAs($this->tenantUserA)
            ->test(Submit::class, ['rentInvoice' => $this->invoiceA])
            ->set('amount', '60000')
            ->call('save')
            ->assertHasErrors('amount');

        $this->assertSame(0, RentPayment::count());
        Notification::assertNothingSent();
    }

    // ── PDF receipts & invoices ──────────────────────────

    public function test_tenant_can_download_their_invoice_pdf(): void
    {
        $response = $this->actingAs($this->tenantUserA)
            ->get(route('rent-invoices.pdf', $this->invoiceA));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_stranger_tenant_cannot_download_someone_elses_invoice_pdf(): void
    {
        $this->actingAs($this->tenantUserB)
            ->get(route('rent-invoices.pdf', $this->invoiceA))
            ->assertForbidden();
    }

    public function test_confirmed_payment_has_a_downloadable_receipt(): void
    {
        $payment = RentPayment::create([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $this->invoiceA->id,
            'lease_id' => $this->invoiceA->lease_id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $this->tenantA->id,
            'payment_number' => 'PAY-20260703-0001',
            'payment_date' => now()->toDateString(),
            'amount' => 50000,
            'method' => 'mobile_money',
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $this->landlord->id,
        ]);

        $response = $this->actingAs($this->tenantUserA)
            ->get(route('rent-payments.receipt', $payment));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        // The landlord can download it too; a stranger tenant cannot.
        $this->actingAs($this->landlord)
            ->get(route('rent-payments.receipt', $payment))
            ->assertOk();

        $this->actingAs($this->tenantUserB)
            ->get(route('rent-payments.receipt', $payment))
            ->assertForbidden();
    }

    public function test_pending_payment_has_no_receipt(): void
    {
        $payment = RentPayment::create([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $this->invoiceA->id,
            'lease_id' => $this->invoiceA->lease_id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $this->tenantA->id,
            'payment_number' => 'PAY-20260703-0002',
            'payment_date' => now()->toDateString(),
            'amount' => 10000,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        $this->actingAs($this->tenantUserA)
            ->get(route('rent-payments.receipt', $payment))
            ->assertNotFound();
    }

    // ── Payment references & multi-currency ─────────────

    public function test_created_invoices_get_a_unique_payment_reference_and_currency(): void
    {
        $invoiceB = $this->makeInvoice($this->tenantB, 'unused');

        $this->assertMatchesRegularExpression('/^KIR-[A-Z0-9]{8}$/', $this->invoiceA->payment_reference);
        $this->assertMatchesRegularExpression('/^KIR-[A-Z0-9]{8}$/', $invoiceB->payment_reference);
        $this->assertNotSame($this->invoiceA->payment_reference, $invoiceB->payment_reference);

        $this->assertSame($this->property->currency_id, $this->invoiceA->currency_id);
    }

    public function test_legacy_invoices_get_a_reference_backfilled_lazily(): void
    {
        $this->invoiceA->update(['payment_reference' => null]);

        $reference = app(RentInvoiceService::class)->ensurePaymentReference($this->invoiceA->fresh());

        $this->assertMatchesRegularExpression('/^KIR-[A-Z0-9]{8}$/', $reference);
    }

    public function test_money_format_respects_currency_code_and_decimals(): void
    {
        $usd = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimals' => 2,
            'is_active' => true,
        ]);

        $this->assertSame('1,234.50 $', Money::format(1234.5, $usd));
        $this->assertSame('50,000 Fdj', Money::format(50000, Currency::where('code', 'DJF')->first()));
        $this->assertSame('50,000 DJF', Money::format(50000, null));
    }

    public function test_formatted_amount_uses_the_invoice_currency(): void
    {
        $this->assertSame('50,000 Fdj', $this->invoiceA->formatted_amount);
    }
}
