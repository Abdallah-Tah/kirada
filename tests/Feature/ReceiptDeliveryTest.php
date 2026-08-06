<?php

namespace Tests\Feature;

use App\Jobs\DeliverReceiptChannel;
use App\Livewire\RentPayments\Index;
use App\Models\Currency;
use App\Models\Lease;
use App\Models\NotificationDelivery;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RentPaymentReceipt;
use App\Services\BrandedPdfService;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\NotificationChannelResolver;
use App\Services\RentPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_queues_email_and_skips_whatsapp_without_consent(): void
    {
        Queue::fake();
        [$landlord, $payment] = $this->pendingPayment();

        app(RentPaymentService::class)->confirmPayment($payment, $landlord->id);

        $email = NotificationDelivery::where('rent_payment_id', $payment->id)
            ->where('channel', 'email')
            ->firstOrFail();
        $whatsApp = NotificationDelivery::where('rent_payment_id', $payment->id)
            ->where('channel', 'whatsapp')
            ->firstOrFail();

        $this->assertSame('queued', $email->status);
        $this->assertSame('skipped', $whatsApp->status);
        $this->assertSame('Tenant has not opted in to WhatsApp notifications.', $whatsApp->error_message);
        Queue::assertPushed(DeliverReceiptChannel::class, fn ($job) => $job->deliveryId === $email->id);
    }

    public function test_email_job_sends_the_pdf_receipt_and_marks_delivery_sent(): void
    {
        Notification::fake();
        Queue::fake();
        [$landlord, $payment] = $this->pendingPayment();

        $confirmed = app(RentPaymentService::class)->confirmPayment($payment, $landlord->id);
        $delivery = NotificationDelivery::where('rent_payment_id', $payment->id)
            ->where('channel', 'email')
            ->firstOrFail();

        $job = new DeliverReceiptChannel($delivery->id);
        $job->handle(
            app(BrandedPdfService::class),
            app(NotificationChannelResolver::class),
            app(BwaMessagingApi::class),
        );

        Notification::assertSentOnDemand(
            RentPaymentReceipt::class,
            fn ($notification) => $notification->payment->is($confirmed)
                && str_starts_with($notification->pdf, '%PDF-'),
        );
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_landlord_can_manually_resend_a_confirmed_receipt(): void
    {
        Queue::fake();
        [$landlord, $payment] = $this->pendingPayment();
        $confirmed = app(RentPaymentService::class)->confirmPayment($payment, $landlord->id);

        Livewire::actingAs($landlord)
            ->test(Index::class)
            ->call('sendReceipt', $confirmed->id, 'email')
            ->assertHasNoErrors();

        $manual = NotificationDelivery::where('rent_payment_id', $confirmed->id)
            ->where('event', 'like', 'receipt_manual_%')
            ->where('channel', 'email')
            ->firstOrFail();

        $this->assertSame('queued', $manual->status);
        Queue::assertPushed(DeliverReceiptChannel::class, fn ($job) => $job->deliveryId === $manual->id);
    }

    /** @return array{0: User, 1: RentPayment} */
    private function pendingPayment(): array
    {
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $currency = Currency::create([
            'code' => 'DJF',
            'name' => 'Djiboutian Franc',
            'symbol' => 'Fdj',
            'decimals' => 0,
            'is_active' => true,
        ]);
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'currency_id' => $currency->id,
            'name' => 'Receipt Property',
            'type' => 'apartment',
            'address_line_1' => '1 Receipt Street',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);
        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'R1',
            'type' => 'apartment',
            'monthly_rent' => 120000,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'user_id' => $tenantUser->id,
            'first_name' => 'Adna',
            'last_name' => 'Tenant',
            'phone' => '+25377000000',
            'email' => $tenantUser->email,
            'status' => 'active',
        ]);
        $lease = Lease::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 120000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);
        $invoice = RentInvoice::create([
            'landlord_id' => $landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-RECEIPT-0001',
            'payment_reference' => 'KIR-RECEIPT',
            'invoice_month' => now()->startOfMonth(),
            'due_date' => now(),
            'amount' => 120000,
            'currency_id' => $currency->id,
            'status' => 'unpaid',
        ]);
        $payment = RentPayment::create([
            'landlord_id' => $landlord->id,
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'payment_number' => 'PAY-RECEIPT-0001',
            'payment_date' => now(),
            'amount' => 120000,
            'currency_id' => $currency->id,
            'method' => 'cash',
            'status' => 'pending',
        ]);

        return [$landlord, $payment];
    }
}
