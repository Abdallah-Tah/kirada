<?php

namespace App\Services;

use App\Models\ContractSignature;
use App\Models\MaintenanceRequest;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AttentionItem;

/**
 * Builds the role-scoped "Needs attention" list shown in the app header.
 *
 * Attention is not a notification feed — it is a live count of things still
 * waiting on *this* user. A landlord and a tenant have nothing in common to be
 * nudged about, so each role gets its own list and no role is left with a
 * permanently empty menu.
 */
class AttentionService
{
    /**
     * @return array<int, AttentionItem>
     */
    public function itemsFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->canAccessLandlordPortal()) {
            return $this->landlordItems($user);
        }

        if ($user->hasRole('maintenance')) {
            return $this->maintenanceItems($user);
        }

        if ($user->isTenant()) {
            return $this->tenantItems($user);
        }

        return [];
    }

    public function countFor(?User $user): int
    {
        return array_sum(array_map(
            static fn (AttentionItem $item): int => $item->count,
            $this->itemsFor($user),
        ));
    }

    /**
     * @return array<int, AttentionItem>
     */
    private function landlordItems(User $user): array
    {
        $landlordId = $user->landlordAccountId();

        $pendingConnections = $user->landlordAccount()
            ?->maintenanceConnections()->wherePivot('status', 'pending')->count() ?? 0;

        $openRequests = $landlordId
            ? MaintenanceRequest::forLandlord($landlordId)->open()->count()
            : 0;

        $overdueInvoices = $landlordId
            ? RentInvoice::forLandlord($landlordId)->overdue()->count()
            : 0;

        return array_values(array_filter([
            $this->pendingConnectionsItem($pendingConnections, route('maintenance-network.index')),
            $this->openRequestsItem($openRequests),
            $overdueInvoices > 0 ? new AttentionItem(
                key: 'overdue-invoices',
                label: trans_choice(':count overdue invoice|:count overdue invoices', $overdueInvoices),
                url: route('rent-invoices.index'),
                icon: 'exclamation-triangle',
                count: $overdueInvoices,
                countClass: 'text-red-500',
            ) : null,
        ]));
    }

    /**
     * @return array<int, AttentionItem>
     */
    private function maintenanceItems(User $user): array
    {
        return array_values(array_filter([
            $this->pendingConnectionsItem(
                $user->landlordConnections()->wherePivot('status', 'pending')->count(),
                route('maintenance-network.inbox'),
            ),
            $this->openRequestsItem(MaintenanceRequest::assignedTo($user->id)->open()->count()),
        ]));
    }

    /**
     * A tenant's list leads with signatures: an unsigned lease blocks
     * everything else, and until now it was only ever announced by email.
     *
     * @return array<int, AttentionItem>
     */
    private function tenantItems(User $user): array
    {
        $tenant = Tenant::where('user_id', $user->id)->first();

        if (! $tenant) {
            return [];
        }

        $signatures = ContractSignature::forTenant($tenant->id)->actionable()
            ->oldest('id')
            ->get();

        $unpaidInvoices = RentInvoice::where('tenant_id', $tenant->id)->unpaid()->count();
        $openRequests = MaintenanceRequest::forTenant($tenant->id)->open()->count();

        return array_values(array_filter([
            $signatures->isNotEmpty() ? new AttentionItem(
                key: 'pending-signatures',
                label: trans_choice(
                    ':count contract awaiting your signature|:count contracts awaiting your signature',
                    $signatures->count(),
                ),
                url: route('contracts.sign', $signatures->first()->token),
                icon: 'pencil-square',
                count: $signatures->count(),
                countClass: 'text-emerald-500',
                navigate: false,
            ) : null,
            $unpaidInvoices > 0 ? new AttentionItem(
                key: 'unpaid-invoices',
                label: trans_choice(':count unpaid invoice|:count unpaid invoices', $unpaidInvoices),
                url: route('rent-invoices.index'),
                icon: 'banknotes',
                count: $unpaidInvoices,
                countClass: 'text-amber-500',
            ) : null,
            $this->openRequestsItem($openRequests),
        ]));
    }

    private function pendingConnectionsItem(int $count, string $url): ?AttentionItem
    {
        if ($count < 1) {
            return null;
        }

        return new AttentionItem(
            key: 'pending-connections',
            label: trans_choice(':count pending connection|:count pending connections', $count),
            url: $url,
            icon: 'user-group',
            count: $count,
            countClass: 'text-sky-500',
        );
    }

    private function openRequestsItem(int $count): ?AttentionItem
    {
        if ($count < 1) {
            return null;
        }

        return new AttentionItem(
            key: 'open-requests',
            label: trans_choice(':count open maintenance request|:count open maintenance requests', $count),
            url: route('maintenance-requests.index'),
            icon: 'wrench-screwdriver',
            count: $count,
            countClass: 'text-amber-500',
        );
    }
}
