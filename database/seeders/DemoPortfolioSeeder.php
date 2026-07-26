<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $country = Country::where('code', 'DJI')->firstOrFail();
        $currency = Currency::where('code', 'DJF')->firstOrFail();
        $demoPassword = config('app.demo_password');

        if (! is_string($demoPassword) || $demoPassword === '') {
            throw new \RuntimeException('KIRADA_DEMO_PASSWORD must be a non-empty string.');
        }

        $landlord = User::firstOrCreate(
            ['email' => 'abdal_cascad@hotmail.com'],
            [
                'name' => 'Abdallah Mohamed',
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
                'country_id' => $country->id,
                'preferred_language' => 'en',
                'phone_country_code' => $country->dial_code,
            ],
        );
        $landlord->assignRole('landlord');
        app(SubscriptionService::class)->startTrial($landlord);

        $property = Property::firstOrCreate(
            [
                'landlord_id' => $landlord->id,
                'name' => 'Abdallah Mohamed',
                'address_line_1' => 'Lot 615 - Cite Nagad',
            ],
            [
                'country_id' => $country->id,
                'currency_id' => $currency->id,
                'type' => 'residential',
                'address_line_2' => '',
                'city' => 'Djibouti',
                'region' => 'Djibouti-ville',
                'postal_code' => '',
                'country' => 'Djibouti',
                'is_active' => true,
            ],
        );

        $unit = Unit::firstOrCreate(
            ['property_id' => $property->id, 'unit_number' => '3'],
            [
                'type' => 'other',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'monthly_rent' => 120000,
                'security_deposit' => 120000,
                'status' => 'occupied',
                'is_active' => true,
            ],
        );

        $tenant = Tenant::firstOrCreate(
            [
                'landlord_id' => $landlord->id,
                'first_name' => 'Adna',
                'last_name' => 'Mohamoud-Rachid',
                'phone' => '77222406',
            ],
            [
                'address' => 'Ambouli',
                'city' => 'Djibouti',
                'status' => 'active',
            ],
        );

        Lease::firstOrCreate(
            [
                'landlord_id' => $landlord->id,
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
                'start_date' => '2026-06-05',
            ],
            [
                'property_id' => $property->id,
                'monthly_rent' => 120000,
                'security_deposit' => 120000,
                'payment_due_day' => 5,
                'auto_generate_invoices' => true,
                'invoice_generation_days_before_due' => 7,
                'grace_period_days' => 5,
                'late_fee_type' => 'none',
                'late_fee_frequency' => 'once',
                'reminder_schedule' => [
                    'before_due_7',
                    'before_due_3',
                    'before_due_1',
                    'overdue_1',
                ],
                'status' => 'active',
            ],
        );
    }
}
