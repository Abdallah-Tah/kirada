<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBwaEvent;
use App\Models\BwaEvent;
use App\Models\BwaWebhookRequest;
use App\Services\Bwa\BwaSignature;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

class BwaWhatsAppEventController extends Controller
{
    private const EVENT_PATH = '/api/internal/bwa/whatsapp/events';

    public function __invoke(Request $request, BwaSignature $signature): JsonResponse
    {
        $rawBody = $request->getContent();
        $timestamp = (string) $request->header('X-BWA-Timestamp');
        $requestId = (string) $request->header('X-BWA-Request-ID');
        $providedSignature = (string) $request->header('X-BWA-Signature');
        $secret = (string) config('services.bwa.event_signing_secret');

        if ($secret === ''
            || ! ctype_digit($timestamp)
            || ! Str::isUuid($requestId)
            || ! $signature->verify(
                $providedSignature,
                'POST',
                self::EVENT_PATH,
                $timestamp,
                $requestId,
                $rawBody,
                $secret,
            )) {
            return $this->error('UNAUTHENTICATED', 'Event authentication failed.', Response::HTTP_UNAUTHORIZED);
        }

        $maxAge = max(1, (int) config('services.bwa.signature_max_age_seconds', 300));
        $signedAt = CarbonImmutable::createFromTimestamp((int) $timestamp);

        if (abs(now()->diffInSeconds($signedAt, false)) > $maxAge) {
            return $this->error('SIGNATURE_EXPIRED', 'Event signature has expired.', Response::HTTP_UNAUTHORIZED);
        }

        $payloadHash = hash('sha256', $rawBody);
        $webhookRequest = BwaWebhookRequest::query()->createOrFirst(
            ['request_id' => $requestId],
            [
                'payload_hash' => $payloadHash,
                'received_at' => now(),
                'expires_at' => now()->addSeconds($maxAge),
            ],
        );

        if (! $webhookRequest->wasRecentlyCreated) {
            return $this->error('REPLAYED_REQUEST', 'This request ID has already been used.', Response::HTTP_CONFLICT);
        }

        try {
            $payload = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->error('INVALID_JSON', 'The event body must be valid JSON.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! is_array($payload)) {
            return $this->error('INVALID_EVENT', 'The event body must be an object.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $eventId = data_get($payload, 'id') ?? data_get($payload, 'event_id');
        $eventType = data_get($payload, 'type') ?? data_get($payload, 'event');

        if (! is_string($eventId) || $eventId === '' || strlen($eventId) > 255
            || ! is_string($eventType) || $eventType === '' || strlen($eventType) > 100) {
            return $this->error('INVALID_EVENT', 'Event ID and type are required.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $occurredAt = $this->occurredAt($payload);
        $event = BwaEvent::query()->createOrFirst(
            ['event_id' => $eventId],
            [
                'type' => $eventType,
                'status' => BwaEvent::STATUS_QUEUED,
                'raw_body' => $rawBody,
                'payload_hash' => $payloadHash,
                'occurred_at' => $occurredAt,
                'received_at' => now(),
            ],
        );

        if ($event->wasRecentlyCreated) {
            ProcessBwaEvent::dispatch($event->id);
        }

        return response()->json([
            'accepted' => true,
            'duplicate' => ! $event->wasRecentlyCreated,
            'event_id' => $eventId,
        ], $event->wasRecentlyCreated ? Response::HTTP_ACCEPTED : Response::HTTP_OK);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function occurredAt(array $payload): ?CarbonImmutable
    {
        $value = data_get($payload, 'occurred_at') ?? data_get($payload, 'created_at');

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json(['error' => compact('code', 'message')], $status);
    }
}
