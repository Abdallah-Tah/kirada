<?php

namespace Tests\Feature;

use App\Jobs\ProcessMetaWhatsAppWebhook;
use App\Models\NotificationDelivery;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\DeliveryStatusUpdater;
use App\Services\WhatsApp\InboundMessageRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The canonical Meta webhook: signature handling, verification handshake, and
 * the message/status processing behind it.
 */
class MetaWhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-app-secret';

    private const VERIFY = 'test-verify-token';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.app_secret' => self::SECRET,
            'services.whatsapp.webhook_verify_token' => self::VERIFY,
        ]);
    }

    // ---------------------------------------------------------------- verify

    public function test_verification_returns_the_challenge_for_a_valid_token(): void
    {
        $this->get('/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token='.self::VERIFY.'&hub_challenge=CHALLENGE123')
            ->assertOk()
            ->assertSee('CHALLENGE123');
    }

    public function test_verification_is_forbidden_for_a_wrong_token_and_does_not_leak_it(): void
    {
        $response = $this->get('/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=SHOULD_NOT_ECHO');

        $response->assertForbidden();
        $response->assertDontSee('SHOULD_NOT_ECHO');
        $response->assertDontSee(self::VERIFY);
    }

    public function test_verification_is_forbidden_when_no_token_is_configured(): void
    {
        config(['services.whatsapp.webhook_verify_token' => '']);

        $this->get('/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=&hub_challenge=X')
            ->assertForbidden();
    }

    // ------------------------------------------------------------- signature

    public function test_a_valid_signature_is_accepted_and_dispatches_processing(): void
    {
        Queue::fake();

        $this->postSigned(['entry' => []])->assertOk();

        Queue::assertPushed(ProcessMetaWhatsAppWebhook::class);
    }

    public function test_an_invalid_signature_is_rejected_and_nothing_is_dispatched(): void
    {
        Queue::fake();

        $payload = json_encode(['entry' => []], JSON_THROW_ON_ERROR);

        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $payload, 'wrong-secret'),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_a_missing_signature_is_rejected(): void
    {
        Queue::fake();

        $this->postJson('/webhooks/whatsapp', ['entry' => []])->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_the_signature_is_computed_over_the_exact_raw_body(): void
    {
        Queue::fake();

        $payload = json_encode(['entry' => [], 'marker' => 'a'], JSON_THROW_ON_ERROR);

        // Signature of a different body must not validate this one.
        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $payload.' ', self::SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(401);

        Queue::assertNothingPushed();
    }

    // ---------------------------------------------------------------- status

    public function test_statuses_advance_an_invitation_through_sent_delivered_read(): void
    {
        $invitation = $this->invitationWithWamid('wamid.AAA');

        $this->processStatus('wamid.AAA', 'sent', 1786500000);
        $this->assertSame('sent', $invitation->fresh()->whatsapp_status);

        $this->processStatus('wamid.AAA', 'delivered', 1786500060);
        $this->assertSame('delivered', $invitation->fresh()->whatsapp_status);

        $this->processStatus('wamid.AAA', 'read', 1786500120);

        $fresh = $invitation->fresh();
        $this->assertSame('read', $fresh->whatsapp_status);
        $this->assertNotNull($fresh->whatsapp_sent_at);
        $this->assertNotNull($fresh->whatsapp_delivered_at);
        $this->assertNotNull($fresh->whatsapp_read_at);
    }

    public function test_a_late_sent_callback_does_not_downgrade_a_read_message(): void
    {
        $invitation = $this->invitationWithWamid('wamid.BBB');

        $this->processStatus('wamid.BBB', 'read', 1786500120);
        $this->processStatus('wamid.BBB', 'sent', 1786500000);

        $this->assertSame('read', $invitation->fresh()->whatsapp_status);
    }

    public function test_a_duplicate_delivered_callback_is_idempotent(): void
    {
        $invitation = $this->invitationWithWamid('wamid.CCC');

        $this->processStatus('wamid.CCC', 'delivered', 1786500060);
        $first = $invitation->fresh()->whatsapp_delivered_at;

        $this->processStatus('wamid.CCC', 'delivered', 1786500060);
        $second = $invitation->fresh()->whatsapp_delivered_at;

        $this->assertSame('delivered', $invitation->fresh()->whatsapp_status);
        $this->assertEquals($first, $second);
    }

    public function test_a_stale_failure_does_not_overwrite_a_delivered_message(): void
    {
        $invitation = $this->invitationWithWamid('wamid.DDD');

        $this->processStatus('wamid.DDD', 'delivered', 1786500060);
        $this->processStatus('wamid.DDD', 'failed', 1786500000, [
            ['code' => 131047, 'title' => 'Re-engagement message', 'error_data' => ['details' => 'outside window']],
        ]);

        $this->assertSame('delivered', $invitation->fresh()->whatsapp_status);
    }

    public function test_a_later_delivery_supersedes_an_earlier_failure(): void
    {
        $invitation = $this->invitationWithWamid('wamid.EEE');

        $this->processStatus('wamid.EEE', 'failed', 1786500000, [
            ['code' => 131030, 'title' => 'Not in allowed list'],
        ]);
        $this->assertSame('failed', $invitation->fresh()->whatsapp_status);

        $this->processStatus('wamid.EEE', 'delivered', 1786500060);

        $fresh = $invitation->fresh();
        $this->assertSame('delivered', $fresh->whatsapp_status);
        $this->assertNull($fresh->whatsapp_error);
    }

    public function test_a_failure_stores_the_meta_code_title_and_details(): void
    {
        $invitation = $this->invitationWithWamid('wamid.FFF');

        $this->processStatus('wamid.FFF', 'failed', 1786500000, [
            ['code' => 131030, 'title' => 'Recipient not in allowed list', 'error_data' => ['details' => 'Add recipient to list']],
        ]);

        $fresh = $invitation->fresh();
        $this->assertSame('failed', $fresh->whatsapp_status);
        $this->assertStringContainsString('131030', $fresh->whatsapp_error);
        $this->assertStringContainsString('Recipient not in allowed list', $fresh->whatsapp_error);
        $this->assertStringContainsString('Add recipient to list', $fresh->whatsapp_error);
        $this->assertNotNull($fresh->whatsapp_failed_at);
    }

    public function test_a_status_for_an_unknown_wamid_is_ignored_without_error(): void
    {
        $this->processStatus('wamid.UNKNOWN', 'delivered', 1786500060);

        $this->assertSame(0, TenantInvitation::whereNotNull('whatsapp_delivered_at')->count());
    }

    public function test_a_notification_delivery_is_advanced_by_wamid(): void
    {
        $landlord = User::factory()->create();
        $invoice = RentInvoice::factory()->create();
        $delivery = NotificationDelivery::create([
            'landlord_id' => $landlord->id,
            'rent_invoice_id' => $invoice->id,
            'event' => 'invoice_issued',
            'channel' => 'whatsapp',
            'status' => NotificationDelivery::STATUS_SENT,
            'provider_message_id' => 'gw_1',
            'provider_wamid' => 'wamid.GGG',
            'idempotency_key' => 'idem-1',
        ]);

        $this->processStatus('wamid.GGG', 'delivered', 1786500060);

        $this->assertSame(NotificationDelivery::STATUS_DELIVERED, $delivery->fresh()->status);
    }

    // --------------------------------------------------------------- inbound

    public function test_an_inbound_text_message_is_stored_and_associated(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->create(['landlord_id' => $landlord->id, 'phone' => '+1 207-409-7887']);

        $this->processInbound('wamid.IN1', '12074097887', 'Test reply', 1786500200, 'Abdallah');

        $message = WhatsAppMessage::firstOrFail();
        $this->assertSame('Test reply', $message->body);
        $this->assertSame('12074097887', $message->from_number);
        $this->assertSame('Abdallah', $message->profile_name);
        $this->assertSame($tenant->landlord_id, $message->landlord_id);
        $this->assertSame('text', $message->message_type);
    }

    public function test_a_duplicate_inbound_delivery_does_not_create_a_second_row(): void
    {
        Tenant::factory()->create([
            'landlord_id' => User::factory()->create()->id,
            'phone' => '+1 207-409-7887',
        ]);

        $this->processInbound('wamid.IN2', '12074097887', 'Test reply', 1786500200);
        $this->processInbound('wamid.IN2', '12074097887', 'Test reply', 1786500200);

        $this->assertSame(1, WhatsAppMessage::count());
    }

    public function test_an_inbound_reply_retains_the_context_message_id(): void
    {
        Tenant::factory()->create([
            'landlord_id' => User::factory()->create()->id,
            'phone' => '+1 207-409-7887',
        ]);

        (new ProcessMetaWhatsAppWebhook([
            'entry' => [['changes' => [['value' => [
                'contacts' => [['wa_id' => '12074097887', 'profile' => ['name' => 'Abdallah']]],
                'messages' => [[
                    'id' => 'wamid.IN3',
                    'from' => '12074097887',
                    'timestamp' => '1786500200',
                    'type' => 'text',
                    'text' => ['body' => 'Test reply'],
                    'context' => ['id' => 'wamid.ORIGINAL'],
                ]],
            ]]]]],
        ]))->handle(app(InboundMessageRecorder::class), app(DeliveryStatusUpdater::class));

        $this->assertSame('wamid.ORIGINAL', WhatsAppMessage::firstOrFail()->payload['context']['id']);
    }

    public function test_messages_and_statuses_in_one_payload_are_both_processed(): void
    {
        $invitation = $this->invitationWithWamid('wamid.MIX');
        Tenant::factory()->create([
            'landlord_id' => User::factory()->create()->id,
            'phone' => '+1 207-409-7887',
        ]);

        $this->dispatchPayload(['entry' => [['changes' => [['value' => [
            'contacts' => [['wa_id' => '12074097887', 'profile' => ['name' => 'A']]],
            'messages' => [[
                'id' => 'wamid.MIXIN', 'from' => '12074097887', 'timestamp' => '1786500200',
                'type' => 'text', 'text' => ['body' => 'hi'],
            ]],
            'statuses' => [[
                'id' => 'wamid.MIX', 'status' => 'delivered', 'timestamp' => '1786500060',
            ]],
        ]]]]]]);

        $this->assertSame(1, WhatsAppMessage::count());
        $this->assertSame('delivered', $invitation->fresh()->whatsapp_status);
    }

    // --------------------------------------------------------------- helpers

    private function invitationWithWamid(string $wamid): TenantInvitation
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->create(['landlord_id' => $landlord->id]);

        return TenantInvitation::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'email' => 'tenant@example.test',
            'phone' => '+12074097887',
            'delivery_channels' => ['whatsapp'],
            'token' => bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'whatsapp_message_id' => 'gw_'.$wamid,
            'whatsapp_wamid' => $wamid,
            'whatsapp_status' => 'queued',
        ]);
    }

    /** @param array<int, array<string, mixed>> $errors */
    private function processStatus(string $wamid, string $status, int $timestamp, array $errors = []): void
    {
        $entry = ['id' => $wamid, 'status' => $status, 'timestamp' => (string) $timestamp];

        if ($errors !== []) {
            $entry['errors'] = $errors;
        }

        $this->dispatchPayload(['entry' => [['changes' => [['value' => ['statuses' => [$entry]]]]]]]);
    }

    private function processInbound(
        string $wamid,
        string $from,
        string $body,
        int $timestamp,
        ?string $name = null,
    ): void {
        $value = [
            'messages' => [[
                'id' => $wamid, 'from' => $from, 'timestamp' => (string) $timestamp,
                'type' => 'text', 'text' => ['body' => $body],
            ]],
        ];

        if ($name) {
            $value['contacts'] = [['wa_id' => $from, 'profile' => ['name' => $name]]];
        }

        $this->dispatchPayload(['entry' => [['changes' => [['value' => $value]]]]]);
    }

    /** @param array<string, mixed> $payload */
    private function dispatchPayload(array $payload): void
    {
        (new ProcessMetaWhatsAppWebhook($payload))->handle(
            app(InboundMessageRecorder::class),
            app(DeliveryStatusUpdater::class),
        );
    }

    /** @param array<string, mixed> $payload */
    private function postSigned(array $payload): TestResponse
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'HTTP_X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $body, self::SECRET),
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }
}
