<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseReceiptController extends Controller
{
    public function __invoke(Expense $expense): StreamedResponse
    {
        $this->authorize('view', $expense);

        abort_unless($expense->receipt_path && Storage::disk('private')->exists($expense->receipt_path), 404);

        return Storage::disk('private')->download(
            $expense->receipt_path,
            $expense->receipt_original_filename ?: basename($expense->receipt_path),
            ['Content-Type' => $expense->receipt_mime_type ?: 'application/octet-stream'],
        );
    }
}
