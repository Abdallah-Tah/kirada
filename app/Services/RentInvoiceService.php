<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\RentInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RentInvoiceService
{
    /**
     * Generate a unique invoice number: INV-YYYYMM-XXXX
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        $latest = RentInvoice::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        $sequence = $latest
            ? (int) Str::after($latest->invoice_number, $prefix) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
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
            'lease_id'    => $lease->id,
            'property_id' => $lease->property_id,
            'unit_id'     => $lease->unit_id,
            'tenant_id'   => $lease->tenant_id,
            'amount'      => $lease->monthly_rent,
            'invoice_month' => $month->startOfMonth()->format('Y-m-d'),
            'due_date'    => $dueDate->format('Y-m-d'),
        ];
    }

    /**
     * Create an invoice, preventing duplicates for the same lease + month.
     */
    public function createInvoice(array $data): RentInvoice
    {
        $exists = RentInvoice::where('lease_id', $data['lease_id'])
            ->where('invoice_month', $data['invoice_month'])
            ->exists();

        if ($exists) {
            throw new \DomainException('An invoice already exists for this lease and month.');
        }

        $data['invoice_number'] = $this->generateInvoiceNumber();

        if (!isset($data['status']) || empty($data['status'])) {
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
}