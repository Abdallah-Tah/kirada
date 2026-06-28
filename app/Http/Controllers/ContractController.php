<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function __construct(private ContractService $contracts) {}

    /**
     * Render the contract as a standalone, printable HTML document
     * (browser "Print → Save as PDF" produces the signed PDF).
     */
    public function print(Contract $contract): Response
    {
        $this->authorize('view', $contract);

        $contract->load(['signatures', 'landlord', 'tenant']);

        return response()->view('contracts.document', [
            'contract' => $contract,
            'finalized' => $contract->isCompleted(),
        ]);
    }

    /**
     * Download the archived signed PDF, or a freshly rendered PDF if the
     * contract has not been finalised yet.
     */
    public function download(Contract $contract): StreamedResponse|Response
    {
        $this->authorize('view', $contract);

        $document = $contract->document;

        if ($document && Storage::disk('private')->exists($document->file_path)) {
            return Storage::disk('private')->download(
                $document->file_path,
                $document->original_filename,
                ['Content-Type' => $document->mime_type],
            );
        }

        // Fallback: render the current state to a PDF so a download is always available.
        $pdf = $this->contracts->renderPdf($contract);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$contract->reference.'.pdf"',
        ]);
    }
}
