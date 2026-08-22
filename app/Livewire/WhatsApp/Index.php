<?php

namespace App\Livewire\WhatsApp;

use App\Models\WhatsAppMessage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Show only inbound that matched no tenant. Admin-only, because an
     * unmatched message belongs to no landlord and must not be shown to one on
     * the chance that it is theirs.
     */
    public bool $unmatchedOnly = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingUnmatchedOnly(): void
    {
        $this->resetPage();
    }

    public function isAdmin(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Inbound nobody can action. These are invisible to every landlord, so
     * without a count here they accumulate unnoticed.
     */
    #[Computed]
    public function unmatchedCount(): int
    {
        return $this->isAdmin() ? WhatsAppMessage::query()->unmatched()->count() : 0;
    }

    #[Computed]
    public function messages()
    {
        $query = WhatsAppMessage::query()
            ->with(['landlord:id,name', 'tenant:id,first_name,last_name'])
            ->latest('received_at');

        if (! $this->isAdmin()) {
            $query->where('landlord_id', auth()->id());
        } elseif ($this->unmatchedOnly) {
            $query->unmatched();
        }

        if ($this->search !== '') {
            $query->where(function ($query): void {
                $term = "%{$this->search}%";
                $query->where('from_number', 'like', $term)
                    ->orWhere('profile_name', 'like', $term)
                    ->orWhere('body', 'like', $term);
            });
        }

        return $query->paginate(25);
    }

    public function markAsRead(int $id): void
    {
        $message = WhatsAppMessage::findOrFail($id);

        if (! $this->isAdmin() && $message->landlord_id !== auth()->id()) {
            abort(403);
        }

        $message->update(['read_at' => now()]);
        unset($this->messages);
    }

    public function render()
    {
        return view('livewire.whatsapp.index')
            ->layout('layouts.app', ['title' => __('WhatsApp Inbox')]);
    }
}
