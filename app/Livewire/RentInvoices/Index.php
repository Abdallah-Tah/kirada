<?php

namespace App\Livewire\RentInvoices;

use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Services\RentInvoiceService;
use Flux\Flux;
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
    public function invoices()
    {
        $query = RentInvoice::query()
            ->with(['property:id,name,currency_id', 'property.currency', 'currency', 'unit:id,unit_number', 'tenant:id,first_name,last_name', 'lease:id,start_date,end_date'])
            ->when($this->search, function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('tenant', function ($q) {
                        $q->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('property', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        } elseif (auth()->user()->hasRole('tenant')) {
            // "My Rent": tenants only see invoices addressed to their own
            // tenant record(s); a user with no tenant record sees nothing.
            $tenantIds = Tenant::where('user_id', auth()->id())->pluck('id');
            $query->whereIn('tenant_id', $tenantIds);
        }

        return $query->paginate(10);
    }

    public function markOverdue(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'landlord']), 403);

        $count = app(RentInvoiceService::class)->markOverdue();

        unset($this->invoices);

        Flux::toast("{$count} invoice(s) marked as overdue.", 'success');
    }

    public function delete(int $id): void
    {
        $invoice = RentInvoice::findOrFail($id);

        $this->authorize('delete', $invoice);

        app(RentInvoiceService::class)->deleteInvoice($invoice);

        unset($this->invoices);

        Flux::toast('Invoice deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.rent-invoices.index')
            ->layout('layouts.app')
            ->title(__('Rent Invoices'));
    }
}
