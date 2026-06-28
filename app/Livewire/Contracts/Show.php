<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Services\ContractService;
use Livewire\Component;

class Show extends Component
{
    public Contract $contract;

    public function mount(Contract $contract): void
    {
        $this->authorize('view', $contract);

        $this->contract = $contract->load(['signatures', 'tenant', 'lease', 'document']);
    }

    public function send(): void
    {
        $this->authorize('update', $this->contract);

        app(ContractService::class)->send($this->contract);
        $this->refreshContract();

        Flux::toast(__('Contract sent for signature.'), 'success');
    }

    public function cancel(): void
    {
        $this->authorize('update', $this->contract);

        app(ContractService::class)->cancel($this->contract);
        $this->refreshContract();

        Flux::toast(__('Contract cancelled.'), 'success');
    }

    public function signingUrl(string $token): string
    {
        return route('contracts.sign', $token);
    }

    protected function refreshContract(): void
    {
        $this->contract = $this->contract->fresh(['signatures', 'tenant', 'lease', 'document']);
    }

    public function render()
    {
        return view('livewire.contracts.show')
            ->layout('layouts.app')
            ->title($this->contract->reference);
    }
}
