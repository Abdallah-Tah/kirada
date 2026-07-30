<?php

namespace App\Livewire\Audit;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $event = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEvent(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user->can('audit.view'), 403);

        $events = AuditEvent::query()
            ->with('actor:id,name,email')
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where(
                'landlord_id',
                $user->landlordAccountId(),
            ))
            ->when($this->event !== '', fn (Builder $query) => $query->where('event', $this->event))
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('auditable_type', 'like', $term)
                        ->orWhere('route_name', 'like', $term)
                        ->orWhereHas('actor', fn (Builder $actor) => $actor
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term));
                });
            })
            ->latest('id')
            ->paginate(20);

        return view('livewire.audit.index', compact('events'))
            ->layout('layouts.app')
            ->title(__('Audit Center'));
    }
}
