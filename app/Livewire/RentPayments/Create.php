<?php

namespace App\Livewire\RentPayments;

use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

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

    // Auto-filled display fields
    public ?string $invoice_number = null;
    public ?string $remaining_amount = null;
    public ?string $property_name = null;
    public ?string $unit_number = null;
    public ?string $tenant_name = null;

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
            'proof'            => 'nullable|file|max:5120', // 5MB max
            'notes'            => 'nullable|string|max:2000',
        ];
    }

    #[Computed]
    public function invoices()
    {
        $query = RentInvoice::with(['tenant:id,first_name,last_name', 'property:id,name', 'unit:id,unit_number'])
            ->select('id', 'invoice_number', 'lease_id', 'property_id', 'unit_id', 'tenant_id', 'amount', 'status')
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    /**
     * When an invoice is selected, auto-fill all related fields.
     */
    public function updatedRentInvoiceId(): void
    {
        if (!$this->rent_invoice_id) {
            return;
        }

        $invoice = $this->invoices->firstWhere('id', $this->rent_invoice_id);

        if (!$invoice) {
            return;
        }

        $data = app(RentPaymentService::class)->dataFromInvoice($invoice);

        $this->lease_id = $data['lease_id'];
        $this->property_id = $data['property_id'];
        $this->unit_id = $data['unit_id'];
        $this->tenant_id = $data['tenant_id'];
        $this->amount = (string) $data['amount'];

        $this->invoice_number = $invoice->invoice_number;
        $this->remaining_amount = number_format($data['amount'], 0) . ' DJF';
        $this->property_name = $invoice->property?->name;
        $this->unit_number = $invoice->unit?->unit_number;
        $this->tenant_name = trim(($invoice->tenant?->first_name ?? '') . ' ' . ($invoice->tenant?->last_name ?? ''));

        if (empty($this->payment_date)) {
            $this->payment_date = now()->format('Y-m-d');
        }
    }

    public function save(): void
    {
        $this->authorize('create', RentPayment::class);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $invoice = RentInvoice::find($validated['rent_invoice_id']);
            abort_if($invoice->landlord_id !== auth()->id(), 403);
        }

        $data = collect($validated)->except('proof')->toArray();

        $data['landlord_id'] = auth()->user()->hasRole('admin')
            ? RentInvoice::find($validated['rent_invoice_id'])->landlord_id
            : auth()->id();

        // Handle confirmed_by if status is confirmed
        if ($data['status'] === 'confirmed') {
            $data['confirmed_at'] = now();
            $data['confirmed_by'] = auth()->id();
        }

        try {
            app(RentPaymentService::class)->createPayment($data, $this->proof);
        } catch (\DomainException $e) {
            $this->addError('amount', $e->getMessage());
            return;
        }

        \Flux\Flux::toast('Payment recorded successfully.', 'success');

        $this->redirect(route('rent-payments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rent-payments.create')
            ->layout('layouts.app')
            ->title(__('Record Payment'));
    }
}
