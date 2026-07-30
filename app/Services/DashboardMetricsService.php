<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Message;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;

class DashboardMetricsService
{
    public function getAdminMetrics(): array
    {
        return [
            'total_landlords' => User::role('landlord')->count(),
            'total_tenants' => Tenant::count(),
            'total_properties' => Property::count(),
            'total_units' => Unit::count(),
            'active_leases' => Lease::where('status', 'active')->count(),
            'unpaid_invoices' => RentInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
            'open_maintenance' => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
            'active_subscriptions' => Subscription::whereIn('status', ['trialing', 'active'])->count(),
            'recent_properties' => Property::with('landlord:id,name')->latest()->limit(5)->get(),
            'recent_maintenance' => MaintenanceRequest::with('property:id,name')->latest()->limit(5)->get(),
        ];
    }

    public function getLandlordMetrics(User $user): array
    {
        $landlordId = $user->landlordAccountId();
        abort_unless($landlordId !== null, 403);

        $propertyIds = Property::forLandlord($landlordId)->pluck('id');

        $occupiedUnits = Unit::whereIn('property_id', $propertyIds)->where('status', 'occupied')->count();
        $vacantUnits = Unit::whereIn('property_id', $propertyIds)->where('status', 'vacant')->count();
        $totalUnits = Unit::whereIn('property_id', $propertyIds)->count();

        $collectedThisMonth = RentPayment::where('landlord_id', $landlordId)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $unreadMessages = $this->getUnreadMessageCount($user);

        $rentDueThisMonth = RentInvoice::forLandlord($landlordId)
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue', 'sent'])
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->sum('amount');

        $billedThisMonth = RentInvoice::forLandlord($landlordId)
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->sum('amount');

        $overdueInvoices = RentInvoice::forLandlord($landlordId)
            ->where('status', 'overdue')
            ->with(['tenant:id,first_name,last_name'])
            ->latest('due_date')
            ->limit(10)
            ->get();

        $upcomingInvoices = RentInvoice::forLandlord($landlordId)
            ->whereIn('status', ['unpaid', 'sent'])
            ->where('due_date', '>=', now()->toDateString())
            ->where('due_date', '<=', now()->addDays(14)->toDateString())
            ->with(['tenant:id,first_name,last_name'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return [
            // Aggregate sums are labelled with the landlord's first property
            // currency (true multi-currency aggregation is out of scope).
            'dashboard_currency' => Property::forLandlord($landlordId)
                ->whereNotNull('currency_id')->with('currency')->first()?->currency,
            'my_properties' => Property::forLandlord($landlordId)->count(),
            'my_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $vacantUnits,
            'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0,
            'collection_rate' => $billedThisMonth > 0 ? min(100, round(($collectedThisMonth / $billedThisMonth) * 100)) : 0,
            'active_leases' => Lease::where('landlord_id', $landlordId)->where('status', 'active')->count(),
            'unpaid_invoices' => RentInvoice::forLandlord($landlordId)
                ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
            'rent_due_this_month' => $rentDueThisMonth,
            'overdue_invoices' => $overdueInvoices,
            'upcoming_invoices' => $upcomingInvoices,
            'collected_this_month' => $collectedThisMonth,
            'open_maintenance' => MaintenanceRequest::forLandlord($landlordId)
                ->whereIn('status', ['open', 'in_progress'])->count(),
            'unread_messages' => $unreadMessages,
            'recent_leases' => Lease::where('landlord_id', $landlordId)
                ->with(['tenant:id,first_name,last_name', 'property:id,name', 'unit:id,unit_number'])
                ->latest()->limit(5)->get(),
            'recent_payments' => RentPayment::where('landlord_id', $landlordId)
                ->with(['tenant:id,first_name,last_name', 'rentInvoice:id,invoice_number'])
                ->latest()->limit(5)->get(),
        ];
    }

    public function getTenantMetrics(User $user): array
    {
        $tenant = Tenant::where('user_id', $user->id)->first();

        if (! $tenant) {
            return [
                'active_lease' => null,
                'dashboard_currency' => null,
                'current_invoice' => null,
                'payment_history_count' => 0,
                'open_maintenance' => 0,
                'unread_messages' => 0,
                'documents_count' => 0,
                'recent_invoices' => collect(),
                'recent_payments' => collect(),
            ];
        }

        $activeLease = Lease::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with(['property:id,name,currency_id', 'property.currency', 'unit:id,unit_number'])
            ->latest()->first();

        $currentInvoice = RentInvoice::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->latest()->first();

        $paymentCount = RentPayment::where('tenant_id', $tenant->id)->count();

        $openMaintenance = MaintenanceRequest::forTenant($tenant->id)
            ->whereIn('status', ['open', 'in_progress'])->count();

        $unreadMessages = $this->getUnreadMessageCount($user);

        $documentsCount = Document::where('tenant_id', $tenant->id)
            ->where('visibility', 'tenant_visible')->count();

        $nextDueDate = null;
        $overdueAmount = 0.0;
        if ($activeLease) {
            $nextDueDate = app(RentInvoiceService::class)->nextDueDate($activeLease);
        }
        $overdueAmount = RentInvoice::where('tenant_id', $tenant->id)
            ->where('status', 'overdue')
            ->sum('amount');

        return [
            'active_lease' => $activeLease,
            'dashboard_currency' => $activeLease?->property?->currency,
            'current_invoice' => $currentInvoice,
            'next_due_date' => $nextDueDate,
            'overdue_amount' => (float) $overdueAmount,
            'payment_history_count' => $paymentCount,
            'open_maintenance' => $openMaintenance,
            'unread_messages' => $unreadMessages,
            'documents_count' => $documentsCount,
            'recent_invoices' => RentInvoice::where('tenant_id', $tenant->id)
                ->latest()->limit(5)->get(),
            'recent_payments' => RentPayment::where('tenant_id', $tenant->id)
                ->with(['rentInvoice:id,invoice_number'])
                ->latest()->limit(5)->get(),
        ];
    }

    public function getMaintenanceMetrics(User $user): array
    {
        $assignedOpen = MaintenanceRequest::assignedTo($user->id)
            ->whereIn('status', ['open', 'in_progress'])->count();

        $inProgress = MaintenanceRequest::assignedTo($user->id)
            ->where('status', 'in_progress')->count();

        $resolvedThisMonth = MaintenanceRequest::assignedTo($user->id)
            ->where('status', 'resolved')
            ->whereMonth('resolved_at', now()->month)
            ->whereYear('resolved_at', now()->year)
            ->count();

        $recentAssigned = MaintenanceRequest::assignedTo($user->id)
            ->with(['property:id,name', 'unit:id,unit_number'])
            ->latest()->limit(5)->get();

        return [
            'assigned_open' => $assignedOpen,
            'in_progress' => $inProgress,
            'resolved_this_month' => $resolvedThisMonth,
            'recent_assigned' => $recentAssigned,
            // Directory standing: without a published profile a provider gets no
            // work at all, so the dashboard leads with that rather than burying it.
            'profile' => $user->maintenanceProfile,
            'pending_invitations' => $user->landlordConnections()
                ->wherePivot('status', 'pending')->count(),
            'active_landlords' => $user->approvedLandlords()->count(),
        ];
    }

    protected function getUnreadMessageCount(User $user): int
    {
        $conversationIds = Conversation::query()
            ->when($user->canAccessLandlordPortal(), fn ($q) => $q->where('landlord_id', $user->landlordAccountId()))
            ->when($user->hasRole('tenant'), function ($q) use ($user) {
                $tenant = Tenant::where('user_id', $user->id)->first();
                $q->where('tenant_id', $tenant?->id ?? 0);
            })
            ->pluck('id');

        if ($conversationIds->isEmpty()) {
            return 0;
        }

        return Message::whereIn('conversation_id', $conversationIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
