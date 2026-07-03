<?php

namespace App\Livewire\RentPayments;

use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Notifications\TenantPaymentSubmitted;
use App\Services\RentInvoiceService;
use App\Services\RentPaymentService;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tenant-facing "I paid" form: reports a payment on their own invoice as
 * *pending*, feeding the existing landlord confirm/reject flow.
 */
class Submit extends Component
{
    use WithFileUploads;

    public RentInvoice $rentInvoice;

    public string $amount = '';

    public string $method = 'mobile_money';

    public ?string $reference_number = null;

    public $proof = null;

    public ?string $notes = null;

    public float $remaining = 0;

    public string $paymentReference = '';

    public function mount(RentInvoice $rentInvoice): void
    {
        // Only the tenant this invoice is addressed to may report a payment.
        $ownsInvoice = Tenant::where('user_id', auth()->id())
            ->where('id', $rentInvoice->tenant_id)
            ->exists();

        abort_unless(auth()->user()->hasRole('tenant') && $ownsInvoice, 403);
        abort_if(\in_array($rentInvoice->status, ['paid', 'cancelled', 'draft'], true), 403);

        $this->rentInvoice = $rentInvoice;
        $this->remaining = app(RentPaymentService::class)->getRemainingAmount($rentInvoice);
        $this->amount = (string) $this->remaining;
        $this->paymentReference = app(RentInvoiceService::class)->ensurePaymentReference($rentInvoice);
    }

    protected function rules(): array
    {
        return [
            'amount' => "required|numeric|min:1|max:{$this->remaining}",
            'method' => 'required|in:cash,bank_transfer,mobile_money,check,other',
            'reference_number' => 'nullable|string|max:255',
            'proof' => 'nullable|file|max:5120',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $service = app(RentPaymentService::class);

        $data = $service->dataFromInvoice($this->rentInvoice);
        $data['landlord_id'] = $this->rentInvoice->landlord_id;
        $data['amount'] = $validated['amount'];
        $data['method'] = $validated['method'];
        $data['reference_number'] = $validated['reference_number'];
        $data['notes'] = $validated['notes'];
        $data['payment_date'] = now()->format('Y-m-d');
        $data['status'] = 'pending';

        try {
            $payment = $service->createPayment($data, $this->proof);
        } catch (\DomainException $e) {
            $this->addError('amount', $e->getMessage());

            return;
        }

        $this->rentInvoice->landlord?->notify(new TenantPaymentSubmitted($payment));

        Flux::toast(__('Payment reported. Your landlord will confirm it shortly.'), 'success');

        $this->redirect(route('rent-invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rent-payments.submit')
            ->layout('layouts.app')
            ->title(__('Report a Payment'));
    }
}
