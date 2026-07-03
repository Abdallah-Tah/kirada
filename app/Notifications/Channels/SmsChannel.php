<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS channel over the Twilio REST API (plain HTTP, no SDK dependency).
 * Notifications opt in by implementing toPhoneMessage(): ['to', 'message'].
 * No-ops with a log line when credentials are not configured.
 */
class SmsChannel
{
    public static function isConfigured(): bool
    {
        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.from'));
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
            Log::info('SMS channel skipped (not configured).', ['to' => $payload['to']]);

            return;
        }

        $sid = config('services.twilio.sid');

        $response = Http::withBasicAuth($sid, config('services.twilio.token'))
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => config('services.twilio.from'),
                'To' => $payload['to'],
                'Body' => $payload['message'],
            ]);

        if ($response->failed()) {
            Log::warning('SMS message failed.', [
                'to' => $payload['to'],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
