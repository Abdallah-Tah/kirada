<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Str;

class TenantInvitationService
{
    public const DEFAULT_EXPIRY_DAYS = 7;

    /**
     * Generate a secure random token.
     */
    public function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new invitation for a tenant.
     */
    public function createInvitation(int $landlordId, int $tenantId, ?string $email, ?string $phone): TenantInvitation
    {
        $tenant = Tenant::findOrFail($tenantId);

        abort_if($tenant->landlord_id !== $landlordId && !auth()->user()->hasRole('admin'), 403);

        if (empty($email) && empty($phone)) {
            throw new \DomainException('Either email or phone is required for an invitation.');
        }

        // Check for existing pending invitation
        $existing = TenantInvitation::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            throw new \DomainException('A pending invitation already exists for this tenant. Cancel it first or resend.');
        }

        return TenantInvitation::create([
            'landlord_id'  => $landlordId,
            'tenant_id'     => $tenantId,
            'email'         => $email,
            'phone'         => $phone,
            'token'         => $this->generateToken(),
            'status'        => 'pending',
            'expires_at'    => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    /**
     * Resend an invitation — resets expiry and generates a new token.
     */
    public function resendInvitation(TenantInvitation $invitation): TenantInvitation
    {
        if (!$invitation->isPending()) {
            throw new \DomainException('Only pending invitations can be resent.');
        }

        $invitation->update([
            'token'      => $this->generateToken(),
            'expires_at' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);

        return $invitation->fresh();
    }

    /**
     * Cancel a pending invitation.
     */
    public function cancelInvitation(TenantInvitation $invitation): TenantInvitation
    {
        if (!$invitation->isPending()) {
            throw new \DomainException('Only pending invitations can be cancelled.');
        }

        $invitation->update(['status' => 'cancelled']);

        return $invitation->fresh();
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

        if (!$invitation) {
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
        if (!$invitation->isPending()) {
            throw new \DomainException('This invitation is no longer pending.');
        }

        if ($invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);
            throw new \DomainException('This invitation has expired.');
        }

        // Check if a user with this email already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // Link existing user — verify password
            if (!password_verify($password, $user->password)) {
                throw new \DomainException('An account with this email already exists. Please provide the correct password to link it.');
            }
        } else {
            // Validate that invitation email matches if set
            if ($invitation->email && $invitation->email !== $email) {
                throw new \DomainException('The email address does not match the invitation.');
            }

            // Create new user
            $user = User::create([
                'name'               => $name,
                'email'              => $email,
                'password'            => $password,
                'email_verified_at'   => now(),
                'country_id'          => $invitation->landlord->country_id,
                'preferred_language' => $invitation->landlord->preferred_language ?? 'en',
                'phone_country_code'  => $invitation->landlord->phone_country_code,
            ]);
        }

        // Assign tenant role if not already
        if (!$user->hasRole('tenant')) {
            $user->assignRole('tenant');
        }

        // Link tenant to user
        $tenant = $invitation->tenant;
        $tenant->update([
            'user_id' => $user->id,
            'email'   => $tenant->email ?? $email,
        ]);

        // Mark invitation as accepted
        $invitation->update([
            'status'           => 'accepted',
            'accepted_at'      => now(),
            'accepted_user_id' => $user->id,
        ]);

        return $user;
    }
}