<?php

namespace App\Livewire\LandlordTeam;

use App\Models\LandlordTeamMembership;
use App\Services\LandlordTeamService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $email = '';

    public string $role = 'property-manager';

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user->isLandlord() || $user->can('team.view'), 403);
    }

    #[Computed]
    public function members()
    {
        return LandlordTeamMembership::query()
            ->where('landlord_id', auth()->user()->landlordAccountId())
            ->with(['user:id,name,email', 'inviter:id,name'])
            ->latest()
            ->get();
    }

    public function invite(): void
    {
        abort_unless(auth()->user()->isLandlord() || auth()->user()->can('team.invite'), 403);

        $validated = $this->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:'.implode(',', LandlordTeamMembership::ROLES),
        ]);

        try {
            app(LandlordTeamService::class)->invite(auth()->user(), $validated['email'], $validated['role']);
        } catch (\DomainException $exception) {
            $this->addError('email', __($exception->getMessage()));

            return;
        }

        $this->reset('email');
        unset($this->members);
        Flux::toast(text: __('Team invitation sent.'), variant: 'success');
    }

    public function updateRole(int $membershipId, string $role): void
    {
        $membership = $this->membership($membershipId);

        try {
            app(LandlordTeamService::class)->updateRole(auth()->user(), $membership, $role);
        } catch (\DomainException $exception) {
            Flux::toast(text: __($exception->getMessage()), variant: 'danger');
        }

        unset($this->members);
    }

    public function remove(int $membershipId): void
    {
        $membership = $this->membership($membershipId);

        try {
            app(LandlordTeamService::class)->remove(auth()->user(), $membership);
        } catch (\DomainException $exception) {
            Flux::toast(text: __($exception->getMessage()), variant: 'danger');

            return;
        }

        unset($this->members);
        Flux::toast(text: __('Team member removed.'), variant: 'success');
    }

    private function membership(int $id): LandlordTeamMembership
    {
        return LandlordTeamMembership::where('landlord_id', auth()->user()->landlordAccountId())
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.landlord-team.index')
            ->layout('layouts.app')
            ->title(__('Property Team'));
    }
}
