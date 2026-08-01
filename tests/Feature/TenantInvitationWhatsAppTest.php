<?php

namespace Tests\Feature;

use App\Jobs\ProcessBwaEvent;
use App\Jobs\SendTenantInvitationWhatsApp;
use App\Mail\TenantInvitationMail;
use App\Models\BwaEvent;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\TenantInvitationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TenantInvitationWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->seed(RolePermissionSeeder::class);

        $this->landlord = User::factory()->create(['name' => 'Abdallah Mohamed']);
        $this->landlord->assignRole('landlord');

        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud-Rachid',
            'phone' => '+253 77 22 24 06',
            'email' => 'adna@example.com',
            'status' => 'active',
        ]);
    }

    public function test_landlord_can_queue_a_whatsapp_invitation(): void
    {
        $this->configureBwa();
        Queue::fake();

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            null,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_WHATSAPP],
        );

        $this->assertSame(['whatsapp'], $invitation->delivery_channels);
        $this->assertDatabaseHas('tenant_invitations', [
            'id' => $invitation->id,
            'phone' => $this->tenant->phone,
            'whatsapp_sent_at' => null,
        ]);
        Queue::assertPushed(
            SendTenantInvitationWhatsApp::class,
            fn (SendTenantInvitationWhatsApp $job): bool => $job->invitationId === $invitation->id
                && $job->token === $invitation->token
                && $job->requestId === $invitation->whatsapp_request_id,
        );
        Mail::assertNothingSent();
    }

    public function test_whatsapp_invitation_job_posts_a_signed_template_and_records_provider_id(): void
    {
        $this->configureBwa();
        Http::fake([
            'bwa.test/api/v1/whatsapp/messages' => Http::response([
                'data' => ['message_id' => 'bwa-invitation-123'],
            ], 202),
        ]);

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            null,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_WHATSAPP],
        );

        (new SendTenantInvitationWhatsApp(
            $invitation->id,
            $invitation->token,
            $invitation->whatsapp_request_id,
        ))
            ->handle(app(BwaMessagingApi::class));

        $invitation->refresh();
        $this->assertSame('bwa-invitation-123', $invitation->whatsapp_message_id);
        $this->assertSame('queued', $invitation->whatsapp_status);
        $this->assertNull($invitation->whatsapp_sent_at);
        $this->assertNull($invitation->whatsapp_error);

        Http::assertSent(function ($request): bool {
            $timestamp = $request->header('X-BWA-Timestamp')[0] ?? '';
            $requestId = $request->header('X-BWA-Request-ID')[0] ?? '';
            $rawBody = $request->body();
            $expectedSignature = 'sha256='.hash_hmac(
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
                && ($request->header('X-BWA-Signature')[0] ?? null) === $expectedSignature
                && ($request->header('Idempotency-Key')[0] ?? null) === $request['idempotency_key']
                && $request['recipient'] === '+25377222406'
                && $request['type'] === 'template'
                && $request['product'] === 'kirada'
                && $request['template']['name'] === 'kirada_tenant_invitation'
                && $request['template']['language'] === 'fr'
                && count($request['template']['components'][0]['parameters']) === 4;
        });
    }

    public function test_bwa_status_events_update_whatsapp_invitation_delivery(): void
    {
        $invitation = TenantInvitation::create([
            'landlord_id' => $this->landlord->id,
            'tenant_id' => $this->tenant->id,
            'phone' => $this->tenant->phone,
            'delivery_channels' => ['whatsapp'],
            'token' => str_repeat('a', 64),
            'status' => 'pending',
            'expires_at' => now()->addWeek(),
            'whatsapp_message_id' => 'bwa-invitation-status-123',
            'whatsapp_status' => 'queued',
        ]);

        foreach (['accepted', 'sent', 'delivered', 'read'] as $status) {
            $event = BwaEvent::create([
                'event_id' => 'event-'.$status,
                'type' => 'whatsapp.message.status',
                'status' => BwaEvent::STATUS_QUEUED,
                'raw_body' => json_encode([
                    'id' => 'event-'.$status,
                    'type' => 'whatsapp.message.status',
                    'occurred_at' => now()->toIso8601String(),
                    'data' => [
                        'message_id' => 'bwa-invitation-status-123',
                        'status' => $status,
                        'occurred_at' => now()->toIso8601String(),
                    ],
                ], JSON_THROW_ON_ERROR),
                'payload_hash' => fake()->sha256(),
                'received_at' => now(),
            ]);

            (new ProcessBwaEvent($event->id))->handle();
        }

        $invitation->refresh();
        $this->assertSame('read', $invitation->whatsapp_status);
        $this->assertNotNull($invitation->whatsapp_sent_at);
        $this->assertNotNull($invitation->whatsapp_delivered_at);
        $this->assertNotNull($invitation->whatsapp_read_at);
        $this->assertNull($invitation->whatsapp_error);
    }

    public function test_bwa_failed_event_records_the_provider_error_on_the_invitation(): void
    {
        $invitation = TenantInvitation::create([
            'landlord_id' => $this->landlord->id,
            'tenant_id' => $this->tenant->id,
            'phone' => $this->tenant->phone,
            'delivery_channels' => ['whatsapp'],
            'token' => str_repeat('b', 64),
            'status' => 'pending',
            'expires_at' => now()->addWeek(),
            'whatsapp_message_id' => 'bwa-invitation-failed-123',
            'whatsapp_status' => 'queued',
        ]);
        $event = BwaEvent::create([
            'event_id' => 'event-failed',
            'type' => 'whatsapp.message.status',
            'status' => BwaEvent::STATUS_QUEUED,
            'raw_body' => json_encode([
                'id' => 'event-failed',
                'type' => 'whatsapp.message.status',
                'data' => [
                    'message_id' => 'bwa-invitation-failed-123',
                    'status' => 'failed',
                    'error' => [
                        'code' => '132001',
                        'message' => 'Template name does not exist.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            'payload_hash' => fake()->sha256(),
            'received_at' => now(),
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        $invitation->refresh();
        $this->assertSame('failed', $invitation->whatsapp_status);
        $this->assertSame('Template name does not exist.', $invitation->whatsapp_error);
        $this->assertNotNull($invitation->whatsapp_failed_at);
    }

    public function test_landlord_can_deliver_the_same_invitation_by_email_and_whatsapp(): void
    {
        $this->configureBwa();
        Queue::fake();

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            $this->tenant->email,
            $this->tenant->phone,
            [
                TenantInvitationService::CHANNEL_EMAIL,
                TenantInvitationService::CHANNEL_WHATSAPP,
            ],
        );

        Mail::assertQueued(
            TenantInvitationMail::class,
            fn (TenantInvitationMail $mail): bool => $mail->hasTo($this->tenant->email),
        );
        Queue::assertPushed(SendTenantInvitationWhatsApp::class);
        $this->assertSame(
            ['email', 'whatsapp'],
            $invitation->delivery_channels,
        );
    }

    public function test_whatsapp_invitation_requires_configured_bwa(): void
    {
        config([
            'services.bwa.api_url' => null,
            'services.bwa.app' => 'kirada',
            'services.bwa.request_signing_secret' => null,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Configure the BWA Messaging API');

        app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            null,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_WHATSAPP],
        );

        $this->assertSame(0, TenantInvitation::count());
    }

    public function test_existing_pending_invitation_can_add_whatsapp_delivery(): void
    {
        $this->configureBwa();
        Queue::fake();

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            $this->tenant->email,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_EMAIL],
        );

        app(TenantInvitationService::class)->resendWhatsApp($invitation);

        $this->assertSame(
            ['email', 'whatsapp'],
            $invitation->fresh()->delivery_channels,
        );
        Queue::assertPushed(SendTenantInvitationWhatsApp::class);
    }

    public function test_each_explicit_whatsapp_resend_creates_a_new_delivery_attempt(): void
    {
        $this->configureBwa();
        Queue::fake();

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            null,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_WHATSAPP],
        );
        $firstRequestId = $invitation->fresh()->whatsapp_request_id;

        app(TenantInvitationService::class)->resendWhatsApp($invitation->fresh());
        $secondRequestId = $invitation->fresh()->whatsapp_request_id;

        $this->assertNotSame($firstRequestId, $secondRequestId);
        Queue::assertPushed(
            SendTenantInvitationWhatsApp::class,
            fn (SendTenantInvitationWhatsApp $job): bool => $job->requestId === $firstRequestId,
        );
        Queue::assertPushed(
            SendTenantInvitationWhatsApp::class,
            fn (SendTenantInvitationWhatsApp $job): bool => $job->requestId === $secondRequestId,
        );
    }

    public function test_a_stale_whatsapp_attempt_cannot_send_after_an_explicit_resend(): void
    {
        $this->configureBwa();
        Queue::fake();
        Http::fake();

        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            null,
            $this->tenant->phone,
            [TenantInvitationService::CHANNEL_WHATSAPP],
        )->fresh();
        $staleJob = new SendTenantInvitationWhatsApp(
            $invitation->id,
            $invitation->token,
            $invitation->whatsapp_request_id,
        );

        app(TenantInvitationService::class)->resendWhatsApp($invitation);
        $staleJob->handle(app(BwaMessagingApi::class));

        Http::assertNothingSent();
    }

    private function configureBwa(): void
    {
        config([
            'services.bwa.api_url' => 'https://bwa.test',
            'services.bwa.app' => 'kirada',
            'services.bwa.request_signing_secret' => 'request-secret',
            'services.bwa.invitation_template' => 'kirada_tenant_invitation',
            'services.bwa.template_language' => 'fr',
        ]);
    }
}
