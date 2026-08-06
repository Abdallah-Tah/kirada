<?php

namespace App\Filament\Widgets;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalLandlords = User::role('landlord')->count();
        $totalTenants = Tenant::count();
        $activeLeases = Lease::where('status', 'active')->count();

        $occupiedUnits = Unit::where('status', 'occupied')->count();
        $vacantUnits = Unit::where('status', 'vacant')->count();

        $unpaidInvoices = RentInvoice::whereIn('status', ['unpaid', 'partially_paid'])->count();
        $overdueInvoices = RentInvoice::where('status', 'overdue')->count();

        $pendingPayments = RentPayment::where('status', 'pending')->count();
        $confirmedPayments = RentPayment::where('status', 'confirmed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->count();
        $rejectedPayments = RentPayment::where('status', 'rejected')
            ->whereMonth('created_at', now()->month)
            ->count();

        // Revenue this month (using first available currency for display)
        $revenueThisMonth = RentPayment::where('status', 'confirmed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Outstanding rent
        $outstandingRent = RentInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->sum('amount');

        $openMaintenance = MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count();

        $failedJobs = DB::table('failed_jobs')->count();

        $recentDeliveryFailures = DB::table('notification_deliveries')
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Total Landlords', number_format($totalLandlords))
                ->description('Landlord accounts')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Total Tenants', number_format($totalTenants))
                ->description('Tenant profiles')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Active Leases', number_format($activeLeases))
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),

            Stat::make('Occupied / Vacant', "{$occupiedUnits} / {$vacantUnits}")
                ->description('Unit occupancy')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning'),

            Stat::make('Unpaid / Overdue', "{$unpaidInvoices} / {$overdueInvoices}")
                ->description('Invoice status')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Confirmed / Pending', "{$confirmedPayments} / {$pendingPayments}")
                ->description('Payments this month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Rejected Payments', number_format($rejectedPayments))
                ->description('This month')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Revenue This Month', Money::format((float) $revenueThisMonth))
                ->description('Confirmed payments')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Outstanding Rent', Money::format((float) $outstandingRent))
                ->description('Unpaid + overdue')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Open Maintenance', number_format($openMaintenance))
                ->description('Open + in progress')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('gray'),

            Stat::make('Failed Queue Jobs', number_format($failedJobs))
                ->description('Requires attention')
                ->descriptionIcon('heroicon-m-bolt-slash')
                ->color($failedJobs > 0 ? 'danger' : 'success'),

            Stat::make('Delivery Failures (7d)', number_format($recentDeliveryFailures))
                ->description('Email/WhatsApp failures')
                ->descriptionIcon('heroicon-m-envelope-exclamation')
                ->color($recentDeliveryFailures > 0 ? 'warning' : 'success'),
        ];
    }
}
