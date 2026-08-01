<?php

namespace App\Livewire\TenantInvitations;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Services\TenantInvitationService;
use Flux\Flux;
use Illuminate\Validation\Rule;
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

    /** @var array<int, string> */
    public array $deliveryChannels = [];

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

    public function updatedEmail(?string $value): void
    {
        if (blank($value)) {
            $this->deliveryChannels = array_values(array_diff(
                $this->deliveryChannels,
                [TenantInvitationService::CHANNEL_EMAIL],
            ));
        } elseif (! in_array(TenantInvitationService::CHANNEL_EMAIL, $this->deliveryChannels, true)) {
            $this->deliveryChannels[] = TenantInvitationService::CHANNEL_EMAIL;
        }
    }

    public function updatedPhone(?string $value): void
    {
        if (blank($value)) {
            $this->deliveryChannels = array_values(array_diff(
                $this->deliveryChannels,
                [TenantInvitationService::CHANNEL_WHATSAPP],
            ));
        }
    }

    protected function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'deliveryChannels' => ['required', 'array', 'min:1'],
            'deliveryChannels.*' => ['string', Rule::in(TenantInvitationService::CHANNELS)],
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
                $q->where(function ($q) {
                    $q->whereHas('tenant', function ($q) {
                        $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%");
                    })->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->canAccessLandlordPortal()) {
            $query->forLandlord(auth()->user()->landlordAccountId());
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function availableTenants()
    {
        $query = Tenant::select('id', 'first_name', 'last_name', 'phone', 'email')
            ->whereDoesntHave('user')
            ->orderBy('first_name');

        if (auth()->user()->canAccessLandlordPortal()) {
            $query->forLandlord(auth()->user()->landlordAccountId());
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
        if (auth()->user()->canAccessLandlordPortal()) {
            $tenant = Tenant::find($validated['tenant_id']);
            abort_unless(auth()->user()->belongsToLandlordAccount($tenant->landlord_id), 403);
        }

        $landlordId = auth()->user()->hasRole('admin')
            ? Tenant::find($validated['tenant_id'])->landlord_id
            : auth()->user()->landlordAccountId();

        try {
            app(TenantInvitationService::class)->createInvitation(
                $landlordId,
                $validated['tenant_id'],
                $validated['email'],
                $validated['phone'],
                $validated['deliveryChannels'],
            );
        } catch (\DomainException $e) {
            $this->addError('tenant_id', $e->getMessage());

            return;
        }

        Flux::toast(__('Invitation created and delivery queued.'), 'success');

        $this->reset(['tenant_id', 'email', 'phone', 'deliveryChannels']);
        $this->deliveryChannels = [];
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

        Flux::toast(__('Invitation resent and delivery queued.'), 'success');
        unset($this->invitations);
    }

    public function resendWhatsApp(int $id): void
    {
        $invitation = TenantInvitation::findOrFail($id);
        $this->authorize('update', $invitation);

        try {
            app(TenantInvitationService::class)->resendWhatsApp($invitation);
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');

            return;
        }

        Flux::toast(__('WhatsApp invitation queued.'), 'success');
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
        Flux::toast('Link: '.$invitation->accept_url, 'success');
    }

    public function render()
    {
        return view('livewire.tenant-invitations.index')
            ->layout('layouts.app')
            ->title(__('Tenant Invitations'));
    }
}
