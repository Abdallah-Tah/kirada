<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Services\RentInvoiceService;
use Illuminate\Console\Command;

class GenerateRentInvoices extends Command
{
    protected $signature   = 'kirada:generate-rent-invoices';
    protected $description = 'Auto-generate rent invoices for active leases approaching their due date.';

    public function handle(RentInvoiceService $service): int
    {
        $generated = 0;
        $skipped   = 0;

        Lease::with(['tenant.user', 'property', 'unit'])
            ->where('status', 'active')
            ->where('auto_generate_invoices', true)
            ->each(function (Lease $lease) use ($service, &$generated, &$skipped) {
                $invoice = $service->generateForLease($lease);

                if ($invoice) {
                    $generated++;
                    $this->line("  Generated {$invoice->invoice_number} for lease #{$lease->id}");
                } else {
                    $skipped++;
                }
            });

        $this->info("Done. Generated: {$generated}, Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
