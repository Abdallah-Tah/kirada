<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMetaWhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * The canonical Meta WhatsApp webhook for Kirada.
 *
 * Meta posts inbound messages and delivery statuses to one callback URL, and
 * retries anything that is slow or not 2xx. So this endpoint does the minimum
 * synchronously — verify the signature, hand the payload to a queued job — and
 * answers immediately. All interpretation happens in ProcessMetaWhatsAppWebhook.
 */
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

        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            return response('EVENT_RECEIVED', 200);
        }

        ProcessMetaWhatsAppWebhook::dispatch($payload);

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Meta's subscription handshake. PHP rewrites dots in query keys to
     * underscores, so hub.mode arrives as hub_mode; both spellings are accepted
     * rather than relying on that.
     */
    private function verify(Request $request): Response
    {
        $mode = (string) ($request->query('hub_mode') ?? $request->query('hub.mode'));
        $token = (string) ($request->query('hub_verify_token') ?? $request->query('hub.verify_token'));
        $challenge = (string) ($request->query('hub_challenge') ?? $request->query('hub.challenge'));
        $expected = (string) config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $expected !== '' && hash_equals($expected, $token)) {
            return response($challenge, 200);
        }

        // Deliberately does not echo the expected or supplied token.
        Log::warning('WhatsApp webhook verification rejected.', ['mode' => $mode]);

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
}
