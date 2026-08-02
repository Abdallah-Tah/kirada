<?php

namespace App\Jobs;

use App\Models\TenantInvitation;
use App\Services\Bwa\BwaMessagingApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class SendTenantInvitationWhatsApp implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $invitationId,
        public string $token,
        public string $requestId,
    ) {
        $this->afterCommit();
    }

    public function handle(BwaMessagingApi $whatsApp): void
    {
        $invitation = TenantInvitation::query()
            ->with(['tenant', 'landlord'])
            ->findOrFail($this->invitationId);

        // A resend replaces the token. An older queued job must never send a
        // stale invitation link after the newer invitation has been created.
        if ($invitation->token !== $this->token
            || $invitation->whatsapp_request_id !== $this->requestId
            || ! $invitation->isPending()) {
            return;
        }

        if (! in_array('whatsapp', $invitation->delivery_channels ?? [], true)) {
            return;
        }

        if (blank($invitation->phone)) {
            throw new RuntimeException('A phone number is required for WhatsApp invitation delivery.');
        }

        $response = $whatsApp->sendTemplate(
            $invitation->phone,
            (string) config('services.bwa.invitation_template'),
            $whatsApp->templateLanguageFor($invitation->landlord),
            [[
                'type' => 'body',
                'parameters' => array_map(
                    static fn (string $value): array => ['type' => 'text', 'text' => $value],
                    [
                        $this->tenantName($invitation),
                        $invitation->landlord?->name ?? 'Kirada',
                        $invitation->accept_url,
                        $invitation->expires_at?->format('d/m/Y') ?? '—',
                    ],
                ),
            ]],
            $this->idempotencyKey($invitation),
        );

        $invitation->update([
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
        TenantInvitation::query()
            ->whereKey($this->invitationId)
            ->where('token', $this->token)
            ->where('whatsapp_request_id', $this->requestId)
            ->update([
                'whatsapp_status' => 'failed',
                'whatsapp_failed_at' => now(),
                'whatsapp_error' => $this->safeErrorMessage($exception),
            ]);
    }

    private function tenantName(TenantInvitation $invitation): string
    {
        $name = trim(($invitation->tenant?->first_name ?? '').' '.($invitation->tenant?->last_name ?? ''));

        return $name !== '' ? $name : 'Tenant';
    }

    private function idempotencyKey(TenantInvitation $invitation): string
    {
        return hash('sha256', implode('|', [
            'tenant-invitation',
            $invitation->id,
            $invitation->token,
            $this->requestId,
            'whatsapp',
        ]));
    }

    private function safeErrorMessage(?Throwable $exception): string
    {
        if ($exception instanceof RuntimeException && ! str_contains($exception->getMessage(), 'HTTP request')) {
            return mb_substr($exception->getMessage(), 0, 1000);
        }

        return 'The WhatsApp provider rejected the invitation request. Review BWA configuration and delivery logs.';
    }
}
