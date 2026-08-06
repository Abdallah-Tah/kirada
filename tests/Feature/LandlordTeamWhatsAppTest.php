<?php

namespace Tests\Feature;

use App\Jobs\ProcessBwaEvent;
use App\Jobs\SendLandlordTeamInvitationWhatsApp;
use App\Livewire\LandlordTeam\Index as LandlordTeamIndex;
use App\Mail\LandlordTeamInvitationMail;
use App\Models\BwaEvent;
use App\Models\LandlordTeamMembership;
use App\Models\User;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\LandlordTeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Team invitations gain the same channel choice tenant invitations already
 * have. WhatsApp only becomes selectable once BWA is configured *and* Meta has
 * approved a staff template — otherwise the provider rejects the send.
 */
class LandlordTeamWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create(['email_verified_at' => now()]);
        $this->landlord->assignRole('landlord');

        $this->enableWhatsApp();
    }

    private function enableWhatsApp(?string $template = 'kirada_team_invitation'): void
    {
        config([
            'services.bwa.api_url' => 'https://bwa.test',
            'services.bwa.app' => 'kirada',
            'services.bwa.request_signing_secret' => 'secret',
            'services.bwa.team_invitation_template' => $template,
        ]);
    }

    // ── Channel selection ───────────────────────────────

    public function test_an_invitation_defaults_to_email_only(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'property-manager');

        $this->assertSame(['email'], $membership->delivery_channels);
        Mail::assertQueued(LandlordTeamInvitationMail::class);
        Bus::assertNotDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }

    public function test_a_landlord_can_invite_over_whatsapp_only(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)->invite(
            $this->landlord,
            'staff@example.dj',
            'accountant',
            '+25377123456',
            ['whatsapp'],
        );

        $this->assertSame(['whatsapp'], $membership->delivery_channels);
        $this->assertSame('+25377123456', $membership->phone);
        Mail::assertNothingQueued();
        Bus::assertDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }

    public function test_a_landlord_can_invite_over_both_channels(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)->invite(
            $this->landlord,
            'staff@example.dj',
            'viewer',
            '+25377123456',
            ['email', 'whatsapp'],
        );

        $this->assertEqualsCanonicalizing(['email', 'whatsapp'], $membership->delivery_channels);
        Mail::assertQueued(LandlordTeamInvitationMail::class);
        Bus::assertDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }

    public function test_whatsapp_is_dropped_without_a_phone_number(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'viewer', null, ['whatsapp']);

        // Never leave an invitation with no way to reach anyone.
        $this->assertSame(['email'], $membership->delivery_channels);
        Bus::assertNotDispatched(SendLandlordTeamInvitationWhatsApp::class);
        Mail::assertQueued(LandlordTeamInvitationMail::class);
    }

    public function test_whatsapp_is_dropped_when_no_template_is_approved(): void
    {
        $this->enableWhatsApp(template: null);

        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)->invite(
            $this->landlord,
            'staff@example.dj',
            'viewer',
            '+25377123456',
            ['email', 'whatsapp'],
        );

        $this->assertSame(['email'], $membership->delivery_channels);
        Bus::assertNotDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }

    public function test_whatsapp_availability_requires_both_bwa_and_a_template(): void
    {
        $service = app(LandlordTeamService::class);
        $this->assertTrue($service->whatsAppAvailable());

        $this->enableWhatsApp(template: null);
        $this->assertFalse($service->whatsAppAvailable());

        config(['services.bwa.api_url' => null, 'services.bwa.team_invitation_template' => 'kirada_team_invitation']);
        $this->assertFalse($service->whatsAppAvailable());
    }

    // ── Resend ──────────────────────────────────────────

    public function test_an_email_invitation_can_be_resent_over_whatsapp(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'viewer');

        $previousHash = $membership->token_hash;

        $membership = app(LandlordTeamService::class)->resendWhatsApp($membership, '+25377123456');

        $this->assertEqualsCanonicalizing(['email', 'whatsapp'], $membership->delivery_channels);
        $this->assertSame('queued', $membership->whatsapp_status);
        // A fresh link is minted, so the emailed one is retired.
        $this->assertNotSame($previousHash, $membership->token_hash);
        Bus::assertDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }

    public function test_resending_requires_a_phone_number(): void
    {
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'viewer');

        $this->expectException(\DomainException::class);
        app(LandlordTeamService::class)->resendWhatsApp($membership);
    }

    public function test_an_accepted_invitation_cannot_be_resent(): void
    {
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'viewer', '+25377123456', ['email']);
        $membership->update(['status' => 'active', 'accepted_at' => now()]);

        $this->expectException(\DomainException::class);
        app(LandlordTeamService::class)->resendWhatsApp($membership->fresh());
    }

    // ── The job ─────────────────────────────────────────

    public function test_a_stale_job_does_not_send_a_retired_link(): void
    {
        Bus::fake();
        Mail::fake();

        $membership = app(LandlordTeamService::class)->invite(
            $this->landlord,
            'staff@example.dj',
            'viewer',
            '+25377123456',
            ['whatsapp'],
        );

        // Re-invite: the old token is replaced.
        app(LandlordTeamService::class)->invite(
            $this->landlord,
            'staff@example.dj',
            'viewer',
            '+25377123456',
            ['whatsapp'],
        );

        $stale = new SendLandlordTeamInvitationWhatsApp(
            $membership->id,
            'an-old-token',
            (string) $membership->whatsapp_request_id,
        );

        // No provider call, no exception — it simply stops.
        $stale->handle(app(BwaMessagingApi::class));

        $this->assertNotSame('sent', $membership->fresh()->whatsapp_status);
    }

    // ── Delivery receipts ───────────────────────────────

    public function test_the_bwa_webhook_advances_a_team_invitation_status(): void
    {
        Mail::fake();

        $membership = app(LandlordTeamService::class)
            ->invite($this->landlord, 'staff@example.dj', 'viewer', '+25377123456', ['email']);
        $membership->update(['whatsapp_message_id' => 'wamid.TEAM1', 'whatsapp_status' => 'queued']);

        $event = BwaEvent::create([
            'event_id' => 'evt-team-1',
            'type' => 'message.status',
            'status' => BwaEvent::STATUS_QUEUED,
            'raw_body' => $body = json_encode([
                'data' => ['message_id' => 'wamid.TEAM1', 'status' => 'delivered'],
            ]),
            'payload_hash' => hash('sha256', $body),
            'received_at' => now(),
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        $membership->refresh();
        $this->assertSame('delivered', $membership->whatsapp_status);
        $this->assertNotNull($membership->whatsapp_delivered_at);
    }

    // ── The screen ──────────────────────────────────────

    public function test_the_team_screen_offers_the_channel_picker(): void
    {
        Livewire::actingAs($this->landlord)
            ->test(LandlordTeamIndex::class)
            ->assertOk()
            ->assertSee('Send invitation via')
            ->assertSee('WhatsApp');
    }

    public function test_inviting_over_whatsapp_without_a_phone_shows_an_error(): void
    {
        Livewire::actingAs($this->landlord)
            ->test(LandlordTeamIndex::class)
            ->set('email', 'staff@example.dj')
            ->set('deliveryChannels', ['whatsapp'])
            ->call('invite')
            ->assertHasErrors('phone');

        $this->assertDatabaseCount('landlord_team_memberships', 0);
    }

    public function test_a_landlord_can_invite_with_whatsapp_from_the_screen(): void
    {
        Bus::fake();
        Mail::fake();

        Livewire::actingAs($this->landlord)
            ->test(LandlordTeamIndex::class)
            ->set('email', 'staff@example.dj')
            ->set('phone', '+25377123456')
            ->set('role', 'accountant')
            ->set('deliveryChannels', ['email', 'whatsapp'])
            ->call('invite')
            ->assertHasNoErrors();

        $membership = LandlordTeamMembership::firstOrFail();
        $this->assertEqualsCanonicalizing(['email', 'whatsapp'], $membership->delivery_channels);
        Bus::assertDispatched(SendLandlordTeamInvitationWhatsApp::class);
    }
}
