<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\RentInvoiceGenerated;
use App\Notifications\TenantPaymentSubmitted;
use App\Services\RentInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase1ChannelsTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private Property $property;

    private Unit $unit;

    private User $tenantUser;

    private Tenant $tenant;

    private RentInvoice $invoice;

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
            'name' => 'Channels Property',
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

        $this->tenantUser = User::factory()->create();
        $this->tenantUser->assignRole('tenant');

        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $this->tenantUser->id,
            'first_name' => 'Amina',
            'last_name' => 'One',
            'phone' => '+25377000001',
            'email' => $this->tenantUser->email,
            'status' => 'active',
        ]);

        $lease = Lease::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 50000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);

        $this->invoice = app(RentInvoiceService::class)->createInvoice([
            'landlord_id' => $this->landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $this->tenant->id,
            'invoice_month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'amount' => 50000,
            'status' => 'unpaid',
        ]);
    }

    // ── Channel selection ────────────────────────────────

    public function test_via_is_mail_only_when_channels_are_not_configured(): void
    {
        config(['services.whatsapp.token' => null, 'services.twilio.sid' => null]);

        $notification = new RentInvoiceGenerated($this->invoice);

        $this->assertSame(['mail'], $notification->via($this->tenantUser));
    }

    public function test_via_adds_whatsapp_and_sms_when_configured_and_tenant_has_phone(): void
    {
        config([
            'services.whatsapp.token' => 'test-token',
            'services.whatsapp.phone_number_id' => '123',
            'services.twilio.sid' => 'AC123',
            'services.twilio.token' => 'secret',
            'services.twilio.from' => '+15550001',
        ]);

        $notification = new RentInvoiceGenerated($this->invoice);
        $channels = $notification->via($this->tenantUser);

        $this->assertContains('mail', $channels);
        $this->assertContains(WhatsAppChannel::class, $channels);
        $this->assertContains(SmsChannel::class, $channels);
    }

    public function test_via_stays_mail_only_when_tenant_has_no_phone(): void
    {
        config([
            'services.whatsapp.token' => 'test-token',
            'services.whatsapp.phone_number_id' => '123',
        ]);

        $this->tenant->update(['phone' => '']);

        $notification = new RentInvoiceGenerated($this->invoice->fresh());

        $this->assertSame(['mail'], $notification->via($this->tenantUser));
    }

    public function test_channels_no_op_without_config_and_make_no_http_calls(): void
    {
        Http::fake();

        config(['services.whatsapp.token' => null, 'services.twilio.sid' => null]);

        $notification = new RentInvoiceGenerated($this->invoice);

        (new WhatsAppChannel)->send($this->tenantUser, $notification);
        (new SmsChannel)->send($this->tenantUser, $notification);

        Http::assertNothingSent();
    }

    public function test_whatsapp_channel_posts_to_the_cloud_api_when_configured(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => []], 200)]);

        config([
            'services.whatsapp.token' => 'test-token',
            'services.whatsapp.phone_number_id' => '123',
        ]);

        (new WhatsAppChannel)->send($this->tenantUser, new RentInvoiceGenerated($this->invoice));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'graph.facebook.com/v19.0/123/messages')
                && $request['to'] === '25377000001'
                && str_contains($request['text']['body'], $this->invoice->payment_reference);
        });
    }

    // ── Payment webhook ──────────────────────────────────

    private function webhookHeaders(): array
    {
        return ['X-Kirada-Signature' => 'test-secret'];
    }

    public function test_webhook_with_bad_secret_is_forbidden(): void
    {
        config(['payments.gateways.manual.secret' => 'test-secret']);

        $this->postJson(route('webhooks.payments', 'manual'), [
            'payment_reference' => $this->invoice->payment_reference,
            'amount' => 50000,
        ], ['X-Kirada-Signature' => 'wrong'])->assertForbidden();

        $this->assertSame(0, RentPayment::count());
    }

    public function test_webhook_without_configured_secret_is_forbidden(): void
    {
        config(['payments.gateways.manual.secret' => null]);

        $this->postJson(route('webhooks.payments', 'manual'), [
            'payment_reference' => $this->invoice->payment_reference,
            'amount' => 50000,
        ], ['X-Kirada-Signature' => ''])->assertForbidden();
    }

    public function test_webhook_for_unknown_gateway_is_not_found(): void
    {
        $this->postJson(route('webhooks.payments', 'nonexistent'), [])->assertNotFound();
    }

    public function test_webhook_for_disabled_gateway_is_not_found(): void
    {
        $this->postJson(route('webhooks.payments', 'waafi'), [])->assertNotFound();
    }

    public function test_webhook_with_unknown_reference_is_not_found(): void
    {
        config(['payments.gateways.manual.secret' => 'test-secret']);

        $this->postJson(route('webhooks.payments', 'manual'), [
            'payment_reference' => 'KIR-DOESNOTX',
            'amount' => 50000,
        ], $this->webhookHeaders())->assertNotFound();
    }

    public function test_valid_webhook_creates_a_pending_payment_and_notifies_the_landlord(): void
    {
        Notification::fake();

        config(['payments.gateways.manual.secret' => 'test-secret']);

        $response = $this->postJson(route('webhooks.payments', 'manual'), [
            'payment_reference' => $this->invoice->payment_reference,
            'amount' => 50000,
            'method' => 'mobile_money',
            'reference_number' => 'WAAFI-987654',
        ], $this->webhookHeaders());

        $response->assertCreated();

        $payment = RentPayment::where('rent_invoice_id', $this->invoice->id)->firstOrFail();

        $this->assertSame('pending', $payment->status);
        $this->assertSame('mobile_money', $payment->method);
        $this->assertSame('WAAFI-987654', $payment->reference_number);
        $this->assertSame($this->landlord->id, $payment->landlord_id);

        // The invoice is untouched until the landlord confirms.
        $this->assertSame('unpaid', $this->invoice->fresh()->status);

        Notification::assertSentTo($this->landlord, TenantPaymentSubmitted::class);
    }

    public function test_webhook_overpayment_is_rejected(): void
    {
        config(['payments.gateways.manual.secret' => 'test-secret']);

        $this->postJson(route('webhooks.payments', 'manual'), [
            'payment_reference' => $this->invoice->payment_reference,
            'amount' => 999999,
        ], $this->webhookHeaders())->assertUnprocessable();

        $this->assertSame(0, RentPayment::count());
    }
}
