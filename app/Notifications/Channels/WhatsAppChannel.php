<?php

namespace App\Notifications\Channels;

use App\Services\Meta\WhatsAppCloudApi;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * WhatsApp Cloud API channel. Notifications opt in by implementing
 * toPhoneMessage(): ['to' => E.164 number, 'message' => short text].
 * No-ops with a log line when credentials are not configured, so the
 * channel is safe to wire before an operator account exists.
 */
class WhatsAppChannel
{
    private WhatsAppCloudApi $client;

    public function __construct(?WhatsAppCloudApi $client = null)
    {
        $this->client = $client ?? app(WhatsAppCloudApi::class);
    }

    public static function isConfigured(): bool
    {
        return app(WhatsAppCloudApi::class)->isConfigured();
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toPhoneMessage')) {
            return;
        }

        $payload = $notification->toPhoneMessage($notifiable);

        if (! $payload || empty($payload['to']) || empty($payload['message'])) {
            return;
        }

        if (! self::isConfigured()) {
            Log::info('WhatsApp channel skipped (not configured).', ['to' => $payload['to']]);

            return;
        }

        try {
            $this->client->sendText($payload['to'], $payload['message']);
        } catch (Throwable $exception) {
            report($exception);

            Log::warning('Meta WhatsApp message failed.', [
                'to' => $payload['to'],
                'exception' => $exception::class,
            ]);
        }
    }
}
