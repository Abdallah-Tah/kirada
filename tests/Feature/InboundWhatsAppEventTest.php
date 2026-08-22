<?php

namespace Tests\Feature;

use App\Jobs\ProcessBwaEvent;
use App\Models\BwaEvent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Inbound WhatsApp messages relayed by the BWA gateway as
 * whatsapp.message.received events, which feed the landlord inbox.
 */
class InboundWhatsAppEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_inbound_event_is_recorded_against_the_tenants_landlord(): void
    {
        $tenant = $this->tenantWithPhone('+253 77 85 20 37');

        $this->processInboundEvent('evt_in_1', 'wamid.INBOUND1', '25377852037', 'Bonjour');

        $message = WhatsAppMessage::firstOrFail();

        $this->assertSame('wamid.INBOUND1', $message->provider_message_id);
        $this->assertSame('Bonjour', $message->body);
        $this->assertSame('25377852037', $message->from_number);
        $this->assertSame($tenant->landlord_id, $message->landlord_id);
    }

    public function test_phone_numbers_match_across_differing_formats(): void
    {
        $tenant = $this->tenantWithPhone('+253 77-85-20-37');

        $this->processInboundEvent('evt_in_fmt', 'wamid.FMT', '25377852037', 'salut');

        $this->assertSame($tenant->landlord_id, WhatsAppMessage::firstOrFail()->landlord_id);
    }

    public function test_a_tenant_stored_without_a_country_code_is_still_matched(): void
    {
        // Meta always sends the full international number; landlords often omit
        // the country code when entering a tenant.
        $tenant = $this->tenantWithPhone('(207) 409-7887');

        $this->processInboundEvent('evt_cc', 'wamid.CC', '12074097887', 'Test reply');

        $this->assertSame($tenant->landlord_id, WhatsAppMessage::firstOrFail()->landlord_id);
    }

    public function test_an_ambiguous_suffix_match_is_left_unattributed(): void
    {
        // The same local number recorded under two different landlords:
        // guessing would leak the message into the wrong portfolio.
        $this->tenantWithPhone('(207) 409-7887');
        $this->tenantWithPhone('207-409-7887');

        $this->processInboundEvent('evt_amb', 'wamid.AMB', '12074097887', 'hello');

        $this->assertNull(WhatsAppMessage::firstOrFail()->landlord_id);
    }

    public function test_a_short_shared_tail_does_not_attribute(): void
    {
        $this->tenantWithPhone('7887');

        $this->processInboundEvent('evt_short', 'wamid.SHORT', '12074097887', 'hi');

        $this->assertNull(WhatsAppMessage::firstOrFail()->landlord_id);
    }

    public function test_a_duplicate_event_does_not_create_a_second_message(): void
    {
        $this->tenantWithPhone('+253 77 85 20 37');

        $this->processInboundEvent('evt_dup_a', 'wamid.SAME', '25377852037', 'first');
        $this->processInboundEvent('evt_dup_b', 'wamid.SAME', '25377852037', 'first');

        $this->assertSame(1, WhatsAppMessage::count());
    }

    public function test_an_inbound_message_is_not_attributed_to_an_unrelated_landlord(): void
    {
        $mine = $this->tenantWithPhone('+253 77 85 20 37');
        $other = $this->tenantWithPhone('+253 77 11 11 11');

        $this->processInboundEvent('evt_iso', 'wamid.ISO', '25377852037', 'hello');

        $message = WhatsAppMessage::firstOrFail();

        $this->assertSame($mine->landlord_id, $message->landlord_id);
        $this->assertNotSame($other->landlord_id, $message->landlord_id);
    }

    public function test_an_unknown_sender_is_recorded_without_a_landlord_rather_than_dropped(): void
    {
        $this->processInboundEvent('evt_unknown', 'wamid.UNKNOWN', '25399999999', 'who is this');

        $message = WhatsAppMessage::firstOrFail();

        $this->assertNull($message->landlord_id);
        $this->assertSame('who is this', $message->body);
    }

    public function test_an_event_without_a_phone_number_is_ignored(): void
    {
        $this->tenantWithPhone('+253 77 85 20 37');

        $event = $this->makeEvent('evt_nophone', [
            'id' => 'evt_nophone',
            'type' => 'whatsapp.message.received',
            'data' => [
                'provider_message_id' => 'wamid.NOPHONE',
                'message_type' => 'text',
                'text' => 'no number attached',
            ],
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        $this->assertSame(0, WhatsAppMessage::count());
        $this->assertSame(BwaEvent::STATUS_PROCESSED, $event->refresh()->status);
    }

    public function test_status_events_are_still_routed_to_delivery_handling(): void
    {
        $event = $this->makeEvent('evt_status', [
            'id' => 'evt_status',
            'type' => 'whatsapp.message.status',
            'data' => ['message_id' => 'wamid.STATUS', 'status' => 'delivered'],
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        // A status event must never land in the inbox.
        $this->assertSame(0, WhatsAppMessage::count());
        $this->assertSame(BwaEvent::STATUS_PROCESSED, $event->refresh()->status);
    }

    public function test_a_matched_message_records_the_tenant_not_just_the_landlord(): void
    {
        $tenant = $this->tenantWithPhone('(207) 409-7887');

        $this->processInboundEvent('evt_tenant', 'wamid.TENANT', '12074097887', 'ok');

        $message = WhatsAppMessage::firstOrFail();

        $this->assertSame($tenant->id, $message->tenant_id);
        $this->assertSame($tenant->landlord_id, $message->landlord_id);
    }

    public function test_correcting_a_tenant_phone_number_re_attributes_orphaned_messages(): void
    {
        // The number was entered wrong, so the reply landed unmatched and no
        // landlord could see it. Fixing the tenant record must recover it.
        $tenant = $this->tenantWithPhone('207-000-0000');

        $this->processInboundEvent('evt_orphan', 'wamid.ORPHAN', '12074097887', 'This is me');
        $this->assertNull(WhatsAppMessage::firstOrFail()->landlord_id);

        $tenant->update(['phone' => '(207) 409-7887']);

        $message = WhatsAppMessage::firstOrFail();
        $this->assertSame($tenant->id, $message->tenant_id);
        $this->assertSame($tenant->landlord_id, $message->landlord_id);
    }

    public function test_changing_a_phone_number_away_releases_messages_it_no_longer_matches(): void
    {
        $tenant = $this->tenantWithPhone('(207) 409-7887');

        $this->processInboundEvent('evt_moved', 'wamid.MOVED', '12074097887', 'hello');
        $this->assertSame($tenant->landlord_id, WhatsAppMessage::firstOrFail()->landlord_id);

        $tenant->update(['phone' => '253 77 11 22 33']);

        $message = WhatsAppMessage::firstOrFail();
        $this->assertNull($message->landlord_id);
        $this->assertNull($message->tenant_id);
    }

    public function test_editing_an_unrelated_tenant_field_does_not_re_run_attribution(): void
    {
        $tenant = $this->tenantWithPhone('(207) 409-7887');
        $this->processInboundEvent('evt_keep', 'wamid.KEEP', '12074097887', 'hi');

        // Attribution is deliberately pinned to the phone number; a name change
        // must not quietly reshuffle who owns which message.
        WhatsAppMessage::query()->update(['landlord_id' => null, 'tenant_id' => null]);
        $tenant->update(['first_name' => 'Renamed']);

        $this->assertNull(WhatsAppMessage::firstOrFail()->landlord_id);
    }

    public function test_the_relayed_profile_name_and_media_id_are_recorded(): void
    {
        $this->tenantWithPhone('(207) 409-7887');

        $event = $this->makeEvent('evt_media', [
            'id' => 'evt_media',
            'type' => 'whatsapp.message.received',
            'event_type' => 'whatsapp.message.received',
            'occurred_at' => now()->toIso8601String(),
            'data' => [
                'provider_message_id' => 'wamid.MEDIA',
                'provider' => 'meta',
                'message_type' => 'image',
                'text' => null,
                'phone_number' => '12074097887',
                'profile_name' => 'Abdallah Mohamed',
                'media_id' => 'media-123',
            ],
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        $message = WhatsAppMessage::firstOrFail();

        $this->assertSame('Abdallah Mohamed', $message->profile_name);
        $this->assertSame('media-123', $message->media_id);
    }

    private function tenantWithPhone(string $phone): Tenant
    {
        $landlord = User::factory()->create();

        return Tenant::factory()->create([
            'landlord_id' => $landlord->id,
            'phone' => $phone,
        ]);
    }

    private function processInboundEvent(
        string $eventId,
        string $providerMessageId,
        string $phone,
        string $text,
    ): void {
        $event = $this->makeEvent($eventId, [
            'id' => $eventId,
            'type' => 'whatsapp.message.received',
            'event_type' => 'whatsapp.message.received',
            'occurred_at' => now()->toIso8601String(),
            'data' => [
                'provider_message_id' => $providerMessageId,
                'provider' => 'meta',
                'message_type' => 'text',
                'text' => $text,
                'phone_number' => $phone,
            ],
        ]);

        (new ProcessBwaEvent($event->id))->handle();
    }

    /** @param array<string, mixed> $payload */
    private function makeEvent(string $eventId, array $payload): BwaEvent
    {
        return BwaEvent::create([
            'event_id' => $eventId,
            'type' => $payload['type'],
            'status' => BwaEvent::STATUS_QUEUED,
            'raw_body' => json_encode($payload, JSON_THROW_ON_ERROR),
            'payload_hash' => hash('sha256', $eventId),
            'received_at' => now(),
        ]);
    }
}
