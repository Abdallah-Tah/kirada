<?php

namespace App\Livewire\Messages;

use App\Models\Conversation;
use App\Models\Tenant;
use App\Services\MessagingService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // New conversation form
    public ?int $selectedTenantId = null;
    public string $subject = '';
    public string $firstMessage = '';
    public bool $showNewForm = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function conversations()
    {
        $query = app(MessagingService::class)->getConversationsForUser(auth()->user());

        // Apply search on the paginated query
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('subject', 'like', "%{$this->search}%")
                  ->orWhereHas('tenant', function ($q) {
                      $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('landlord', function ($q) {
                      $q->where('name', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function availableTenants()
    {
        $user = auth()->user();
        $query = Tenant::select('id', 'first_name', 'last_name', 'user_id')
            ->whereNotNull('user_id')
            ->orderBy('first_name');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant && $tenant->landlord_id) {
                $query->where('landlord_id', $tenant->landlord_id)
                      ->where('id', '!=', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->get();
    }

    #[Computed]
    public function currentTenant()
    {
        if (auth()->user()->hasRole('tenant')) {
            return Tenant::where('user_id', auth()->id())->first();
        }
        return null;
    }

    public function startConversation(): void
    {
        $this->validate([
            'selectedTenantId' => 'required|exists:tenants,id',
            'subject'           => 'required|string|max:255',
            'firstMessage'      => 'required|string|max:5000',
        ]);

        $user = auth()->user();
        $tenant = Tenant::findOrFail($this->selectedTenantId);

        // Determine landlord
        $landlordId = $user->hasRole('landlord') ? $user->id : $tenant->landlord_id;

        // Check for existing open conversation with same tenant+landlord
        $existing = Conversation::where('tenant_id', $tenant->id)
            ->where('landlord_id', $landlordId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            \Flux\Flux::toast('An open conversation with this tenant already exists.', 'error');
            return;
        }

        try {
            $conversation = app(MessagingService::class)->startConversation($user, [
                'landlord_id' => $landlordId,
                'tenant_id'   => $tenant->id,
                'subject'     => $this->subject,
            ]);
        } catch (\DomainException $e) {
            $this->addError('selectedTenantId', $e->getMessage());
            return;
        }

        app(MessagingService::class)->sendMessage($conversation, $user, $this->firstMessage);

        \Flux\Flux::toast('Conversation started.', 'success');

        $this->reset(['selectedTenantId', 'subject', 'firstMessage', 'showNewForm']);
        unset($this->conversations);

        $this->redirect(route('messages.show', $conversation), navigate: true);
    }

    public function render()
    {
        return view('livewire.messages.index')
            ->layout('layouts.app')
            ->title(__('Messages'));
    }
}
