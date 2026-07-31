<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceQuote;
use App\Services\BrandedPdfService;
use Illuminate\Http\Response;

class MaintenancePdfController extends Controller
{
    public function __construct(private BrandedPdfService $pdf) {}

    public function __invoke(MaintenanceQuote $maintenanceQuote): Response
    {
        $maintenanceQuote->load([
            'items',
            'currency',
            'maintenanceUser',
            'maintenanceRequest.landlord',
            'maintenanceRequest.tenant',
            'maintenanceRequest.property.currency',
            'maintenanceRequest.unit',
        ]);

        $this->authorize('view', $maintenanceQuote->maintenanceRequest);

        $isInvoice = $maintenanceQuote->isInvoiced();
        $documentDate = $isInvoice
            ? ($maintenanceQuote->invoiced_at ?? $maintenanceQuote->updated_at)
            : $maintenanceQuote->created_at;
        $filenamePrefix = $isInvoice ? 'maintenance-invoice-' : 'maintenance-quote-';

        $pdf = $this->pdf->render(
            'maintenance.quote-pdf',
            [
                'quote' => $maintenanceQuote,
                'isInvoice' => $isInvoice,
            ],
            $maintenanceQuote->reference,
            $documentDate,
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filenamePrefix.$maintenanceQuote->reference.'.pdf"',
        ]);
    }
}
