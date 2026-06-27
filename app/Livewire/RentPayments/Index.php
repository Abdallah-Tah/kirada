<?php

namespace App\Livewire\RentPayments;

use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public ?int $filterInvoice = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function payments()
    {
        $query = RentPayment::query()
            ->with([
                'rentInvoice:id,invoice_number,amount',
                'property:id,name',
                'unit:id,unit_number',
                'tenant:id,first_name,last_name',
                'confirmer:id,name',
            ])
            ->when($this->search, function ($q) {
                $q->where('payment_number', 'like', "%{$this->search}%")
                  ->orWhere('reference_number', 'like', "%{$this->search}%")
                  ->orWhereHas('tenant', function ($q) {
                      $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterInvoice, fn($q) => $q->where('rent_invoice_id', $this->filterInvoice))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    public function confirmPayment(int $id): void
    {
        $payment = RentPayment::findOrFail($id);

        $this->authorize('update', $payment);

        app(RentPaymentService::class)->confirmPayment($payment, auth()->id());

        unset($this->payments);

        Flux::toast('Payment confirmed. Invoice status updated.', 'success');
    }

    public function rejectPayment(int $id): void
    {
        $payment = RentPayment::findOrFail($id);

        $this->authorize('update', $payment);

        app(RentPaymentService::class)->rejectPayment($payment);

        unset($this->payments);

        Flux::toast('Payment rejected.', 'success');
    }

    public function delete(int $id): void
    {
        $payment = RentPayment::findOrFail($id);

        $this->authorize('delete', $payment);

        app(RentPaymentService::class)->deletePayment($payment);

        unset($this->payments);

        Flux::toast('Payment deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.rent-payments.index')
            ->layout('layouts.app')
            ->title(__('Rent Payments'));
    }
}