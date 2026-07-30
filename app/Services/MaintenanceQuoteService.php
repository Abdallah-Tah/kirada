<?php

namespace App\Services;

use App\Models\MaintenanceQuote;
use App\Models\MaintenanceQuoteItem;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaintenanceQuoteService
{
    /**
     * Submit a priced quote for a maintenance request (by the assigned pro).
     *
     * @param  array<int, array{description:string, quantity:float, unit_price:float}>  $items
     */
    public function submitQuote(
        MaintenanceRequest $request,
        User $pro,
        array $items,
        float $taxRate = 0,
        ?string $notes = null,
        ?int $currencyId = null,
    ): MaintenanceQuote {
        if ($items === []) {
            throw new \DomainException('A quote needs at least one line item.');
        }

        if ($request->assigned_to !== $pro->id || ! $pro->hasRole('maintenance')) {
            throw new \DomainException('Only the assigned maintenance professional can submit a quote.');
        }

        return DB::transaction(function () use ($request, $pro, $items, $taxRate, $notes, $currencyId) {
            $quote = MaintenanceQuote::create([
                'maintenance_request_id' => $request->id,
                'maintenance_user_id' => $pro->id,
                'currency_id' => $currencyId,
                'status' => 'pending',
                'tax_rate' => $taxRate,
                'notes' => $notes,
            ]);

            foreach (array_values($items) as $i => $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];

                MaintenanceQuoteItem::create([
                    'maintenance_quote_id' => $quote->id,
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'amount' => round($qty * $price, 2),
                    'sort_order' => $i,
                ]);
            }

            $quote->load('items');
            $quote->recalculate();
            $quote->save();

            return $quote->fresh(['items', 'currency', 'maintenanceUser']);
        });
    }

    /**
     * Landlord/admin approves a pending quote.
     */
    public function approve(MaintenanceQuote $quote): MaintenanceQuote
    {
        if (! $quote->isPending()) {
            throw new \DomainException('Only a pending quote can be approved.');
        }

        $quote->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * Landlord/admin declines a pending quote.
     */
    public function decline(MaintenanceQuote $quote): MaintenanceQuote
    {
        if (! $quote->isPending()) {
            throw new \DomainException('Only a pending quote can be declined.');
        }

        $quote->update(['status' => 'declined']);

        return $quote->fresh();
    }

    /**
     * Convert an approved quote into an invoice (work done, payment due).
     */
    public function markInvoiced(MaintenanceQuote $quote): MaintenanceQuote
    {
        if (! $quote->isApproved()) {
            throw new \DomainException('Only an approved quote can be invoiced.');
        }

        $quote->update([
            'status' => 'invoiced',
            'invoiced_at' => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * Mark an invoiced quote as paid.
     */
    public function markPaid(MaintenanceQuote $quote): MaintenanceQuote
    {
        if ($quote->status !== 'invoiced') {
            throw new \DomainException('Only an invoiced quote can be marked paid.');
        }

        $quote->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $quote->fresh();
    }
}
