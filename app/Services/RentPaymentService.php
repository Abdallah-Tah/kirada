<?php

namespace App\Services;

use App\Models\RentInvoice;
use App\Models\RentPayment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class RentPaymentService
{
    /**
     * Generate a unique payment number: PAY-YYYYMMDD-XXXX
     */
    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-'.now()->format('Ymd').'-';

        $latest = RentPayment::withTrashed()
            ->where('payment_number', 'like', $prefix.'%')
            ->latest('id')
            ->first();

        $sequence = $latest
            ? (int) Str::after($latest->payment_number, $prefix) + 1
            : 1;

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-fill payment data from an invoice.
     */
    public function dataFromInvoice(RentInvoice $invoice): array
    {
        $remaining = $this->getRemainingAmount($invoice, includePending: true);

        return [
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $invoice->lease_id,
            'property_id' => $invoice->property_id,
            'unit_id' => $invoice->unit_id,
            'tenant_id' => $invoice->tenant_id,
            'amount' => $remaining,
        ];
    }

    /**
     * Get the remaining amount on an invoice (total minus confirmed payments).
     */
    public function getRemainingAmount(RentInvoice $invoice, bool $includePending = false): float
    {
        $statuses = $includePending ? ['confirmed', 'pending'] : ['confirmed'];

        $paidTotal = RentPayment::where('rent_invoice_id', $invoice->id)
            ->whereIn('status', $statuses)
            ->sum('amount');

        return max(0, (float) $invoice->totalDue() - (float) $paidTotal);
    }

    /**
     * Create a payment, preventing overpayment on the invoice.
     */
    public function createPayment(array $data, ?UploadedFile $proof = null): RentPayment
    {
        $invoice = RentInvoice::findOrFail($data['rent_invoice_id']);

        $remaining = $this->getRemainingAmount($invoice, includePending: true);

        if ((float) $data['amount'] > $remaining) {
            throw new \DomainException(
                "Payment amount ({$data['amount']}) exceeds remaining invoice balance ({$remaining})."
            );
        }

        $data['payment_number'] = $this->generatePaymentNumber();
        $data['currency_id'] ??= $invoice->currency_id ?? $invoice->property?->currency_id;

        if (! isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'pending';
        }

        if ($proof) {
            $data['proof_path'] = $this->storeProof($proof);
        }

        $payment = RentPayment::create($data);

        if ($payment->isConfirmed()) {
            $this->syncInvoiceStatus($invoice);
        }

        return $payment;
    }

    /**
     * Update a payment and sync invoice status.
     */
    public function updatePayment(RentPayment $payment, array $data, ?UploadedFile $proof = null): RentPayment
    {
        $oldStatus = $payment->status;

        if ($proof) {
            $data['proof_path'] = $this->storeProof($proof);
        }

        $payment->update($data);

        // If amount or status changed, re-sync invoice
        if ($oldStatus !== $payment->status || isset($data['amount'])) {
            $this->syncInvoiceStatus($payment->rentInvoice);
        }

        return $payment->fresh();
    }

    /**
     * Confirm a pending payment.
     */
    public function confirmPayment(RentPayment $payment, int $userId): RentPayment
    {
        $payment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
        ]);

        $this->syncInvoiceStatus($payment->rentInvoice);

        return $payment->fresh();
    }

    /**
     * Reject a pending payment.
     */
    public function rejectPayment(RentPayment $payment): RentPayment
    {
        $wasConfirmed = $payment->isConfirmed();

        $payment->update([
            'status' => 'rejected',
            'confirmed_at' => null,
            'confirmed_by' => null,
        ]);

        if ($wasConfirmed) {
            $this->syncInvoiceStatus($payment->rentInvoice);
        }

        return $payment->fresh();
    }

    /**
     * Delete a payment and re-sync invoice status.
     */
    public function deletePayment(RentPayment $payment): void
    {
        $invoice = $payment->rentInvoice;
        $wasConfirmed = $payment->isConfirmed();

        $payment->delete();

        if ($wasConfirmed) {
            $this->syncInvoiceStatus($invoice);
        }
    }

    /**
     * Sync the invoice status based on confirmed payment totals.
     */
    protected function syncInvoiceStatus(RentInvoice $invoice): void
    {
        $confirmedTotal = RentPayment::where('rent_invoice_id', $invoice->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        if ($confirmedTotal >= (float) $invoice->totalDue()) {
            $invoice->update(['status' => 'paid']);
        } elseif ($confirmedTotal > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } else {
            // Keep overdue if it was overdue, otherwise unpaid
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'unpaid']);
            }
        }
    }

    /**
     * Store a payment proof file.
     */
    protected function storeProof(UploadedFile $file): string
    {
        return $file->store('payment-proofs', 'private');
    }
}
