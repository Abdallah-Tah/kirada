<?php

namespace App\Livewire\Leases;

use App\Models\Lease;
use App\Services\LeaseService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    #[Url]
    public string $filterExpiry = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterExpiry(): void
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
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterExpiry === '30', fn ($q) => $q->expiringWithin(30))
            ->when($this->filterExpiry === '90', fn ($q) => $q->expiringWithin(90))
            ->when($this->filterExpiry === 'expired', fn ($q) => $q->expired())
            ->latest();

        if (auth()->user()->canAccessLandlordPortal()) {
            $query->forLandlord(auth()->user()->landlordAccountId());
        }

        return $query->paginate(10);
    }

    #[Computed]
    public function renewalSummary(): array
    {
        $query = Lease::query();

        if (auth()->user()->canAccessLandlordPortal()) {
            $query->forLandlord(auth()->user()->landlordAccountId());
        }

        return [
            'expiring_30' => (clone $query)->expiringWithin(30)->count(),
            'expiring_90' => (clone $query)->expiringWithin(90)->count(),
            'expired' => (clone $query)->expired()->count(),
        ];
    }

    public function endLease(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('update', $lease);

        app(LeaseService::class)->endLease($lease);

        unset($this->leases);

        Flux::toast(text: 'Lease ended. Unit marked as vacant.', variant: 'success');
    }

    public function cancelLease(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('update', $lease);

        app(LeaseService::class)->cancelLease($lease);

        unset($this->leases);

        Flux::toast(text: 'Lease cancelled. Unit marked as vacant.', variant: 'success');
    }

    public function delete(int $id): void
    {
        $lease = Lease::findOrFail($id);

        $this->authorize('delete', $lease);

        app(LeaseService::class)->deleteLease($lease);

        unset($this->leases);

        Flux::toast(text: 'Lease deleted.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.leases.index')
            ->layout('layouts.app')
            ->title(__('Leases'));
    }
}
