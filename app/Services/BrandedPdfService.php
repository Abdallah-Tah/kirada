<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class BrandedPdfService
{
    /**
     * Render a Kirada-branded A4 document with a consistent French footer and
     * Dompdf Canvas page numbering. The caller retains ownership of filenames,
     * authorization, persistence, and response behavior.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(
        string $view,
        array $data,
        string $reference,
        ?\DateTimeInterface $documentDate = null,
        string $locale = 'fr',
        bool $includeGeneratedFooter = true,
    ): string {
        $previousLocale = App::currentLocale();
        $generatedAt = now();

        App::setLocale($locale);

        try {
            $pdf = Pdf::loadView($view, [
                ...$data,
                'pdfGeneratedAt' => $generatedAt,
                'pdfLogoPath' => public_path('brand/kirada-logo-transparent.png'),
                'pdfSupportEmail' => config('mail.from.address'),
                'pdfDocumentDate' => $documentDate?->format('d/m/Y'),
            ])->setPaper('a4', 'portrait');

            $pdf->render();

            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
            $date = $generatedAt->format('d/m/Y');
            $time = $generatedAt->format('H:i');
            $footer = __('pdf.footer.generated', ['date' => $date, 'time' => $time]);
            $page = __('pdf.footer.page', ['current' => '{PAGE_NUM}', 'total' => '{PAGE_COUNT}']);

            if ($includeGeneratedFooter) {
                $canvas->page_text(40, 818, "{$footer} — {$reference}", $font, 7.5, [0.39, 0.46, 0.56]);
            }

            $canvas->page_text(482, 818, $page, $font, 7.5, [0.39, 0.46, 0.56]);

            return $pdf->output();
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
