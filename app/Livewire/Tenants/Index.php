<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tenants()
    {
        $query = Tenant::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    public function delete(int $id): void
    {
        $tenant = Tenant::findOrFail($id);

        $this->authorize('delete', $tenant);

        $tenant->delete();

        unset($this->tenants);

        \Flux\Flux::toast('Tenant deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.tenants.index')
            ->layout('layouts.app')
            ->title(__('Tenants'));
    }
}
