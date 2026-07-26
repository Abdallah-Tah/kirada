<?php

namespace App\Livewire\LandlordTeam;

use App\Models\LandlordTeamMembership;
use App\Services\LandlordTeamService;
use Livewire\Component;

class Accept extends Component
{
    public string $token;

    public ?LandlordTeamMembership $membership = null;

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->membership = app(LandlordTeamService::class)->findByToken($token);
        abort_unless($this->membership, 404);
    }

    public function accept(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = app(LandlordTeamService::class)->accept(
                $this->membership,
                $validated['name'],
                $validated['password'],
            );
        } catch (\DomainException $exception) {
            $this->addError('password', __($exception->getMessage()));

            return;
        }

        auth()->login($user);
        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.landlord-team.accept')
            ->layout('layouts.auth')
            ->title(__('Accept Team Invitation'));
    }
}
