<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    public function getAdminMetrics(): array
    {
        return [
            'total_landlords'       => User::role('landlord')->count(),
            'total_tenants'         => Tenant::count(),
            'total_properties'      => Property::count(),
            'total_units'           => Unit::count(),
            'active_leases'         => Lease::where('status', 'active')->count(),
            'unpaid_invoices'       => RentInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
            'open_maintenance'      => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
            'active_subscriptions'  => Subscription::whereIn('status', ['trialing', 'active'])->count(),
            'recent_properties'     => Property::with('landlord:id,name')->latest()->limit(5)->get(),
            'recent_maintenance'    => MaintenanceRequest::with('property:id,name')->latest()->limit(5)->get(),
        ];
    }

    public function getLandlordMetrics(User $landlord): array
    {
        $propertyIds = Property::forLandlord($landlord->id)->pluck('id');
        $unitIds = Unit::whereIn('property_id', $propertyIds)->pluck('id');
        $tenantIds = Tenant::forLandlord($landlord->id)->pluck('id');

        $occupiedUnits = Unit::whereIn('property_id', $propertyIds)->where('status', 'occupied')->count();
        $vacantUnits = Unit::whereIn('property_id', $propertyIds)->where('status', 'vacant')->count();

        $collectedThisMonth = RentPayment::where('landlord_id', $landlord->id)
            ->where('status', 'confirmed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $unreadMessages = $this->getUnreadMessageCount($landlord);

        return [
            'my_properties'         => Property::forLandlord($landlord->id)->count(),
            'my_units'              => Unit::whereIn('property_id', $propertyIds)->count(),
            'occupied_units'        => $occupiedUnits,
            'vacant_units'          => $vacantUnits,
            'active_leases'         => Lease::where('landlord_id', $landlord->id)->where('status', 'active')->count(),
            'unpaid_invoices'       => RentInvoice::forLandlord($landlord->id)
                ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count(),
            'collected_this_month'  => $collectedThisMonth,
            'open_maintenance'      => MaintenanceRequest::forLandlord($landlord->id)
                ->whereIn('status', ['open', 'in_progress'])->count(),
            'unread_messages'       => $unreadMessages,
            'recent_leases'         => Lease::where('landlord_id', $landlord->id)
                ->with(['tenant:id,first_name,last_name', 'property:id,name', 'unit:id,unit_number'])
                ->latest()->limit(5)->get(),
            'recent_payments'       => RentPayment::where('landlord_id', $landlord->id)
                ->with(['tenant:id,first_name,last_name', 'rentInvoice:id,invoice_number'])
                ->latest()->limit(5)->get(),
        ];
    }

    public function getTenantMetrics(User $user): array
    {
        $tenant = Tenant::where('user_id', $user->id)->first();

        if (!$tenant) {
            return [
                'active_lease'          => null,
                'current_invoice'       => null,
                'payment_history_count' => 0,
                'open_maintenance'      => 0,
                'unread_messages'       => 0,
                'documents_count'       => 0,
                'recent_invoices'       => collect(),
                'recent_payments'       => collect(),
            ];
        }

        $activeLease = Lease::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with(['property:id,name', 'unit:id,unit_number'])
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

        return [
            'active_lease'          => $activeLease,
            'current_invoice'       => $currentInvoice,
            'payment_history_count' => $paymentCount,
            'open_maintenance'      => $openMaintenance,
            'unread_messages'       => $unreadMessages,
            'documents_count'       => $documentsCount,
            'recent_invoices'       => RentInvoice::where('tenant_id', $tenant->id)
                ->latest()->limit(5)->get(),
            'recent_payments'       => RentPayment::where('tenant_id', $tenant->id)
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
            'assigned_open'         => $assignedOpen,
            'in_progress'           => $inProgress,
            'resolved_this_month'   => $resolvedThisMonth,
            'recent_assigned'       => $recentAssigned,
        ];
    }

    protected function getUnreadMessageCount(User $user): int
    {
        $conversationIds = Conversation::query()
            ->when($user->hasRole('landlord'), fn($q) => $q->where('landlord_id', $user->id))
            ->when($user->hasRole('tenant'), function ($q) use ($user) {
                $tenant = Tenant::where('user_id', $user->id)->first();
                $q->where('tenant_id', $tenant?->id ?? 0);
            })
            ->pluck('id');

        if ($conversationIds->isEmpty()) {
            return 0;
        }

        return \App\Models\Message::whereIn('conversation_id', $conversationIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}