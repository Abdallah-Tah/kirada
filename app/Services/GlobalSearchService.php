<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    private const LIMIT_PER_GROUP = 6;

    /**
     * @return array<int, array{label: string, results: Collection<int, array<string, mixed>>}>
     */
    public function search(User $user, string $rawQuery): array
    {
        $query = trim($rawQuery);

        if (mb_strlen($query) < 2) {
            return [];
        }

        if ($user->isAdmin() || $user->canAccessLandlordPortal()) {
            return $this->portfolioResults($user, $query);
        }

        if ($user->isTenant()) {
            return $this->tenantResults($user, $query);
        }

        if ($user->isMaintenance()) {
            return $this->maintenanceResults($user, $query);
        }

        return [];
    }

    /**
     * @return array<int, array{label: string, results: Collection<int, array<string, mixed>>}>
     */
    private function portfolioResults(User $user, string $query): array
    {
        $landlordId = $user->landlordAccountId();
        $like = $this->like($query);
        $groups = [];

        if ($user->can('properties.view') || $user->isAdmin()) {
            $results = Property::query()
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->forLandlord($landlordId))
                ->where(fn (Builder $builder) => $builder
                    ->where('name', 'like', $like)
                    ->orWhere('address_line_1', 'like', $like)
                    ->orWhere('city', 'like', $like))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (Property $property) => [
                    'title' => $property->name,
                    'subtitle' => collect([$property->address_line_1, $property->city])->filter()->join(', '),
                    'meta' => __('Property'),
                    'href' => route('properties.index', ['search' => $query]),
                ]);
            $this->addGroup($groups, __('Properties'), $results);
        }

        if ($user->can('units.view') || $user->isAdmin()) {
            $results = Unit::query()
                ->with('property:id,name,landlord_id')
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->whereHas(
                    'property',
                    fn (Builder $property) => $property->forLandlord($landlordId),
                ))
                ->where(fn (Builder $builder) => $builder
                    ->where('unit_number', 'like', $like)
                    ->orWhere('floor', 'like', $like)
                    ->orWhereHas('property', fn (Builder $property) => $property->where('name', 'like', $like)))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (Unit $unit) => [
                    'title' => __('Unit :number', ['number' => $unit->unit_number]),
                    'subtitle' => $unit->property?->name,
                    'meta' => __(str($unit->status)->headline()->toString()),
                    'href' => route('units.index', ['search' => $query]),
                ]);
            $this->addGroup($groups, __('Units'), $results);
        }

        if ($user->can('tenants.view') || $user->isAdmin()) {
            $results = Tenant::query()
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->forLandlord($landlordId))
                ->where(fn (Builder $builder) => $builder
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (Tenant $tenant) => [
                    'title' => $tenant->full_name,
                    'subtitle' => $tenant->email ?: $tenant->phone,
                    'meta' => __('Tenant'),
                    'href' => route('tenants.index', ['search' => $query]),
                ]);
            $this->addGroup($groups, __('Tenants'), $results);
        }

        if ($user->can('leases.view') || $user->isAdmin()) {
            $results = Lease::query()
                ->with(['tenant:id,first_name,last_name', 'property:id,name', 'unit:id,unit_number'])
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->forLandlord($landlordId))
                ->where(fn (Builder $builder) => $builder
                    ->where('status', 'like', $like)
                    ->orWhereHas('tenant', fn (Builder $tenant) => $tenant
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like))
                    ->orWhereHas('property', fn (Builder $property) => $property->where('name', 'like', $like))
                    ->orWhereHas('unit', fn (Builder $unit) => $unit->where('unit_number', 'like', $like)))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (Lease $lease) => [
                    'title' => __('Lease #:id', ['id' => $lease->id]),
                    'subtitle' => collect([$lease->tenant?->full_name, $lease->property?->name, $lease->unit?->unit_number])->filter()->join(' · '),
                    'meta' => __(str($lease->status)->headline()->toString()),
                    'href' => route('leases.show', $lease),
                ]);
            $this->addGroup($groups, __('Leases'), $results);
        }

        if ($user->can('invoices.view') || $user->isAdmin()) {
            $results = RentInvoice::query()
                ->with('tenant:id,first_name,last_name')
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->forLandlord($landlordId))
                ->where(fn (Builder $builder) => $builder
                    ->where('invoice_number', 'like', $like)
                    ->orWhere('payment_reference', 'like', $like)
                    ->orWhereHas('tenant', fn (Builder $tenant) => $tenant
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (RentInvoice $invoice) => [
                    'title' => $invoice->invoice_number,
                    'subtitle' => $invoice->tenant?->full_name,
                    'meta' => __(str($invoice->status)->headline()->toString()),
                    'href' => route('rent-invoices.index', ['search' => $query]),
                ]);
            $this->addGroup($groups, __('Rent Invoices'), $results);
        }

        if ($user->can('maintenance.view') || $user->isAdmin()) {
            $results = MaintenanceRequest::query()
                ->with(['property:id,name', 'unit:id,unit_number'])
                ->when(! $user->isAdmin(), fn (Builder $builder) => $builder->forLandlord($landlordId))
                ->where(fn (Builder $builder) => $builder
                    ->where('title', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhereHas('property', fn (Builder $property) => $property->where('name', 'like', $like))
                    ->orWhereHas('unit', fn (Builder $unit) => $unit->where('unit_number', 'like', $like)))
                ->limit(self::LIMIT_PER_GROUP)
                ->get()
                ->map(fn (MaintenanceRequest $request) => $this->maintenanceResult($request));
            $this->addGroup($groups, __('Maintenance'), $results);
        }

        return $groups;
    }

    /**
     * @return array<int, array{label: string, results: Collection<int, array<string, mixed>>}>
     */
    private function tenantResults(User $user, string $query): array
    {
        $tenantIds = Tenant::query()->where('user_id', $user->id)->pluck('id');
        $like = $this->like($query);
        $groups = [];

        $invoices = RentInvoice::query()
            ->whereIn('tenant_id', $tenantIds)
            ->where(fn (Builder $builder) => $builder
                ->where('invoice_number', 'like', $like)
                ->orWhere('payment_reference', 'like', $like)
                ->orWhere('status', 'like', $like))
            ->limit(self::LIMIT_PER_GROUP)
            ->get()
            ->map(fn (RentInvoice $invoice) => [
                'title' => $invoice->invoice_number,
                'subtitle' => $invoice->formatted_amount,
                'meta' => __(str($invoice->status)->headline()->toString()),
                'href' => route('rent-invoices.index', ['search' => $query]),
            ]);
        $this->addGroup($groups, __('My Rent'), $invoices);

        $maintenance = MaintenanceRequest::query()
            ->with(['property:id,name', 'unit:id,unit_number'])
            ->whereIn('tenant_id', $tenantIds)
            ->where(fn (Builder $builder) => $builder
                ->where('title', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('status', 'like', $like))
            ->limit(self::LIMIT_PER_GROUP)
            ->get()
            ->map(fn (MaintenanceRequest $request) => $this->maintenanceResult($request));
        $this->addGroup($groups, __('Maintenance'), $maintenance);

        $documents = Document::query()
            ->whereIn('tenant_id', $tenantIds)
            ->where('visibility', 'tenant_visible')
            ->where(fn (Builder $builder) => $builder
                ->where('title', 'like', $like)
                ->orWhere('original_filename', 'like', $like)
                ->orWhere('type', 'like', $like))
            ->limit(self::LIMIT_PER_GROUP)
            ->get()
            ->map(fn (Document $document) => [
                'title' => $document->title,
                'subtitle' => $document->original_filename,
                'meta' => __('Document'),
                'href' => route('documents.index'),
            ]);
        $this->addGroup($groups, __('Documents'), $documents);

        return $groups;
    }

    /**
     * @return array<int, array{label: string, results: Collection<int, array<string, mixed>>}>
     */
    private function maintenanceResults(User $user, string $query): array
    {
        $like = $this->like($query);
        $results = MaintenanceRequest::query()
            ->with(['property:id,name', 'unit:id,unit_number'])
            ->assignedTo($user->id)
            ->where(fn (Builder $builder) => $builder
                ->where('title', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhere('status', 'like', $like)
                ->orWhereHas('property', fn (Builder $property) => $property->where('name', 'like', $like))
                ->orWhereHas('unit', fn (Builder $unit) => $unit->where('unit_number', 'like', $like)))
            ->limit(self::LIMIT_PER_GROUP)
            ->get()
            ->map(fn (MaintenanceRequest $request) => $this->maintenanceResult($request));

        $groups = [];
        $this->addGroup($groups, __('Assigned Requests'), $results);

        return $groups;
    }

    /**
     * @param  array<int, array{label: string, results: Collection<int, array<string, mixed>>}>  $groups
     */
    private function addGroup(array &$groups, string $label, Collection $results): void
    {
        if ($results->isNotEmpty()) {
            $groups[] = compact('label', 'results');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function maintenanceResult(MaintenanceRequest $request): array
    {
        return [
            'title' => $request->title,
            'subtitle' => collect([$request->property?->name, $request->unit?->unit_number])->filter()->join(' · '),
            'meta' => __(str($request->status)->headline()->toString()),
            'href' => route('maintenance-requests.show', $request),
        ];
    }

    private function like(string $query): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query).'%';
    }
}
