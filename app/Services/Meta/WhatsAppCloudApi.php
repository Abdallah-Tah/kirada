<?php

namespace App\Services\Meta;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use LogicException;

class WhatsAppCloudApi
{
    public function isConfigured(): bool
    {
        return filled(config('services.meta.whatsapp.access_token'))
            && filled(config('services.meta.whatsapp.phone_number_id'));
    }

    /**
     * @return array<string, mixed>
     */
    public function sendText(string $recipient, string $message, bool $previewUrl = false): array
    {
        if (blank(trim($message))) {
            throw new InvalidArgumentException('A WhatsApp message body is required.');
        }

        return $this->send([
            'to' => $this->normalizeRecipient($recipient),
            'type' => 'text',
            'text' => [
                'body' => $message,
                'preview_url' => $previewUrl,
            ],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     * @return array<string, mixed>
     */
    public function sendTemplate(
        string $recipient,
        string $template,
        string $language = 'en_US',
        array $components = [],
    ): array {
        if (blank(trim($template))) {
            throw new InvalidArgumentException('A WhatsApp template name is required.');
        }

        $templatePayload = [
            'name' => $template,
            'language' => ['code' => $language],
        ];

        if ($components !== []) {
            $templatePayload['components'] = $components;
        }

        return $this->send([
            'to' => $this->normalizeRecipient($recipient),
            'type' => 'template',
            'template' => $templatePayload,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function markAsRead(string $messageId): array
    {
        if (blank(trim($messageId))) {
            throw new InvalidArgumentException('A WhatsApp message ID is required.');
        }

        return $this->send([
            'status' => 'read',
            'message_id' => $messageId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function send(array $payload): array
    {
        if (! $this->isConfigured()) {
            throw new LogicException('Meta WhatsApp Cloud API is not configured.');
        }

        $response = $this->request()->post(
            config('services.meta.whatsapp.phone_number_id').'/messages',
            ['messaging_product' => 'whatsapp'] + $payload,
        );

        return $response->throw()->json();
    }

    private function request(): PendingRequest
    {
        $version = trim((string) config('services.meta.graph_version', 'v23.0'), '/');

        return Http::baseUrl("https://graph.facebook.com/{$version}")
            ->withToken(config('services.meta.whatsapp.access_token'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function normalizeRecipient(string $recipient): string
    {
        $normalized = preg_replace('/\D+/', '', $recipient) ?? '';

        if ($normalized === '') {
            throw new InvalidArgumentException('A valid WhatsApp recipient number is required.');
        }

        return $normalized;
    }
}
