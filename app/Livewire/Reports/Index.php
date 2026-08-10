<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'month';

    public function render()
    {
        return view('livewire.reports.index')
            ->layout('layouts.app')
            ->title(__('Reports'));
    }

    #[Computed]
    public function summary(): array
    {
        $landlordId = $this->reportLandlordId();

        $propertyCount = Property::forLandlord($landlordId)->count();
        $unitCount = Unit::whereHas('property', fn ($q) => $q->forLandlord($landlordId))->count();
        $occupiedUnits = Unit::whereHas('property', fn ($q) => $q->forLandlord($landlordId))
            ->occupied()->count();
        $tenantCount = Tenant::forLandlord($landlordId)->count();
        $activeLeases = Lease::forLandlord($landlordId)->active()->count();
        $supportsRenewalReporting = method_exists(Lease::class, 'scopeExpiringWithin')
            && method_exists(Lease::class, 'scopeExpired');
        $leasesExpiring30 = $supportsRenewalReporting
            ? Lease::forLandlord($landlordId)->expiringWithin(30)->count()
            : 0;
        $leasesExpiring90 = $supportsRenewalReporting
            ? Lease::forLandlord($landlordId)->expiringWithin(90)->count()
            : 0;
        $leasesExpired = $supportsRenewalReporting
            ? Lease::forLandlord($landlordId)->expired()->count()
            : 0;

        $invoices = RentInvoice::forLandlord($landlordId);
        $totalInvoiced = (clone $invoices)->sum('amount');
        $totalCollected = RentPayment::whereHas('rentInvoice', fn ($q) => $q->forLandlord($landlordId))
            ->sum('amount');
        $totalExpenses = Expense::forLandlord($landlordId)->sum('amount');
        $outstanding = (clone $invoices)->unpaid()->sum('amount');
        $overdue = (clone $invoices)->overdue()->sum('amount');

        $maintenanceOpen = MaintenanceRequest::forLandlord($landlordId)->open()->count();
        $maintenanceResolved = MaintenanceRequest::forLandlord($landlordId)
            ->where('status', 'resolved')->count();

        $occupancyRate = $unitCount > 0 ? round(($occupiedUnits / $unitCount) * 100, 1) : 0;
        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0;

        return [
            'properties' => $propertyCount,
            'units' => $unitCount,
            'occupied_units' => $occupiedUnits,
            'occupancy_rate' => $occupancyRate,
            'tenants' => $tenantCount,
            'active_leases' => $activeLeases,
            'leases_expiring_30' => $leasesExpiring30,
            'leases_expiring_90' => $leasesExpiring90,
            'leases_expired' => $leasesExpired,
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalCollected,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalCollected - $totalExpenses,
            'outstanding' => $outstanding,
            'overdue' => $overdue,
            'collection_rate' => $collectionRate,
            'maintenance_open' => $maintenanceOpen,
            'maintenance_resolved' => $maintenanceResolved,
        ];
    }

    #[Computed]
    public function rentChart(): array
    {
        $landlordId = $this->reportLandlordId();

        $months = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i);
        });

        $data = [];
        foreach ($months as $month) {
            $invoiced = RentInvoice::forLandlord($landlordId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $collected = RentPayment::whereHas('rentInvoice', fn ($q) => $q->forLandlord($landlordId))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $expenses = Expense::forLandlord($landlordId)
                ->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->sum('amount');

            $data[] = [
                'label' => $month->format('M'),
                'invoiced' => (float) $invoiced,
                'collected' => (float) $collected,
                'expenses' => (float) $expenses,
                'net_income' => (float) $collected - (float) $expenses,
            ];
        }

        return $data;
    }

    #[Computed]
    public function expenseBreakdown(): array
    {
        return Expense::forLandlord($this->reportLandlordId())
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn (Expense $expense) => [
                'category' => $expense->category_label,
                'total' => (float) $expense->total,
            ])
            ->all();
    }

    #[Computed]
    public function maintenanceBreakdown(): array
    {
        $landlordId = $this->reportLandlordId();

        return MaintenanceRequest::forLandlord($landlordId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    #[Computed]
    public function topPropertiesByOutstanding(): array
    {
        $landlordId = $this->reportLandlordId();

        return Property::forLandlord($landlordId)
            ->withSum(['rentInvoices' => fn ($q) => $q->unpaid()], 'amount')
            ->orderByDesc('rent_invoices_sum_amount')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'name' => $p->full_address,
                'outstanding' => $p->rent_invoices_sum_amount ?? 0,
            ])
            ->toArray();
    }

    private function reportLandlordId(): int
    {
        $user = auth()->user();

        if (method_exists($user, 'landlordAccountId')) {
            return $user->landlordAccountId() ?? $user->id;
        }

        return $user->id;
    }
}
