<?php

namespace App\Jobs;

use App\Models\LandlordTeamMembership;
use App\Services\Bwa\BwaMessagingApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class SendLandlordTeamInvitationWhatsApp implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $membershipId,
        public string $token,
        public string $requestId,
    ) {
        $this->afterCommit();
    }

    public function handle(BwaMessagingApi $whatsApp): void
    {
        $membership = LandlordTeamMembership::query()
            ->with('landlord')
            ->findOrFail($this->membershipId);

        // A re-invite replaces the token. An older queued job must never send a
        // stale link after a newer invitation has been issued. The raw token is
        // never stored, so compare its hash.
        if (! hash_equals($membership->token_hash, hash('sha256', $this->token))
            || $membership->whatsapp_request_id !== $this->requestId
            || ! $membership->isPending()) {
            return;
        }

        if (! $membership->usesChannel(LandlordTeamMembership::CHANNEL_WHATSAPP)) {
            return;
        }

        if (blank($membership->phone)) {
            throw new RuntimeException('A phone number is required for WhatsApp invitation delivery.');
        }

        $template = (string) config('services.bwa.team_invitation_template');

        if (blank($template)) {
            throw new RuntimeException('No approved WhatsApp template is configured for team invitations.');
        }

        $response = $whatsApp->sendTemplate(
            $membership->phone,
            $template,
            $whatsApp->templateLanguageFor($membership->landlord),
            [[
                'type' => 'body',
                'parameters' => array_map(
                    static fn (string $value): array => ['type' => 'text', 'text' => $value],
                    [
                        $membership->email,
                        $membership->landlord?->name ?? 'Kirada',
                        route('team-invitations.accept', $this->token),
                        $membership->expires_at?->format('d/m/Y') ?? '—',
                    ],
                ),
            ]],
            $this->idempotencyKey($membership),
        );

        $membership->update([
            'whatsapp_message_id' => data_get($response, 'id')
                ?? data_get($response, 'message.id')
                ?? data_get($response, 'data.id')
                ?? data_get($response, 'data.message_id'),
            'whatsapp_status' => 'queued',
            'whatsapp_sent_at' => null,
            'whatsapp_delivered_at' => null,
            'whatsapp_read_at' => null,
            'whatsapp_failed_at' => null,
            'whatsapp_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        LandlordTeamMembership::query()
            ->whereKey($this->membershipId)
            ->where('token_hash', hash('sha256', $this->token))
            ->where('whatsapp_request_id', $this->requestId)
            ->update([
                'whatsapp_status' => 'failed',
                'whatsapp_failed_at' => now(),
                'whatsapp_error' => $this->safeErrorMessage($exception),
            ]);
    }

    private function idempotencyKey(LandlordTeamMembership $membership): string
    {
        return hash('sha256', implode('|', [
            'team-invitation',
            $membership->id,
            $membership->token_hash,
            $this->requestId,
            'whatsapp',
        ]));
    }

    private function safeErrorMessage(?Throwable $exception): string
    {
        if ($exception instanceof RuntimeException && ! str_contains($exception->getMessage(), 'HTTP request')) {
            return mb_substr($exception->getMessage(), 0, 1000);
        }

        return 'The messaging provider rejected the invitation.';
    }
}
