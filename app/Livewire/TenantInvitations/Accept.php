<?php

namespace App\Livewire\TenantInvitations;

use App\Models\TenantInvitation;
use App\Services\TenantInvitationService;
use Livewire\Component;

class Accept extends Component
{
    public string $token;
    public ?TenantInvitation $invitation = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $showAcceptForm = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invitation = app(TenantInvitationService::class)->findByToken($token);

        if (!$this->invitation) {
            abort(404);
        }

        // Pre-fill email if the invitation has one
        if ($this->invitation->email) {
            $this->email = $this->invitation->email;
        }

        // Pre-fill name from tenant record
        $tenant = $this->invitation->tenant;
        if ($tenant) {
            $this->name = trim($tenant->first_name . ' ' . $tenant->last_name);
        }

        $this->showAcceptForm = $this->invitation->isPending();
    }

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function accept(): void
    {
        if (!$this->invitation || !$this->invitation->isPending()) {
            Flux::toast('This invitation is no longer valid.', 'error');
            return;
        }

        $validated = $this->validate();

        try {
            $user = app(TenantInvitationService::class)->acceptInvitation(
                $this->invitation,
                $validated['name'],
                $validated['email'],
                $validated['password'],
            );

            // Log the user in
            auth()->login($user);

            Flux::toast('Welcome! Your tenant account is ready.', 'success');

            $this->redirect(route('dashboard'), navigate: true);
        } catch (\DomainException $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant-invitations.accept')
            ->layout('layouts.app')
            ->title(__('Accept Invitation'));
    }
}