<?php

namespace App\Livewire\TenantInvitations;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Services\TenantInvitationService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    // Create form
    public ?int $tenant_id = null;
    public ?string $email = null;
    public ?string $phone = null;

    // Copied link feedback
    public ?int $copiedId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'nullable|string|max:30',
        ];
    }

    #[Computed]
    public function invitations()
    {
        $query = TenantInvitation::query()
            ->with([
                'tenant:id,first_name,last_name,phone,email,user_id',
                'acceptedUser:id,name,email',
            ])
            ->when($this->search, function ($q) {
                $q->whereHas('tenant', function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%");
                })->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function availableTenants()
    {
        $query = Tenant::select('id', 'first_name', 'last_name', 'phone', 'email')
            ->whereDoesntHave('user')
            ->orderBy('first_name');

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    public function sendInvitation(): void
    {
        $this->authorize('create', TenantInvitation::class);

        $validated = $this->validate();

        if (empty($validated['email']) && empty($validated['phone'])) {
            $this->addError('email', 'Either email or phone is required.');
            return;
        }

        // Ensure landlord ownership of tenant
        if (auth()->user()->hasRole('landlord')) {
            $tenant = Tenant::find($validated['tenant_id']);
            abort_if($tenant->landlord_id !== auth()->id(), 403);
        }

        $landlordId = auth()->user()->hasRole('admin')
            ? Tenant::find($validated['tenant_id'])->landlord_id
            : auth()->id();

        try {
            app(TenantInvitationService::class)->createInvitation(
                $landlordId,
                $validated['tenant_id'],
                $validated['email'],
                $validated['phone'],
            );
        } catch (\DomainException $e) {
            $this->addError('tenant_id', $e->getMessage());
            return;
        }

        Flux::toast('Invitation created. Share the link with the tenant.', 'success');

        $this->reset(['tenant_id', 'email', 'phone']);
        unset($this->invitations);
        unset($this->availableTenants);
    }

    public function resendInvitation(int $id): void
    {
        $invitation = TenantInvitation::findOrFail($id);
        $this->authorize('update', $invitation);

        try {
            app(TenantInvitationService::class)->resendInvitation($invitation);
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
            return;
        }

        Flux::toast('Invitation resent with a new link.', 'success');
        unset($this->invitations);
    }

    public function cancelInvitation(int $id): void
    {
        $invitation = TenantInvitation::findOrFail($id);
        $this->authorize('update', $invitation);

        try {
            app(TenantInvitationService::class)->cancelInvitation($invitation);
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
            return;
        }

        Flux::toast('Invitation cancelled.', 'success');
        unset($this->invitations);
    }

    public function deleteInvitation(int $id): void
    {
        $invitation = TenantInvitation::findOrFail($id);
        $this->authorize('delete', $invitation);

        $invitation->delete();

        Flux::toast('Invitation deleted.', 'success');
        unset($this->invitations);
    }

    public function copyLink(int $id): void
    {
        $invitation = TenantInvitation::findOrFail($id);
        $this->authorize('view', $invitation);

        $this->copiedId = $id;
        Flux::toast('Link: ' . $invitation->accept_url, 'success');
    }

    public function render()
    {
        return view('livewire.tenant-invitations.index')
            ->layout('layouts.app')
            ->title(__('Tenant Invitations'));
    }
}