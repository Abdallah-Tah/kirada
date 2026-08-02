<?php

namespace App\Livewire\RentPayments;

use App\Models\LandlordPayoutAccount;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Notifications\TenantPaymentSubmitted;
use App\Services\RentInvoiceService;
use App\Services\RentPaymentService;
use App\Support\Locales;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
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

    public ?int $landlord_payout_account_id = null;

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
        $this->landlord_payout_account_id = LandlordPayoutAccount::query()
            ->where('landlord_id', $rentInvoice->landlord_id)
            ->active()
            ->orderByDesc('is_primary')
            ->value('id');
    }

    protected function rules(): array
    {
        $accountRule = $this->payoutAccounts->isNotEmpty() ? 'required' : 'nullable';

        return [
            'amount' => "required|numeric|min:1|max:{$this->remaining}",
            'method' => 'required|in:cash,bank_transfer,mobile_money,check,other',
            'landlord_payout_account_id' => [
                $accountRule,
                'integer',
                'exists:landlord_payout_accounts,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value && ! LandlordPayoutAccount::query()
                        ->whereKey($value)
                        ->where('landlord_id', $this->rentInvoice->landlord_id)
                        ->active()
                        ->exists()) {
                        $fail(__('The selected landlord payment account is not available.'));
                    }
                },
            ],
            'reference_number' => 'required_unless:method,cash|string|max:255',
            'proof' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png,image/webp|max:5120',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($validated['landlord_payout_account_id']) {
            $account = LandlordPayoutAccount::findOrFail($validated['landlord_payout_account_id']);
            $validated['method'] = match ($account->method) {
                'd_money', 'waafi' => 'mobile_money',
                'cac_bank', 'bank_transfer' => 'bank_transfer',
                'cash' => 'cash',
                default => 'other',
            };
        }

        $service = app(RentPaymentService::class);

        $data = $service->dataFromInvoice($this->rentInvoice);
        $data['landlord_id'] = $this->rentInvoice->landlord_id;
        $data['amount'] = $validated['amount'];
        $data['method'] = $validated['method'];
        $data['landlord_payout_account_id'] = $validated['landlord_payout_account_id'];
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

        $this->rentInvoice->landlord?->notify(
            (new TenantPaymentSubmitted($payment))->locale(Locales::forLandlord($this->rentInvoice->landlord)),
        );

        Flux::toast(text: __('Payment reported. Your landlord will confirm it shortly.'), variant: 'success');

        $this->redirect(route('rent-invoices.index'), navigate: true);
    }

    public function updatedLandlordPayoutAccountId(?int $accountId): void
    {
        $account = $accountId ? $this->payoutAccounts->firstWhere('id', $accountId) : null;

        if ($account) {
            $this->method = match ($account->method) {
                'd_money', 'waafi' => 'mobile_money',
                'cac_bank', 'bank_transfer' => 'bank_transfer',
                'cash' => 'cash',
                default => 'other',
            };
        }
    }

    /**
     * @return Collection<int, LandlordPayoutAccount>
     */
    #[Computed]
    public function payoutAccounts(): Collection
    {
        return LandlordPayoutAccount::query()
            ->where('landlord_id', $this->rentInvoice->landlord_id)
            ->active()
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.rent-payments.submit')
            ->layout('layouts.app')
            ->title(__('Report a Payment'));
    }
}
