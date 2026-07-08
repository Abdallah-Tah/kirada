<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

/**
 * Creates or updates Stripe Products and Prices for each active plan.
 *
 * Usage:
 *   php artisan stripe:sync-plans
 *   php artisan stripe:sync-plans --force   # re-sync even if stripe_price_id is set
 *
 * Stripe prices are in the smallest currency unit (DJF has no subunits → use
 * amount as-is since DJF is a zero-decimal currency on Stripe).
 */
class StripeSyncPlans extends Command
{
    protected $signature = 'stripe:sync-plans {--force : Re-sync plans that already have a stripe_price_id}';
    protected $description = 'Sync Kirada plans to Stripe Products and Prices';

    public function handle(): int
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $plans = Plan::active()->get();

        if ($plans->isEmpty()) {
            $this->warn('No active plans found. Run the PlanSeeder first.');
            return self::FAILURE;
        }

        foreach ($plans as $plan) {
            if ($plan->stripe_price_id && ! $this->option('force')) {
                $this->line("  <fg=gray>skip</> {$plan->name} (already synced: {$plan->stripe_price_id})");
                continue;
            }

            if ($plan->monthly_price <= 0) {
                $this->line("  <fg=yellow>skip</> {$plan->name} (price is 0 — Enterprise/custom plan)");
                continue;
            }

            // Create or retrieve a Product for this plan
            $product = Product::create([
                'name'     => "Kirada {$plan->name}",
                'metadata' => ['kirada_plan_slug' => $plan->slug, 'kirada_plan_id' => $plan->id],
            ]);

            // Create a recurring Price (DJF is zero-decimal on Stripe)
            $price = Price::create([
                'product'        => $product->id,
                'unit_amount'    => (int) $plan->monthly_price,
                'currency'       => strtolower($plan->currency?->code ?? 'djf'),
                'recurring'      => ['interval' => 'month'],
                'metadata'       => ['kirada_plan_slug' => $plan->slug],
            ]);

            $plan->update(['stripe_price_id' => $price->id]);

            $this->info("  <fg=green>synced</> {$plan->name} → {$price->id}");
        }

        $this->newLine();
        $this->info('Done. Stripe plans synced successfully.');

        return self::SUCCESS;
    }
}
