<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\MaintenanceRequest;
use App\Services\MaintenanceRequestService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterPriority = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPriority(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function requests()
    {
        $user = auth()->user();
        $query = MaintenanceRequest::query()
            ->with([
                'property:id,name',
                'unit:id,unit_number',
                'tenant:id,first_name,last_name',
                'assignee:id,name',
                'reporter:id,name',
            ])
            ->when($this->search, function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhereHas('property', function ($q) {
                      $q->where('name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('tenant', function ($q) {
                      $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->latest();

        // Role-based scoping
        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = \App\Models\Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->forTenant($tenant->id);
            } else {
                // No tenant record = no requests
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('maintenance')) {
            $query->assignedTo($user->id);
        }
        // Admin sees all

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.maintenance-requests.index')
            ->layout('layouts.app')
            ->title(__('Maintenance Requests'));
    }
}