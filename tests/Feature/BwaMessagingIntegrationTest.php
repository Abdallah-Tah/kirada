<?php

namespace Tests\Feature;

use App\Jobs\ProcessBwaEvent;
use App\Models\BwaEvent;
use App\Services\Bwa\BwaSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BwaMessagingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const EVENT_SECRET = 'test-event-signing-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.bwa.event_signing_secret' => self::EVENT_SECRET,
            'services.bwa.signature_max_age_seconds' => 300,
        ]);
    }

    public function test_signed_event_is_stored_idempotently_and_queued(): void
    {
        Queue::fake();
        $payload = [
            'id' => 'evt_123',
            'type' => 'whatsapp.message.received',
            'occurred_at' => now()->toIso8601String(),
            'data' => ['message_id' => 'msg_123'],
        ];

        $this->postSigned($payload)
            ->assertAccepted()
            ->assertJson([
                'accepted' => true,
                'duplicate' => false,
                'event_id' => 'evt_123',
            ]);

        $event = BwaEvent::firstOrFail();
        $this->assertSame('evt_123', $event->event_id);
        $this->assertSame(hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)), $event->payload_hash);
        $this->assertSame(json_encode($payload, JSON_THROW_ON_ERROR), $event->raw_body);
        Queue::assertPushed(ProcessBwaEvent::class, 1);

        $this->postSigned($payload, (string) Str::uuid())
            ->assertOk()
            ->assertJson(['accepted' => true, 'duplicate' => true]);

        $this->assertSame(1, BwaEvent::count());
        Queue::assertPushed(ProcessBwaEvent::class, 1);
    }

    public function test_invalid_expired_and_missing_signatures_are_rejected(): void
    {
        $payload = ['id' => 'evt_invalid', 'type' => 'whatsapp.message.received'];

        $this->postJson('/api/internal/bwa/whatsapp/events', $payload)
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');

        $this->postSigned($payload, timestamp: (string) now()->subMinutes(10)->timestamp)
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'SIGNATURE_EXPIRED');

        $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);
        $this->call(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            [],
            [],
            [],
            [
                'HTTP_X_BWA_TIMESTAMP' => (string) now()->timestamp,
                'HTTP_X_BWA_REQUEST_ID' => (string) Str::uuid(),
                'HTTP_X_BWA_SIGNATURE' => 'sha256=invalid',
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody,
        )->assertUnauthorized();

        $this->assertDatabaseCount('bwa_events', 0);
        $this->assertDatabaseCount('bwa_webhook_requests', 0);
    }

    public function test_signature_uses_the_method_path_and_raw_body_hash_canonical_format_only(): void
    {
        $rawBody = '{"id":"evt_canonical","type":"integration.test"}';
        $timestamp = '1785470400';
        $requestId = '9e9a65a2-cf89-46ce-9c70-7ea429ef76aa';
        $canonical = implode("\n", [
            'POST',
            '/api/internal/bwa/whatsapp/events',
            $timestamp,
            $requestId,
            hash('sha256', $rawBody),
        ]);
        $expected = 'sha256='.hash_hmac('sha256', $canonical, self::EVENT_SECRET);

        $actual = app(BwaSignature::class)->sign(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            $timestamp,
            $requestId,
            $rawBody,
            self::EVENT_SECRET,
        );

        $this->assertSame($expected, $actual);
        $this->assertNotSame(
            'sha256='.hash_hmac(
                'sha256',
                $timestamp.'.'.$requestId.'.'.$rawBody,
                self::EVENT_SECRET,
            ),
            $actual,
        );
    }

    public function test_body_modified_after_signing_is_rejected(): void
    {
        $signedBody = json_encode([
            'id' => 'evt_original',
            'type' => 'integration.test',
        ], JSON_THROW_ON_ERROR);
        $modifiedBody = json_encode([
            'id' => 'evt_modified',
            'type' => 'integration.test',
        ], JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $requestId = (string) Str::uuid();
        $signature = app(BwaSignature::class)->sign(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            $timestamp,
            $requestId,
            $signedBody,
            self::EVENT_SECRET,
        );

        $this->call(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            [],
            [],
            [],
            [
                'HTTP_X_BWA_TIMESTAMP' => $timestamp,
                'HTTP_X_BWA_REQUEST_ID' => $requestId,
                'HTTP_X_BWA_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $modifiedBody,
        )->assertUnauthorized()
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');

        $this->assertDatabaseCount('bwa_events', 0);
        $this->assertDatabaseCount('bwa_webhook_requests', 0);
    }

    public function test_replayed_request_id_is_rejected_even_for_a_different_event(): void
    {
        Queue::fake();
        $requestId = (string) Str::uuid();

        $this->postSigned(
            ['id' => 'evt_first', 'type' => 'whatsapp.message.received'],
            $requestId,
        )->assertAccepted();

        $this->postSigned(
            ['id' => 'evt_second', 'type' => 'whatsapp.message.received'],
            $requestId,
        )->assertStatus(Response::HTTP_CONFLICT)
            ->assertJsonPath('error.code', 'REPLAYED_REQUEST');

        $this->assertDatabaseCount('bwa_events', 1);
    }

    public function test_event_processing_runs_asynchronously_and_marks_event_processed(): void
    {
        $event = BwaEvent::create([
            'event_id' => 'evt_process',
            'type' => 'whatsapp.message.received',
            'status' => BwaEvent::STATUS_QUEUED,
            'raw_body' => json_encode([
                'id' => 'evt_process',
                'type' => 'whatsapp.message.received',
                'data' => ['message_id' => 'msg_unknown'],
            ], JSON_THROW_ON_ERROR),
            'payload_hash' => hash('sha256', 'evt_process'),
            'received_at' => now(),
        ]);

        (new ProcessBwaEvent($event->id))->handle();

        $event->refresh();
        $this->assertSame(BwaEvent::STATUS_PROCESSED, $event->status);
        $this->assertNotNull($event->processed_at);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postSigned(
        array $payload,
        ?string $requestId = null,
        ?string $timestamp = null,
    ): TestResponse {
        $rawBody = json_encode($payload, JSON_THROW_ON_ERROR);
        $requestId ??= (string) Str::uuid();
        $timestamp ??= (string) now()->timestamp;
        $signature = app(BwaSignature::class)->sign(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            $timestamp,
            $requestId,
            $rawBody,
            self::EVENT_SECRET,
        );

        return $this->call(
            'POST',
            '/api/internal/bwa/whatsapp/events',
            [],
            [],
            [],
            [
                'HTTP_X_BWA_TIMESTAMP' => $timestamp,
                'HTTP_X_BWA_REQUEST_ID' => $requestId,
                'HTTP_X_BWA_SIGNATURE' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $rawBody,
        );
    }
}
