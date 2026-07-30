<?php

namespace App\Services;

use App\Mail\LandlordTeamInvitationMail;
use App\Models\LandlordTeamMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LandlordTeamService
{
    public function invite(User $actor, string $email, string $role): LandlordTeamMembership
    {
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

        $token = Str::random(64);

        $membership = LandlordTeamMembership::updateOrCreate(
            ['landlord_id' => $landlordId, 'email' => $email],
            [
                'user_id' => $existingUser?->id,
                'invited_by' => $actor->id,
                'role' => $role,
                'token_hash' => hash('sha256', $token),
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ],
        );

        $membership->load('landlord');
        Mail::to($email)->queue(new LandlordTeamInvitationMail(
            $membership,
            route('team-invitations.accept', $token),
        ));

        return $membership;
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
