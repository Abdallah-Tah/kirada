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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function messages()
    {
        $query = WhatsAppMessage::query()->with('landlord:id,name')->latest('received_at');

        if (! auth()->user()->hasRole('admin')) {
            $query->where('landlord_id', auth()->id());
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

        if (! auth()->user()->hasRole('admin') && $message->landlord_id !== auth()->id()) {
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
