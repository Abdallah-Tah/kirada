<?php

namespace App\Livewire\Contracts;

use App\Models\ContractSignature;
use App\Services\ContractService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Sign extends Component
{
    #[Locked]
    public int $signatureId;

    public string $signatureData = '';

    public string $typedName = '';

    public bool $agreed = false;

    public bool $justSigned = false;

    public function mount(string $token): void
    {
        $signature = ContractSignature::where('token', $token)
            ->with('contract.signatures')
            ->firstOrFail();

        abort_if(! $signature->contract?->isSent(), 403, __('This contract is not available for signature.'));
        abort_if($signature->isExpired(), 403, __('This signing link has expired. Please request a new link.'));

        $this->signatureId = $signature->id;
        $this->typedName = $signature->name;
    }

    #[Computed]
    public function signature(): ContractSignature
    {
        return ContractSignature::with('contract.signatures')->findOrFail($this->signatureId);
    }

    public function sign(): void
    {
        $signature = $this->signature;
        $contract = $signature->contract;

        abort_if($signature->status !== 'pending', 403, __('This signature is no longer pending.'));
        abort_if(! $contract->isSent(), 403, __('This contract is not available for signature.'));
        abort_if($signature->isExpired(), 403, __('This signing link has expired. Please request a new link.'));

        $this->validate([
            'signatureData' => ['required', 'string', 'starts_with:data:image/', 'max:2000000'],
            'typedName' => ['required', 'string', 'max:200'],
            'agreed' => ['accepted'],
        ], [
            'signatureData.required' => __('Please draw your signature before signing.'),
            'signatureData.starts_with' => __('The signature image is invalid.'),
            'signatureData.max' => __('The signature image is too large.'),
            'agreed.accepted' => __('You must consent to sign electronically.'),
        ]);

        app(ContractService::class)->recordSignature(
            $signature,
            $this->signatureData,
            request()->ip(),
            request()->userAgent(),
            $this->typedName,
        );

        $this->justSigned = true;
        $this->reset('signatureData', 'agreed');

        // Drop the cached computed so the view re-reads the freshly updated state.
        unset($this->signature);
    }

    public function render()
    {
        return view('livewire.contracts.sign', [
            'signature' => $this->signature,
            'contract' => $this->signature->contract,
        ])
            ->layout('layouts.public')
            ->title(__('Sign contract'));
    }
}
