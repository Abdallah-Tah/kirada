<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Cloud API channel. Notifications opt in by implementing
 * toPhoneMessage(): ['to' => E.164 number, 'message' => short text].
 * No-ops with a log line when credentials are not configured, so the
 * channel is safe to wire before an operator account exists.
 */
class WhatsAppChannel
{
    public static function isConfigured(): bool
    {
        return filled(config('services.whatsapp.token'))
            && filled(config('services.whatsapp.phone_number_id'));
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

        $phoneNumberId = config('services.whatsapp.phone_number_id');

        $response = Http::withToken(config('services.whatsapp.token'))
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => ltrim($payload['to'], '+'),
                'type' => 'text',
                'text' => ['body' => $payload['message']],
            ]);

        if ($response->failed()) {
            Log::warning('WhatsApp message failed.', [
                'to' => $payload['to'],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
