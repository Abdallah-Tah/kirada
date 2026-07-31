<?php

namespace App\Notifications\Channels;

use App\Services\Bwa\BwaMessagingApi;
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
    private BwaMessagingApi $client;

    public function __construct(?BwaMessagingApi $client = null)
    {
        $this->client = $client ?? app(BwaMessagingApi::class);
    }

    public static function isConfigured(): bool
    {
        return app(BwaMessagingApi::class)->isConfigured();
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
            $this->client->sendText(
                $payload['to'],
                $payload['message'],
                hash('sha256', implode('|', [
                    $notification::class,
                    (string) ($notifiable->getKey() ?? ''),
                    $payload['to'],
                    $payload['message'],
                ])),
            );
        } catch (Throwable $exception) {
            report($exception);

            Log::warning('BWA WhatsApp message failed.', [
                'to' => $payload['to'],
                'exception' => $exception::class,
            ]);
        }
    }
}
