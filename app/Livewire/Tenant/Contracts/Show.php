<?php

namespace App\Livewire\Tenant\Contracts;

use App\Models\Contract;
use App\Models\ContractSignature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A renter's read-only view of one contract: the terms they agreed to, who has
 * signed, their own signing link while it is still pending, and the archived
 * PDF once every party has signed.
 */
class Show extends Component
{
    #[Locked]
    public int $contractId;

    public function mount(Contract $contract): void
    {
        // Deliberately stricter than `view`: this screen is the renter's copy.
        // Landlords and admins manage contracts on their own screen, which
        // carries every signer's private link.
        $this->authorize('isCounterparty', $contract);

        $this->contractId = $contract->id;
    }

    #[Computed]
    public function contract(): Contract
    {
        return Contract::with(['signatures', 'property:id,name', 'unit:id,unit_number', 'document'])
            ->findOrFail($this->contractId);
    }

    /**
     * This renter's own pending signature, if the contract still needs it.
     */
    #[Computed]
    public function pendingSignature(): ?ContractSignature
    {
        return $this->contract->signatures
            ->firstWhere(fn (ContractSignature $s) => $s->party_role === 'preneur'
                && $s->status === 'pending'
                && ! $s->isExpired());
    }

    public function render()
    {
        return view('livewire.tenant.contracts.show', [
            'contract' => $this->contract,
        ])
            ->layout('layouts.app')
            ->title($this->contract->title);
    }
}
