<?php

namespace App\Services;

use App\Jobs\SendLandlordTeamInvitationWhatsApp;
use App\Mail\LandlordTeamInvitationMail;
use App\Models\LandlordTeamMembership;
use App\Models\User;
use App\Services\Bwa\BwaMessagingApi;
use App\Support\Locales;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LandlordTeamService
{
    /**
     * Is WhatsApp available as a team-invitation channel?
     *
     * It needs both a configured BWA client and a Meta-approved template for
     * staff invitations. Without the template the provider rejects the send, so
     * the option stays hidden rather than queueing work that cannot succeed.
     */
    public function whatsAppAvailable(): bool
    {
        return app(BwaMessagingApi::class)->isConfigured()
            && filled(config('services.bwa.team_invitation_template'));
    }

    /**
     * @param  array<int, string>|null  $channels
     */
    public function invite(
        User $actor,
        string $email,
        string $role,
        ?string $phone = null,
        ?array $channels = null,
    ): LandlordTeamMembership {
        $landlordId = $actor->landlordAccountId();

        if (! $landlordId || (! $actor->isLandlord() && ! $actor->can('team.invite'))) {
            throw new \DomainException('You are not allowed to invite team members.');
        }

        if (! in_array($role, LandlordTeamMembership::ROLES, true)) {
            throw new \DomainException('Select a valid team role.');
        }

        if (! $actor->isLandlord() && $role === 'landlord-admin') {
            throw new \DomainException('Only the account owner can invite another administrator.');
        }

        $email = str($email)->lower()->trim()->toString();
        $landlord = User::findOrFail($landlordId);

        if ($email === str($landlord->email)->lower()->toString()) {
            throw new \DomainException('The account owner is already part of this team.');
        }

        $existingUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existingUser?->isLandlord() || $existingUser?->teamMembership?->isActive()) {
            throw new \DomainException('This person already belongs to a landlord account.');
        }

        $phone = $phone ? trim($phone) : null;
        $channels = $this->normalizeChannels($channels, $phone);

        $token = Str::random(64);

        $membership = LandlordTeamMembership::updateOrCreate(
            ['landlord_id' => $landlordId, 'email' => $email],
            [
                'user_id' => $existingUser?->id,
                'invited_by' => $actor->id,
                'role' => $role,
                'phone' => $phone,
                'delivery_channels' => $channels,
                'token_hash' => hash('sha256', $token),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
                'whatsapp_message_id' => null,
                'whatsapp_request_id' => null,
                'whatsapp_status' => null,
                'whatsapp_sent_at' => null,
                'whatsapp_delivered_at' => null,
                'whatsapp_read_at' => null,
                'whatsapp_failed_at' => null,
                'whatsapp_error' => null,
            ],
        );

        $membership->load('landlord');
        $this->dispatchDelivery($membership, $token);

        return $membership;
    }

    /**
     * Queue a WhatsApp delivery for an existing pending invitation, so a
     * landlord can add WhatsApp to an invitation that went out by email only.
     *
     * The raw token is never stored, so re-sending has to mint a new one — the
     * previous link stops working, which is the correct behaviour for a
     * credential being re-delivered over a second channel.
     */
    public function resendWhatsApp(LandlordTeamMembership $membership, ?string $phone = null): LandlordTeamMembership
    {
        if (! $membership->isPending()) {
            throw new \DomainException('Only pending invitations can be sent by WhatsApp.');
        }

        $phone = $phone ? trim($phone) : $membership->phone;

        if (blank($phone)) {
            throw new \DomainException('A phone number is required for WhatsApp invitation delivery.');
        }

        if (! $this->whatsAppAvailable()) {
            throw new \DomainException('Configure the BWA Messaging API and an approved team invitation template first.');
        }

        $token = Str::random(64);

        $membership->update([
            'phone' => $phone,
            'delivery_channels' => array_values(array_unique([
                ...($membership->delivery_channels ?? [LandlordTeamMembership::CHANNEL_EMAIL]),
                LandlordTeamMembership::CHANNEL_WHATSAPP,
            ])),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'whatsapp_message_id' => null,
            'whatsapp_request_id' => null,
            'whatsapp_status' => null,
            'whatsapp_sent_at' => null,
            'whatsapp_delivered_at' => null,
            'whatsapp_read_at' => null,
            'whatsapp_failed_at' => null,
            'whatsapp_error' => null,
        ]);

        $this->queueWhatsApp($membership->fresh('landlord'), $token);

        return $membership->fresh();
    }

    /**
     * Deliver an invitation through each channel the landlord selected.
     */
    private function dispatchDelivery(LandlordTeamMembership $membership, string $token): void
    {
        if ($membership->usesChannel(LandlordTeamMembership::CHANNEL_EMAIL)) {
            Mail::to($membership->email)
                ->locale(Locales::forLandlord($membership->landlord))
                ->queue(new LandlordTeamInvitationMail(
                    $membership,
                    route('team-invitations.accept', $token),
                ));
        }

        if ($membership->usesChannel(LandlordTeamMembership::CHANNEL_WHATSAPP)) {
            $this->queueWhatsApp($membership, $token);
        }
    }

    private function queueWhatsApp(LandlordTeamMembership $membership, string $token): void
    {
        $requestId = (string) Str::uuid();

        $membership->update([
            'whatsapp_request_id' => $requestId,
            'whatsapp_status' => 'queued',
        ]);

        SendLandlordTeamInvitationWhatsApp::dispatch($membership->id, $token, $requestId);
    }

    /**
     * Email is the floor: an invitation always has at least one channel, and
     * WhatsApp is only honoured when it can actually be delivered.
     *
     * @param  array<int, string>|null  $channels
     * @return array<int, string>
     */
    private function normalizeChannels(?array $channels, ?string $phone): array
    {
        $channels = array_values(array_intersect(
            $channels ?? [LandlordTeamMembership::CHANNEL_EMAIL],
            LandlordTeamMembership::CHANNELS,
        ));

        if (blank($phone) || ! $this->whatsAppAvailable()) {
            $channels = array_values(array_diff($channels, [LandlordTeamMembership::CHANNEL_WHATSAPP]));
        }

        if ($channels === []) {
            $channels = [LandlordTeamMembership::CHANNEL_EMAIL];
        }

        return $channels;
    }

    public function findByToken(string $token): ?LandlordTeamMembership
    {
        return LandlordTeamMembership::with('landlord')
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    public function accept(
        LandlordTeamMembership $membership,
        string $name,
        string $password,
    ): User {
        if (! $membership->isPending()) {
            throw new \DomainException('This team invitation is no longer valid.');
        }

        return DB::transaction(function () use ($membership, $name, $password): User {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($membership->email)])->first();

            if ($user) {
                if (! Hash::check($password, $user->password)) {
                    throw new \DomainException('The password does not match the existing account.');
                }

                if ($user->isLandlord() || $user->teamMembership?->isActive()) {
                    throw new \DomainException('This account already belongs to a landlord team.');
                }
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $membership->email,
                    'password' => $password,
                    'email_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'privacy_accepted_at' => now(),
                ]);
            }

            $user->syncRoles([$membership->role]);

            $membership->update([
                'user_id' => $user->id,
                'status' => 'active',
                'accepted_at' => now(),
            ]);

            return $user->refresh();
        });
    }

    public function updateRole(User $actor, LandlordTeamMembership $membership, string $role): void
    {
        $this->assertManageable($actor, $membership);

        if (! in_array($role, LandlordTeamMembership::ROLES, true)) {
            throw new \DomainException('Select a valid team role.');
        }

        if (! $actor->isLandlord() && ($membership->role === 'landlord-admin' || $role === 'landlord-admin')) {
            throw new \DomainException('Only the account owner can manage administrators.');
        }

        $membership->update(['role' => $role]);
        $membership->user?->syncRoles([$role]);
    }

    public function remove(User $actor, LandlordTeamMembership $membership): void
    {
        $this->assertManageable($actor, $membership);

        if (! $actor->isLandlord() && $membership->role === 'landlord-admin') {
            throw new \DomainException('Only the account owner can remove an administrator.');
        }

        $membership->user?->removeRole($membership->role);
        $membership->update(['status' => 'revoked']);
    }

    private function assertManageable(User $actor, LandlordTeamMembership $membership): void
    {
        if (
            $membership->landlord_id !== $actor->landlordAccountId()
            || (! $actor->isLandlord() && ! $actor->can('team.manage'))
        ) {
            throw new \DomainException('You are not allowed to manage this team member.');
        }
    }
}
