<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\WhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('get')) {
            return $this->verify($request);
        }

        if (! $this->validSignature($request)) {
            return response('Invalid signature', 401);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['messages'] ?? [] as $message) {
                    $this->storeMessage($message, $change['value']);
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = (string) $request->query('hub_verify_token');
        $expected = (string) config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, $token)) {
            return response((string) $request->query('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    private function validSignature(Request $request): bool
    {
        $secret = (string) config('services.whatsapp.app_secret');
        $signature = (string) $request->header('X-Hub-Signature-256');

        if ($secret === '' || ! str_starts_with($signature, 'sha256=')) {
            Log::warning('WhatsApp webhook rejected because app-secret signature verification is not configured.');

            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function storeMessage(array $message, array $value): void
    {
        $providerId = $message['id'] ?? null;
        $from = $message['from'] ?? null;

        if (! $providerId || ! $from || WhatsAppMessage::where('provider_message_id', $providerId)->exists()) {
            return;
        }

        $tenant = Tenant::query()->get(['landlord_id', 'phone'])
            ->first(fn (Tenant $candidate): bool => $this->normalisePhone($candidate->phone) === $this->normalisePhone($from));

        $type = (string) ($message['type'] ?? 'unknown');
        $content = $message[$type] ?? [];
        $body = $content['body'] ?? $content['text'] ?? $content['caption'] ?? null;

        WhatsAppMessage::create([
            'landlord_id' => $tenant?->landlord_id,
            'provider_message_id' => $providerId,
            'from_number' => $from,
            'profile_name' => data_get($value, 'contacts.0.profile.name'),
            'message_type' => $type,
            'body' => is_string($body) ? $body : null,
            'media_id' => is_array($content) ? ($content['id'] ?? null) : null,
            'payload' => $message,
            'received_at' => now()->setTimestamp((int) ($message['timestamp'] ?? now()->timestamp)),
        ]);
    }

    private function normalisePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }
}
