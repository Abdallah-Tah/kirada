<?php

namespace App\Livewire\RentPayments;

use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public RentPayment $payment;
    public ?int $rent_invoice_id = null;
    public ?int $lease_id = null;
    public ?int $property_id = null;
    public ?int $unit_id = null;
    public ?int $tenant_id = null;
    public string $payment_date = '';
    public string $amount = '';
    public string $method = 'cash';
    public string $status = 'pending';
    public ?string $reference_number = null;
    public $proof = null;
    public ?string $notes = null;

    public ?string $existing_proof = null;

    public function mount(RentPayment $payment): void
    {
        $this->authorize('update', $payment);

        $this->payment = $payment;
        $this->fill($payment->only([
            'rent_invoice_id', 'lease_id', 'property_id', 'unit_id', 'tenant_id',
            'method', 'status', 'reference_number', 'notes',
        ]));

        $this->payment_date = $payment->payment_date?->format('Y-m-d') ?? '';
        $this->amount = (string) $payment->amount;
        $this->existing_proof = $payment->proof_path;
    }

    protected function rules(): array
    {
        return [
            'rent_invoice_id'  => 'required|exists:rent_invoices,id',
            'lease_id'         => 'required|exists:leases,id',
            'property_id'      => 'required|exists:properties,id',
            'unit_id'          => 'required|exists:units,id',
            'tenant_id'        => 'required|exists:tenants,id',
            'payment_date'     => 'required|date',
            'amount'           => 'required|numeric|min:0|max:99999999',
            'method'           => 'required|in:cash,bank_transfer,mobile_money,check,other',
            'status'           => 'required|in:pending,confirmed,rejected',
            'reference_number' => 'nullable|string|max:255',
            'proof'            => 'nullable|file|max:5120',
            'notes'            => 'nullable|string|max:2000',
        ];
    }

    #[Computed]
    public function invoices()
    {
        $query = RentInvoice::with(['tenant:id,first_name,last_name', 'property:id,name', 'unit:id,unit_number'])
            ->select('id', 'invoice_number', 'lease_id', 'property_id', 'unit_id', 'tenant_id', 'amount')
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    public function save(): void
    {
        $this->authorize('update', $this->payment);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $invoice = RentInvoice::find($validated['rent_invoice_id']);
            abort_if($invoice->landlord_id !== auth()->id(), 403);
        }

        $data = collect($validated)->except('proof')->toArray();

        // Handle confirmed_by if status changed to confirmed
        if ($data['status'] === 'confirmed' && $this->payment->status !== 'confirmed') {
            $data['confirmed_at'] = now();
            $data['confirmed_by'] = auth()->id();
        } elseif ($data['status'] !== 'confirmed') {
            $data['confirmed_at'] = null;
            $data['confirmed_by'] = null;
        }

        app(RentPaymentService::class)->updatePayment($this->payment, $data, $this->proof);

        \Flux\Flux::toast('Payment updated successfully.', 'success');

        $this->redirect(route('rent-payments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rent-payments.edit')
            ->layout('layouts.app')
            ->title(__('Edit Payment'));
    }
}
