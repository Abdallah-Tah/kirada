<?php

namespace App\Services;

use App\Jobs\SendTenantInvitationWhatsApp;
use App\Mail\TenantInvitationMail;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\Bwa\BwaMessagingApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantInvitationService
{
    public const DEFAULT_EXPIRY_DAYS = 7;

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    /** @var array<int, string> */
    public const CHANNELS = [self::CHANNEL_EMAIL, self::CHANNEL_WHATSAPP];

    /**
     * Generate a secure random token.
     */
    public function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new invitation for a tenant.
     *
     * @param  array<int, string>|null  $deliveryChannels
     */
    public function createInvitation(
        int $landlordId,
        int $tenantId,
        ?string $email,
        ?string $phone,
        ?array $deliveryChannels = null,
    ): TenantInvitation {
        $tenant = Tenant::findOrFail($tenantId);

        abort_if($tenant->landlord_id !== $landlordId, 403);

        if (empty($email) && empty($phone)) {
            throw new \DomainException('Either email or phone is required for an invitation.');
        }

        $email = $email ? Str::lower(trim($email)) : null;
        $phone = $phone ? trim($phone) : null;
        $deliveryChannels = $this->normalizeChannels($deliveryChannels, $email, $phone);

        // Check for existing pending invitation
        $existing = TenantInvitation::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            throw new \DomainException('A pending invitation already exists for this tenant. Cancel it first or resend.');
        }

        $invitation = TenantInvitation::create([
            'landlord_id' => $landlordId,
            'tenant_id' => $tenantId,
            'email' => $email,
            'phone' => $phone,
            'delivery_channels' => $deliveryChannels,
            'token' => $this->generateToken(),
            'status' => 'pending',
            'expires_at' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);

        $this->dispatchDelivery($invitation);

        return $invitation;
    }

    /**
     * Resend an invitation — resets expiry and generates a new token.
     */
    public function resendInvitation(TenantInvitation $invitation): TenantInvitation
    {
        if (! $invitation->isPending()) {
            throw new \DomainException('Only pending invitations can be resent.');
        }

        $invitation->update([
            'token' => $this->generateToken(),
            'expires_at' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
            'whatsapp_message_id' => null,
            'whatsapp_sent_at' => null,
            'whatsapp_error' => null,
        ]);

        $invitation = $invitation->fresh();
        $invitation->update([
            'delivery_channels' => $this->normalizeChannels(
                $invitation->delivery_channels,
                $invitation->email,
                $invitation->phone,
            ),
        ]);

        $this->dispatchDelivery($invitation->fresh());

        return $invitation->fresh();
    }

    /**
     * Queue a WhatsApp delivery for an existing pending invitation. This gives
     * landlords a way to add WhatsApp when an older invitation was email-only.
     */
    public function resendWhatsApp(TenantInvitation $invitation): TenantInvitation
    {
        if (! $invitation->isPending()) {
            throw new \DomainException('Only pending invitations can be sent by WhatsApp.');
        }

        if (blank($invitation->phone)) {
            throw new \DomainException('A phone number is required for WhatsApp invitation delivery.');
        }

        if (! app(BwaMessagingApi::class)->isConfigured()) {
            throw new \DomainException('Configure the BWA Messaging API before sending a WhatsApp invitation.');
        }

        $invitation->update([
            'delivery_channels' => array_values(array_unique([
                ...($invitation->delivery_channels ?? []),
                self::CHANNEL_WHATSAPP,
            ])),
            'whatsapp_message_id' => null,
            'whatsapp_sent_at' => null,
            'whatsapp_error' => null,
        ]);

        $invitation = $invitation->fresh();
        $this->queueWhatsApp($invitation);

        return $invitation->fresh();
    }

    /**
     * Cancel a pending invitation.
     */
    public function cancelInvitation(TenantInvitation $invitation): TenantInvitation
    {
        if (! $invitation->isPending()) {
            throw new \DomainException('Only pending invitations can be cancelled.');
        }

        $invitation->update(['status' => 'cancelled']);

        return $invitation->fresh();
    }

    /**
     * Deliver an invitation through each channel selected by the landlord.
     */
    protected function dispatchDelivery(TenantInvitation $invitation): void
    {
        if (in_array(self::CHANNEL_EMAIL, $invitation->delivery_channels ?? [], true)) {
            $this->sendInvitationEmail($invitation);
        }

        if (in_array(self::CHANNEL_WHATSAPP, $invitation->delivery_channels ?? [], true)) {
            $this->queueWhatsApp($invitation);
        }
    }

    /**
     * Send the invitation email to the tenant (if selected and configured).
     */
    protected function sendInvitationEmail(TenantInvitation $invitation): void
    {
        if (! $invitation->email) {
            return;
        }

        $tenant = $invitation->tenant;
        $landlord = $invitation->landlord;
        $tenantName = $tenant ? trim($tenant->first_name.' '.$tenant->last_name) : 'Tenant';
        $landlordName = $landlord?->name ?? 'Your Landlord';

        Mail::to($invitation->email)->send(new TenantInvitationMail(
            $invitation,
            $tenantName,
            $landlordName,
        ));
    }

    protected function queueWhatsApp(TenantInvitation $invitation): void
    {
        SendTenantInvitationWhatsApp::dispatch($invitation->id, $invitation->token);
    }

    /**
     * @param  array<int, string>|null  $channels
     * @return array<int, string>
     */
    protected function normalizeChannels(?array $channels, ?string $email, ?string $phone): array
    {
        $channels = $channels === null
            ? ($email ? [self::CHANNEL_EMAIL] : [self::CHANNEL_WHATSAPP])
            : array_values(array_unique(array_filter($channels, 'is_string')));

        $channels = array_values(array_intersect(self::CHANNELS, $channels));

        if ($channels === []) {
            throw new \DomainException('Select at least one invitation delivery channel.');
        }

        if (in_array(self::CHANNEL_EMAIL, $channels, true) && blank($email)) {
            throw new \DomainException('An email address is required for email invitation delivery.');
        }

        if (in_array(self::CHANNEL_WHATSAPP, $channels, true)) {
            if (blank($phone)) {
                throw new \DomainException('A phone number is required for WhatsApp invitation delivery.');
            }

            if (! app(BwaMessagingApi::class)->isConfigured()) {
                throw new \DomainException('Configure the BWA Messaging API before sending a WhatsApp invitation.');
            }
        }

        return $channels;
    }

    /**
     * Mark expired invitations (pending + past expires_at) as expired.
     */
    public function expirePending(): int
    {
        return TenantInvitation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Find a pending invitation by token.
     */
    public function findByToken(string $token): ?TenantInvitation
    {
        $invitation = TenantInvitation::where('token', $token)->first();

        if (! $invitation) {
            return null;
        }

        // Auto-expire if past due
        if ($invitation->isPending() && $invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);
            $invitation->refresh();
        }

        return $invitation;
    }

    /**
     * Accept an invitation — create or link a User account, assign tenant role,
     * and link the Tenant record to the user.
     */
    public function acceptInvitation(TenantInvitation $invitation, string $name, string $email, string $password): User
    {
        return DB::transaction(function () use ($invitation, $name, $email, $password): User {
            $invitation = TenantInvitation::query()
                ->with(['landlord', 'tenant'])
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if (! $invitation->isPending()) {
                throw new \DomainException('This invitation is no longer pending.');
            }

            if ($invitation->expires_at->isPast()) {
                $invitation->update(['status' => 'expired']);
                throw new \DomainException('This invitation has expired.');
            }

            $email = Str::lower(trim($email));
            $invitedEmail = $invitation->email ? Str::lower(trim($invitation->email)) : null;

            if ($invitedEmail && ! hash_equals($invitedEmail, $email)) {
                throw new \DomainException('The email address does not match the invitation.');
            }

            $tenant = $invitation->tenant;
            if (! $tenant || $tenant->landlord_id !== $invitation->landlord_id) {
                throw new \DomainException('This invitation is not linked to a valid tenant record.');
            }

            if ($tenant->user_id) {
                throw new \DomainException('This tenant already has a Kirada account.');
            }

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user) {
                if (! Hash::check($password, $user->password)) {
                    throw new \DomainException('An account with this email already exists. Enter its current password to link it.');
                }
            } else {
                $user = User::create([
                    'name' => trim($name),
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => now(),
                    'country_id' => $invitation->landlord->country_id,
                    'preferred_language' => $invitation->landlord->preferred_language ?? 'en',
                    'phone_country_code' => $invitation->landlord->phone_country_code,
                ]);
            }

            if (! $user->hasRole('tenant')) {
                $user->assignRole('tenant');
            }

            $tenant->update([
                'user_id' => $user->id,
                'email' => $tenant->email ?: $email,
            ]);

            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_user_id' => $user->id,
            ]);

            return $user;
        });
    }
}
