<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $djf = Currency::where('code', 'DJF')->first();

        $plans = [
            [
                'name'             => 'Starter',
                'slug'             => 'starter',
                'description'      => 'For landlords with a few units. Up to 10 active units.',
                'monthly_price'    => 5000,
                'currency_id'      => $djf?->id,
                'max_active_units' => 10,
                'max_active_leases' => 10,
            ],
            [
                'name'             => 'Growth',
                'slug'             => 'growth',
                'description'      => 'Growing portfolios. Up to 50 active units.',
                'monthly_price'    => 15000,
                'currency_id'      => $djf?->id,
                'max_active_units' => 50,
                'max_active_leases' => 50,
            ],
            [
                'name'             => 'Business',
                'slug'             => 'business',
                'description'      => 'For established property management. Up to 200 active units.',
                'monthly_price'    => 40000,
                'currency_id'      => $djf?->id,
                'max_active_units' => 200,
                'max_active_leases' => 200,
            ],
            [
                'name'             => 'Enterprise',
                'slug'             => 'enterprise',
                'description'      => 'Unlimited units. Custom pricing for large portfolios.',
                'monthly_price'    => 0,
                'currency_id'      => $djf?->id,
                'max_active_units' => null,
                'max_active_leases' => null,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}