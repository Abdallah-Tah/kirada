<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentInvoiceLineItem;
use App\Notifications\LateFeeApplied;
use App\Notifications\RentInvoiceGenerated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RentInvoiceService
{
    /**
     * Generate a unique invoice number: INV-YYYYMM-XXXX
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';

        $latest = RentInvoice::withTrashed()
            ->where('invoice_number', 'like', $prefix.'%')
            ->latest('id')
            ->first();

        $sequence = $latest
            ? (int) Str::after($latest->invoice_number, $prefix) + 1
            : 1;

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a short unique payment reference tenants quote when paying
     * via mobile money (Waafi / D-Money / CAC Pay): KIR-XXXXXXXX.
     */
    public function generatePaymentReference(): string
    {
        do {
            $reference = 'KIR-'.strtoupper(Str::random(8));
        } while (RentInvoice::withTrashed()->where('payment_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Backfill a payment reference on legacy invoices the first time it is needed.
     */
    public function ensurePaymentReference(RentInvoice $invoice): string
    {
        if (! $invoice->payment_reference) {
            $invoice->update(['payment_reference' => $this->generatePaymentReference()]);
        }

        return $invoice->payment_reference;
    }

    /**
     * The currency an invoice should be denominated in (the property's).
     */
    protected function currencyIdFor(array $data): ?int
    {
        return Property::find($data['property_id'] ?? null)?->currency_id;
    }

    /**
     * Auto-fill invoice data from a lease.
     */
    public function dataFromLease(Lease $lease, ?string $invoiceMonth = null): array
    {
        $month = $invoiceMonth ? Carbon::parse($invoiceMonth) : now();

        $dueDate = $month->copy()
            ->day($lease->payment_due_day)
            ->startOfDay();

        // If due day has passed this month, push to next month
        if ($dueDate->lt(now()->startOfDay())) {
            $dueDate->addMonth();
        }

        return [
            'lease_id' => $lease->id,
            'property_id' => $lease->property_id,
            'unit_id' => $lease->unit_id,
            'tenant_id' => $lease->tenant_id,
            'amount' => $lease->monthly_rent,
            'invoice_month' => $month->startOfMonth()->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
        ];
    }

    /**
     * Create an invoice, preventing duplicates for the same lease + month.
     */
    public function createInvoice(array $data): RentInvoice
    {
        $exists = RentInvoice::where('lease_id', $data['lease_id'])
            ->whereDate('invoice_month', $data['invoice_month'])
            ->exists();

        if ($exists) {
            throw new \DomainException('An invoice already exists for this lease and month.');
        }

        $data['invoice_number'] = $this->generateInvoiceNumber();
        $data['payment_reference'] = $this->generatePaymentReference();
        $data['currency_id'] ??= $this->currencyIdFor($data);

        if (! isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'draft';
        }

        return RentInvoice::create($data);
    }

    /**
     * Update an invoice.
     */
    public function updateInvoice(RentInvoice $invoice, array $data): RentInvoice
    {
        $invoice->update($data);

        return $invoice->fresh();
    }

    /**
     * Mark invoices as overdue if due_date has passed and status is unpaid/partially_paid.
     */
    public function markOverdue(): int
    {
        return RentInvoice::query()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);
    }

    /**
     * Delete an invoice.
     */
    public function deleteInvoice(RentInvoice $invoice): void
    {
        $invoice->delete();
    }

    /**
     * Calculate the next rent due date for a lease from today.
     */
    public function nextDueDate(Lease $lease): Carbon
    {
        $today = Carbon::today();
        $day = min($lease->payment_due_day, $today->daysInMonth);
        $candidate = $today->copy()->day($day)->startOfDay();

        if ($candidate->lte($today)) {
            $candidate->addMonthNoOverflow();
            $candidate->day(min($lease->payment_due_day, $candidate->daysInMonth));
        }

        return $candidate;
    }

    /**
     * Auto-generate an invoice for the upcoming billing period if it is time.
     * Returns the new invoice, or null if skipped (too early / already exists).
     */
    public function generateForLease(Lease $lease): ?RentInvoice
    {
        if (! $lease->isActive() || ! $lease->auto_generate_invoices) {
            return null;
        }

        $nextDue = $this->nextDueDate($lease);
        $invoiceMonth = $nextDue->copy()->startOfMonth()->startOfDay();
        $generateFrom = $nextDue->copy()->subDays($lease->invoice_generation_days_before_due);

        if (Carbon::today()->lt($generateFrom)) {
            return null; // too early
        }

        $exists = RentInvoice::where('lease_id', $lease->id)
            ->whereDate('invoice_month', $invoiceMonth->format('Y-m-d'))
            ->exists();

        if ($exists) {
            return null; // already generated
        }

        $invoice = RentInvoice::create([
            'landlord_id' => $lease->landlord_id,
            'lease_id' => $lease->id,
            'property_id' => $lease->property_id,
            'unit_id' => $lease->unit_id,
            'tenant_id' => $lease->tenant_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'payment_reference' => $this->generatePaymentReference(),
            'invoice_month' => $invoiceMonth->format('Y-m-d'),
            'due_date' => $nextDue->format('Y-m-d'),
            'amount' => $lease->monthly_rent,
            'currency_id' => $lease->property?->currency_id,
            'status' => 'unpaid',
            'is_auto_generated' => true,
            'sent_at' => now(),
        ]);

        // Notify tenant if they have a linked user account
        $tenantUser = $lease->tenant?->user;
        if ($tenantUser) {
            $tenantUser->notify(new RentInvoiceGenerated($invoice));
        }

        return $invoice;
    }

    /**
     * Apply a late fee line item to an invoice if conditions are met.
     * Returns true if a fee was applied.
     */
    public function applyLateFee(RentInvoice $invoice): bool
    {
        $lease = $invoice->lease;

        if (! $lease || $lease->late_fee_type === 'none') {
            return false;
        }

        $graceEnds = $invoice->due_date->copy()->addDays($lease->grace_period_days)->startOfDay();
        if (Carbon::today()->lt($graceEnds)) {
            return false; // still within grace period
        }

        // Check if fee should fire based on frequency
        $lastFee = $invoice->lineItems()
            ->where('type', 'late_fee')
            ->latest()
            ->first();

        if ($lastFee) {
            $nextAllowed = match ($lease->late_fee_frequency) {
                'once' => null,           // never again
                'weekly' => Carbon::parse($lastFee->created_at)->addWeek(),
                'monthly' => Carbon::parse($lastFee->created_at)->addMonthNoOverflow(),
                default => null,
            };

            if ($nextAllowed === null || Carbon::today()->lt($nextAllowed)) {
                return false;
            }
        }

        // Calculate amount
        $feeAmount = $lease->late_fee_type === 'percentage'
            ? round($invoice->amount * $lease->late_fee_amount / 100, 2)
            : (float) $lease->late_fee_amount;

        if ($feeAmount <= 0) {
            return false;
        }

        RentInvoiceLineItem::create([
            'rent_invoice_id' => $invoice->id,
            'type' => 'late_fee',
            'description' => "Late fee — {$invoice->due_date->format('d/m/Y')}",
            'amount' => $feeAmount,
        ]);

        // Ensure invoice status reflects overdue
        if (! \in_array($invoice->status, ['overdue', 'partially_paid'], true)) {
            $invoice->update(['status' => 'overdue']);
        }

        // Notify tenant
        $tenantUser = $lease->tenant?->user;
        if ($tenantUser) {
            $tenantUser->notify(new LateFeeApplied($invoice, $feeAmount));
        }

        return true;
    }
}
