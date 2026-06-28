<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Services\ContractService;
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
    public function contracts()
    {
        $query = Contract::query()
            ->with(['tenant:id,first_name,last_name'])
            ->withCount(['signatures', 'signatures as signed_signatures_count' => fn ($q) => $q->where('status', 'signed')])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('reference', 'like', "%{$this->search}%")
                        ->orWhere('title', 'like', "%{$this->search}%")
                        ->orWhereHas('tenant', function ($q) {
                            $q->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    public function send(int $id): void
    {
        $contract = Contract::findOrFail($id);
        $this->authorize('update', $contract);

        app(ContractService::class)->send($contract);
        unset($this->contracts);

        Flux::toast(__('Contract sent for signature.'), 'success');
    }

    public function cancel(int $id): void
    {
        $contract = Contract::findOrFail($id);
        $this->authorize('update', $contract);

        app(ContractService::class)->cancel($contract);
        unset($this->contracts);

        Flux::toast(__('Contract cancelled.'), 'success');
    }

    public function render()
    {
        return view('livewire.contracts.index')
            ->layout('layouts.app')
            ->title(__('Contracts'));
    }
}
