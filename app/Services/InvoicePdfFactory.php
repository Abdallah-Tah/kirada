<?php

namespace App\Services;

use App\Models\RentInvoice;

class InvoicePdfFactory
{
    public function __construct(private BrandedPdfService $pdf) {}

    public function make(RentInvoice $invoice): string
    {
        $invoice->loadMissing([
            'tenant',
            'property',
            'unit',
            'landlord.payoutAccounts',
            'currency',
            'lineItems',
        ]);

        return $this->pdf->render(
            'receipts.invoice',
            ['invoice' => $invoice],
            $invoice->invoice_number,
            $invoice->created_at,
            includeGeneratedFooter: false,
        );
    }
}
