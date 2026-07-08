<?php

namespace App\Livewire\Leases;

use App\Models\Contract;
use App\Models\Lease;
use App\Services\ContractService;
use App\Services\LeaseService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Lease $lease;

    public function mount(Lease $lease): void
    {
        $this->authorize('view', $lease);

        $this->lease = $lease->load(['property', 'unit', 'tenant', 'landlord']);
    }

    // ── Computed data for each tab ───────────────────────────────────────────

    #[Computed]
    public function contract(): ?Contract
    {
        return $this->lease->contracts()
            ->with(['signatures', 'document'])
            ->latest()
            ->first();
    }

    #[Computed]
    public function invoices()
    {
        return $this->lease->invoices()
            ->with(['currency'])
            ->orderByDesc('due_date')
            ->get();
    }

    #[Computed]
    public function payments()
    {
        return $this->lease->payments()
            ->with(['currency', 'confirmer:id,name'])
            ->orderByDesc('payment_date')
            ->get();
    }

    #[Computed]
    public function documents()
    {
        return $this->lease->documents()
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $invoices = $this->invoices;
        $payments = $this->payments;

        $totalInvoiced  = $invoices->sum('amount');
        $totalPaid      = $payments->where('status', 'confirmed')->sum('amount');
        $outstanding    = max(0, $totalInvoiced - $totalPaid);
        $pendingCount   = $invoices->whereIn('status', ['unpaid', 'partially_paid', 'overdue', 'sent'])->count();

        return compact('totalInvoiced', 'totalPaid', 'outstanding', 'pendingCount');
    }

    #[Computed]
    public function history(): array
    {
        $events = [];

        $events[] = [
            'date'  => $this->lease->created_at,
            'icon'  => 'document-text',
            'color' => 'zinc',
            'label' => __('Lease created'),
        ];

        $contract = $this->contract;

        if ($contract) {
            $events[] = [
                'date'  => $contract->created_at,
                'icon'  => 'pencil-square',
                'color' => 'blue',
                'label' => __('Contract generated — :title', ['title' => $contract->title]),
            ];

            if ($contract->sent_at) {
                $events[] = [
                    'date'  => $contract->sent_at,
                    'icon'  => 'paper-airplane',
                    'color' => 'amber',
                    'label' => __('Contract sent for signature'),
                ];
            }

            foreach ($contract->signatures as $sig) {
                if ($sig->isSigned()) {
                    $events[] = [
                        'date'  => $sig->signed_at,
                        'icon'  => 'check-badge',
                        'color' => 'green',
                        'label' => __(':name signed (:role)', [
                            'name' => $sig->name,
                            'role' => $sig->role_label,
                        ]),
                    ];
                }
            }

            if ($contract->isCompleted() && $contract->completed_at) {
                $events[] = [
                    'date'  => $contract->completed_at,
                    'icon'  => 'check-circle',
                    'color' => 'green',
                    'label' => __('Contract completed — all parties signed'),
                ];
            }

            if ($contract->isCancelled()) {
                $events[] = [
                    'date'  => $contract->updated_at,
                    'icon'  => 'x-circle',
                    'color' => 'red',
                    'label' => __('Contract cancelled'),
                ];
            }
        }

        usort($events, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $events;
    }

    // ── Contract lifecycle actions ───────────────────────────────────────────

    public function sendContract(): void
    {
        $contract = $this->contract;
        abort_if(! $contract, 404);
        $this->authorize('update', $contract);

        app(ContractService::class)->send($contract);
        unset($this->contract);

        Flux::toast(__('Contract sent for signature.'), 'success');
    }

    public function cancelContract(): void
    {
        $contract = $this->contract;
        abort_if(! $contract, 404);
        $this->authorize('update', $contract);

        app(ContractService::class)->cancel($contract);
        unset($this->contract);

        Flux::toast(__('Contract cancelled.'), 'success');
    }

    public function resendSignature(int $signatureId): void
    {
        $contract = $this->contract;
        abort_if(! $contract, 404);
        $this->authorize('update', $contract);

        $sig = $contract->signatures()->whereKey($signatureId)->firstOrFail();

        if ($sig->status !== 'pending' || blank($sig->email) || ! $contract->isSent()) {
            return;
        }

        app(ContractService::class)->sendSignatureRequest($sig);

        Flux::toast(__('Signing link emailed to :name.', ['name' => $sig->name]), 'success');
    }

    public function signingUrl(string $token): string
    {
        return route('contracts.sign', $token);
    }

    // ── Lease lifecycle actions ──────────────────────────────────────────────

    public function endLease(): void
    {
        $this->authorize('update', $this->lease);

        app(LeaseService::class)->endLease($this->lease);
        $this->lease->refresh();

        Flux::toast(__('Lease ended.'), 'success');
    }

    public function cancelLease(): void
    {
        $this->authorize('update', $this->lease);

        app(LeaseService::class)->cancelLease($this->lease);
        $this->lease->refresh();

        Flux::toast(__('Lease cancelled.'), 'success');
    }

    public function render()
    {
        return view('livewire.leases.show')
            ->layout('layouts.app')
            ->title($this->lease->lease_number);
    }
}
