<?php

namespace App\Console\Commands;

use App\Models\RentInvoice;
use App\Notifications\RentReminderDue;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendRentReminders extends Command
{
    protected $signature   = 'kirada:send-rent-reminders';
    protected $description = 'Send scheduled rent reminders to tenants based on lease reminder settings.';

    /** Maps reminder key → days relative to due_date (negative = before, positive = after) */
    private const KEY_OFFSETS = [
        'before_due_7' => -7,
        'before_due_3' => -3,
        'before_due_1' => -1,
        'overdue_1'    =>  1,
        'overdue_7'    =>  7,
    ];

    public function handle(): int
    {
        $sent    = 0;
        $skipped = 0;

        RentInvoice::with(['lease', 'tenant.user'])
            ->actionable()
            ->whereHas('lease', fn ($q) => $q->where('status', 'active'))
            ->each(function (RentInvoice $invoice) use (&$sent, &$skipped) {
                $schedule = $invoice->lease?->reminder_schedule
                    ?? ['before_due_7', 'before_due_3', 'before_due_1', 'overdue_1'];

                $tenantUser = $invoice->tenant?->user;
                if (! $tenantUser) {
                    $skipped++;
                    return;
                }

                foreach ($schedule as $key) {
                    if ($key === 'invoice_created') {
                        continue; // handled by generate command
                    }

                    if ($invoice->reminderWasSent($key)) {
                        continue;
                    }

                    if (! $this->shouldSendToday($key, $invoice)) {
                        continue;
                    }

                    $tenantUser->notify(new RentReminderDue($invoice, $key));
                    $invoice->markReminderSent($key);
                    $sent++;

                    $this->line("  Sent [{$key}] for invoice {$invoice->invoice_number}");
                }
            });

        $this->info("Done. Reminders sent: {$sent}, invoices skipped: {$skipped}.");

        return self::SUCCESS;
    }

    private function shouldSendToday(string $key, RentInvoice $invoice): bool
    {
        if (! isset(self::KEY_OFFSETS[$key])) {
            return false;
        }

        $offset  = self::KEY_OFFSETS[$key];
        $target  = $invoice->due_date->copy()->addDays($offset)->startOfDay();

        return Carbon::today()->eq($target);
    }
}
