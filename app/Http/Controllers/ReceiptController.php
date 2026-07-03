<?php

namespace App\Http\Controllers;

use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Services\RentInvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    /**
     * Download a PDF receipt for a confirmed payment.
     */
    public function paymentReceipt(RentPayment $rentPayment): Response
    {
        $this->authorize('view', $rentPayment);

        abort_unless($rentPayment->isConfirmed(), 404);

        $rentPayment->load(['rentInvoice', 'tenant', 'property', 'unit', 'landlord', 'currency', 'confirmer']);

        $pdf = Pdf::loadView('receipts.payment-receipt', ['payment' => $rentPayment])
            ->setPaper('a4')
            ->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-'.$rentPayment->payment_number.'.pdf"',
        ]);
    }

    /**
     * Download a rent invoice as a PDF.
     */
    public function invoicePdf(RentInvoice $rentInvoice): Response
    {
        $this->authorize('view', $rentInvoice);

        app(RentInvoiceService::class)->ensurePaymentReference($rentInvoice);

        $rentInvoice->load(['tenant', 'property', 'unit', 'landlord', 'currency', 'lineItems']);

        $pdf = Pdf::loadView('receipts.invoice', ['invoice' => $rentInvoice])
            ->setPaper('a4')
            ->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$rentInvoice->invoice_number.'.pdf"',
        ]);
    }
}
