<?php

namespace App\Console\Commands;

use App\Models\RentInvoice;
use App\Services\RentInvoiceService;
use Illuminate\Console\Command;

class ApplyLateFees extends Command
{
    protected $signature   = 'kirada:apply-late-fees';
    protected $description = 'Apply late fee line items to overdue invoices past their grace period.';

    public function handle(RentInvoiceService $service): int
    {
        $applied = 0;
        $skipped = 0;

        RentInvoice::with(['lease', 'tenant.user', 'lineItems'])
            ->actionable()
            ->whereHas('lease', fn ($q) => $q->where('status', 'active')
                ->where('late_fee_type', '!=', 'none'))
            ->each(function (RentInvoice $invoice) use ($service, &$applied, &$skipped) {
                if ($service->applyLateFee($invoice)) {
                    $applied++;
                    $this->line("  Applied late fee to {$invoice->invoice_number}");
                } else {
                    $skipped++;
                }
            });

        $this->info("Done. Fees applied: {$applied}, Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
