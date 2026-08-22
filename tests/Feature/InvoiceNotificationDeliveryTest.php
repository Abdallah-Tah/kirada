<?php

namespace Tests\Feature;

use App\Jobs\DeliverInvoiceChannel;
use App\Livewire\RentInvoices\DeliveryHistory;
use App\Models\Currency;
use App\Models\LandlordNotificationSetting;
use App\Models\Lease;
use App\Models\NotificationDelivery;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RentInvoiceGenerated;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\InvoiceDeliveryService;
use App\Services\InvoicePdfFactory;
use App\Services\NotificationChannelResolver;
use App\Services\RentInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private User $tenantUser;

    private Tenant $tenant;

    private Lease $lease;

    private RentInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');
        $this->tenantUser = User::factory()->create();
        $this->tenantUser->assignRole('tenant');

        $currency = Currency::create([
            'code' => 'DJF',
            'name' => 'Djiboutian Franc',
            'symbol' => 'Fdj',
            'decimals' => 0,
            'is_active' => true,
        ]);
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'currency_id' => $currency->id,
            'name' => 'Delivery Property',
            'type' => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);
        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'A1',
            'type' => 'apartment',
            'monthly_rent' => 120000,
            'status' => 'occupied',
        ]);
        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $this->tenantUser->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud',
            'phone' => '+25377123456',
            'email' => $this->tenantUser->email,
            'status' => 'active',
        ]);
        $this->lease = Lease::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => now()->subMonth(),
            'monthly_rent' => 120000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);
        $this->invoice = app(RentInvoiceService::class)->createInvoice([
            'lease_id' => $this->lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $this->tenant->id,
            'invoice_month' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(4),
            'amount' => 120000,
            'status' => 'draft',
        ]);
    }

    public function test_global_channels_are_inherited_and_lease_overrides_take_precedence(): void
    {
        LandlordNotificationSetting::create([
            'landlord_id' => $this->landlord->id,
            'invoice_channels' => ['email', 'whatsapp'],
            'reminder_channels' => ['whatsapp'],
            'auto_send_invoices' => true,
            'attach_pdf_to_email' => true,
        ]);

        $resolver = app(NotificationChannelResolver::class);

        $this->assertSame(['email', 'whatsapp'], $resolver->channels($this->invoice));
        $this->assertSame(['whatsapp'], $resolver->channels($this->invoice, true));

        $this->lease->update([
            'invoice_delivery_channels' => ['email'],
            'reminder_delivery_channels' => ['email', 'whatsapp'],
            'auto_send_invoice_override' => false,
        ]);

        $invoice = $this->invoice->fresh(['lease', 'landlord.notificationSetting']);
        $this->assertSame(['email'], $resolver->channels($invoice));
        $this->assertSame(['email', 'whatsapp'], $resolver->channels($invoice, true));
        $this->assertFalse($resolver->autoSendEnabled($invoice));
    }

    public function test_whatsapp_requires_consent_and_uses_the_tenant_recipient(): void
    {
        $resolver = app(NotificationChannelResolver::class);
        $this->assertNull($resolver->whatsAppRecipient($this->invoice));

        $this->tenant->update([
            'whatsapp_consented_at' => now(),
            'whatsapp_consent_source' => 'tenant_settings',
        ]);

        $this->assertSame(
            '+25377123456',
            $resolver->whatsAppRecipient($this->invoice->fresh(['tenant'])),
        );

        $this->tenant->update(['whatsapp_consent_revoked_at' => now()]);
        $this->assertNull($resolver->whatsAppRecipient($this->invoice->fresh(['tenant'])));
    }

    public function test_dispatch_creates_independent_idempotent_channel_records(): void
    {
        Queue::fake();
        $this->tenant->update(['whatsapp_consented_at' => now()]);

        // A scheduler that runs twice in the same window must not send the
        // reminder twice.
        $service = app(InvoiceDeliveryService::class);
        $first = $service->dispatch($this->invoice, 'overdue_7', $this->landlord, ['email', 'whatsapp']);
        $second = $service->dispatch($this->invoice, 'overdue_7', $this->landlord, ['email', 'whatsapp']);

        $this->assertCount(2, $first);
        $this->assertCount(2, $second);
        $this->assertSame(2, NotificationDelivery::count());
        Queue::assertPushed(DeliverInvoiceChannel::class, 2);
        $this->assertStringEndsWith('3456', NotificationDelivery::where('channel', 'whatsapp')->value('recipient_masked'));
    }

    public function test_manual_send_queues_a_fresh_delivery_after_an_earlier_one_succeeded(): void
    {
        Queue::fake();
        $this->tenant->update(['whatsapp_consented_at' => now()]);
        $service = app(InvoiceDeliveryService::class);

        $first = $service->dispatch(
            $this->invoice,
            InvoiceDeliveryService::MANUAL_EVENT,
            $this->landlord,
            ['email', 'whatsapp'],
        );
        // The tenant received and read the first send a week ago; the landlord
        // is sending again because they were asked to, not by accident.
        NotificationDelivery::query()->update(['status' => NotificationDelivery::STATUS_READ]);

        $second = $service->dispatch(
            $this->invoice->fresh(),
            InvoiceDeliveryService::MANUAL_EVENT,
            $this->landlord,
            ['email', 'whatsapp'],
        );

        $this->assertSame(4, NotificationDelivery::count());
        $this->assertEmpty($first->pluck('id')->intersect($second->pluck('id')));
        $this->assertSame(
            ['queued', 'queued'],
            $second->pluck('status')->all(),
        );
        Queue::assertPushed(DeliverInvoiceChannel::class, 4);
    }

    public function test_missing_whatsapp_consent_is_logged_as_skipped_without_a_job(): void
    {
        Queue::fake();

        app(InvoiceDeliveryService::class)->dispatch(
            $this->invoice,
            'manual_send',
            $this->landlord,
            ['whatsapp'],
        );

        $this->assertDatabaseHas('notification_deliveries', [
            'channel' => 'whatsapp',
            'status' => 'skipped',
            'error_code' => 'recipient_unavailable',
        ]);
        Queue::assertNothingPushed();
    }

    public function test_skipped_delivery_can_be_requeued_after_tenant_grants_consent(): void
    {
        Queue::fake();
        $service = app(InvoiceDeliveryService::class);

        $service->dispatch($this->invoice, 'overdue_7', $this->landlord, ['whatsapp']);
        $this->tenant->update(['whatsapp_consented_at' => now()]);
        $service->dispatch($this->invoice->fresh(), 'overdue_7', $this->landlord, ['whatsapp']);

        $this->assertSame(1, NotificationDelivery::count());
        $this->assertDatabaseHas('notification_deliveries', [
            'channel' => 'whatsapp',
            'status' => 'queued',
            'error_code' => null,
        ]);
        Queue::assertPushed(DeliverInvoiceChannel::class, 1);
    }

    public function test_whatsapp_job_sends_a_signed_bwa_document_template_request(): void
    {
        $this->tenant->update(['whatsapp_consented_at' => now()]);
        config([
            'services.bwa.api_url' => 'https://bwa.test',
            'services.bwa.app' => 'kirada',
            'services.bwa.request_signing_secret' => 'request-secret',
            'services.bwa.invoice_template' => 'kirada_rent_invoice',
            'services.bwa.template_language' => 'fr',
        ]);
        Http::fake([
            'bwa.test/api/v1/whatsapp/messages' => Http::response([
                'data' => ['message_id' => 'bwa-message-123'],
            ], 202),
        ]);

        $delivery = NotificationDelivery::create([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $this->invoice->id,
            'tenant_id' => $this->tenant->id,
            'event' => 'manual_send',
            'channel' => 'whatsapp',
            'status' => 'queued',
            'idempotency_key' => hash('sha256', 'whatsapp-test'),
            'queued_at' => now(),
        ]);

        (new DeliverInvoiceChannel($delivery->id))->handle(
            app(InvoicePdfFactory::class),
            app(NotificationChannelResolver::class),
            app(BwaMessagingApi::class),
        );

        $delivery->refresh();
        $this->assertSame('sent', $delivery->status);
        $this->assertNull($delivery->provider_media_id);
        $this->assertSame('bwa-message-123', $delivery->provider_message_id);
        $this->assertSame('sent', $this->invoice->fresh()->status);

        Http::assertSent(function ($request) use ($delivery) {
            $timestamp = $request->header('X-BWA-Timestamp')[0] ?? '';
            $requestId = $request->header('X-BWA-Request-ID')[0] ?? '';
            $rawBody = $request->body();
            $expected = 'sha256='.hash_hmac(
                'sha256',
                implode("\n", [
                    'POST',
                    '/api/v1/whatsapp/messages',
                    $timestamp,
                    $requestId,
                    hash('sha256', $rawBody),
                ]),
                'request-secret',
            );

            return $request->url() === 'https://bwa.test/api/v1/whatsapp/messages'
                && ($request->header('X-BWA-App')[0] ?? null) === 'kirada'
                && ($request->header('Idempotency-Key')[0] ?? null) === $delivery->idempotency_key
                && ($request->header('X-BWA-Signature')[0] ?? null) === $expected
                && $request['recipient'] === '+25377123456'
                && $request['product'] === 'kirada'
                && $request['template']['language'] === 'fr'
                && $request['idempotency_key'] === $delivery->idempotency_key
                && data_get($request, 'template.components.0.parameters.0.document.content_type') === 'application/pdf'
                && base64_decode(
                    (string) data_get($request, 'template.components.0.parameters.0.document.content_base64'),
                    true,
                ) !== false;
        });
    }

    public function test_email_job_attaches_the_invoice_pdf(): void
    {
        Notification::fake();
        $delivery = NotificationDelivery::create([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $this->invoice->id,
            'tenant_id' => $this->tenant->id,
            'event' => 'manual_send',
            'channel' => 'email',
            'status' => 'queued',
            'idempotency_key' => hash('sha256', 'email-test'),
            'queued_at' => now(),
        ]);

        (new DeliverInvoiceChannel($delivery->id))->handle(
            app(InvoicePdfFactory::class),
            app(NotificationChannelResolver::class),
            app(BwaMessagingApi::class),
        );

        Notification::assertSentOnDemand(
            RentInvoiceGenerated::class,
            fn ($notification) => str_starts_with((string) $notification->pdf, '%PDF-'),
        );
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_authorized_user_can_queue_manual_send_and_tenant_cannot(): void
    {
        Queue::fake();

        Livewire::actingAs($this->landlord)
            ->test(DeliveryHistory::class, ['rentInvoice' => $this->invoice])
            ->call('send')
            ->assertHasNoErrors();

        Queue::assertPushed(DeliverInvoiceChannel::class);

        Livewire::actingAs($this->tenantUser)
            ->test(DeliveryHistory::class, ['rentInvoice' => $this->invoice])
            ->assertForbidden();
    }
}
