<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Smart Billing Engine ───────────────────────────────────────────────────

// Mark unpaid invoices overdue first (05:30), then the three billing commands run after.
Schedule::call(function () {
    app(\App\Services\RentInvoiceService::class)->markOverdue();
})->dailyAt('05:30')->name('mark-overdue-invoices');

// Generate invoices for leases whose due date is within X days.
Schedule::command('kirada:generate-rent-invoices')->dailyAt('06:00');

// Fire before-due / overdue reminder notifications.
Schedule::command('kirada:send-rent-reminders')->dailyAt('08:00');

// Add late fee line items after grace period expires.
Schedule::command('kirada:apply-late-fees')->dailyAt('09:00');
