<?php

namespace App\Services\Bwa;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class BwaMessagingApi
{
    private const MESSAGE_PATH = '/api/v1/whatsapp/messages';

    public function __construct(private BwaSignature $signature) {}

    public function isConfigured(): bool
    {
        return filled(config('services.bwa.api_url'))
            && filled(config('services.bwa.app'))
            && filled(config('services.bwa.request_signing_secret'));
    }

    /**
     * @return array<string, mixed>
     */
    public function sendText(
        string $recipient,
        string $message,
        string $idempotencyKey,
        bool $previewUrl = false,
    ): array {
        if (blank(trim($message))) {
            throw new InvalidArgumentException('A WhatsApp message body is required.');
        }

        return $this->send([
            'recipient' => $this->normalizeRecipient($recipient),
            'type' => 'text',
            'body' => $message,
            'product' => (string) config('services.bwa.app', 'kirada'),
            'preview_url' => $previewUrl,
            'idempotency_key' => $this->validateIdempotencyKey($idempotencyKey),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return array<string, mixed>
     */
    public function sendTemplate(
        string $recipient,
        string $template,
        string $language,
        array $components,
        string $idempotencyKey,
    ): array {
        if (blank(trim($template))) {
            throw new InvalidArgumentException('A WhatsApp template name is required.');
        }

        return $this->send([
            'recipient' => $this->normalizeRecipient($recipient),
            'type' => 'template',
            'product' => (string) config('services.bwa.app', 'kirada'),
            'template' => [
                'name' => trim($template),
                'language' => trim($language),
                'components' => $components,
            ],
            'idempotency_key' => $this->validateIdempotencyKey($idempotencyKey),
        ]);
    }

    /**
     * @param  array<int, string>  $bodyVariables
     * @return array<string, mixed>
     */
    public function sendDocumentTemplate(
        string $recipient,
        string $template,
        string $language,
        string $documentContents,
        array $bodyVariables,
        string $filename,
        string $idempotencyKey,
    ): array {
        if ($documentContents === '' || blank($filename)) {
            throw new InvalidArgumentException('A PDF document and filename are required.');
        }

        return $this->sendTemplate(
            $recipient,
            $template,
            $language,
            [
                [
                    'type' => 'header',
                    'parameters' => [[
                        'type' => 'document',
                        'document' => [
                            'filename' => $filename,
                            'content_type' => 'application/pdf',
                            'content_base64' => base64_encode($documentContents),
                        ],
                    ]],
                ],
                [
                    'type' => 'body',
                    'parameters' => array_map(
                        fn (string $value) => ['type' => 'text', 'text' => $value],
                        $bodyVariables,
                    ),
                ],
            ],
            $idempotencyKey,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function send(array $payload): array
    {
        $this->guardConfigured();

        $rawBody = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = (string) now()->timestamp;
        $requestId = (string) Str::uuid();
        $signature = $this->signature->sign(
            'POST',
            self::MESSAGE_PATH,
            $timestamp,
            $requestId,
            $rawBody,
            (string) config('services.bwa.request_signing_secret'),
        );

        $response = $this->request()
            ->withHeaders([
                'X-BWA-App' => (string) config('services.bwa.app', 'kirada'),
                'X-BWA-Timestamp' => $timestamp,
                'X-BWA-Request-ID' => $requestId,
                'X-BWA-Signature' => $signature,
                'Idempotency-Key' => (string) $payload['idempotency_key'],
            ])
            ->withBody($rawBody, 'application/json')
            ->post(self::MESSAGE_PATH);

        return $response->throw()->json();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.bwa.api_url'), '/'))
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(30);
    }

    private function guardConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new LogicException('BWA Messaging API is not configured.');
        }
    }

    private function normalizeRecipient(string $recipient): string
    {
        $normalized = preg_replace('/\D+/', '', $recipient) ?? '';

        if ($normalized === '') {
            throw new InvalidArgumentException('A valid WhatsApp recipient number is required.');
        }

        return '+'.$normalized;
    }

    private function validateIdempotencyKey(string $idempotencyKey): string
    {
        $idempotencyKey = trim($idempotencyKey);

        if ($idempotencyKey === '' || strlen($idempotencyKey) > 255) {
            throw new InvalidArgumentException('A stable idempotency key is required.');
        }

        return $idempotencyKey;
    }
}
