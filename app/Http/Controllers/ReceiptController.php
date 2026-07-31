<?php

namespace App\Http\Controllers;

use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Services\BrandedPdfService;
use App\Services\InvoicePdfFactory;
use App\Services\RentInvoiceService;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    public function __construct(
        private BrandedPdfService $pdf,
        private InvoicePdfFactory $invoicePdf,
    ) {}

    /**
     * Download a PDF receipt for a confirmed payment.
     */
    public function paymentReceipt(RentPayment $rentPayment): Response
    {
        $this->authorize('view', $rentPayment);

        abort_unless($rentPayment->isConfirmed(), 404);

        $rentPayment->load(['rentInvoice', 'tenant', 'property', 'unit', 'landlord', 'currency', 'confirmer']);

        $pdf = $this->pdf->render(
            'receipts.payment-receipt',
            ['payment' => $rentPayment],
            $rentPayment->payment_number,
            $rentPayment->payment_date,
        );

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

        $pdf = $this->invoicePdf->make($rentInvoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$rentInvoice->invoice_number.'.pdf"',
        ]);
    }
}
