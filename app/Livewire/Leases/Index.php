<?php

namespace App\Livewire\Leases;

use App\Models\Lease;
use App\Services\LeaseService;
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
    public function leases()
    {
        $query = Lease::query()
            ->with(['property:id,name', 'unit:id,unit_number', 'tenant:id,first_name,last_name'])
            ->when($this->search, function ($q) {
                $q->whereHas('tenant', function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%");
                })->orWhereHas('property', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('unit', function ($q) {
                    $q->where('unit_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    public function endLease(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('update', $lease);

        app(LeaseService::class)->endLease($lease);

        unset($this->leases);

        Flux::toast('Lease ended. Unit marked as vacant.', 'success');
    }

    public function cancelLease(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('update', $lease);

        app(LeaseService::class)->cancelLease($lease);

        unset($this->leases);

        Flux::toast('Lease cancelled. Unit marked as vacant.', 'success');
    }

    public function delete(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('delete', $lease);

        app(LeaseService::class)->deleteLease($lease);

        unset($this->leases);

        Flux::toast('Lease deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.leases.index')
            ->layout('layouts.app')
            ->title(__('Leases'));
    }
}