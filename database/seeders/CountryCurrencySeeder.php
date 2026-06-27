<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountryCurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'DJF', 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0],
            ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'decimals' => 2],
            ['code' => 'SOS', 'name' => 'Somali Shilling', 'symbol' => 'Sh', 'decimals' => 0],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimals' => 2],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimals' => 2],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => '﷼', 'decimals' => 2],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'decimals' => 2],
        ];

        foreach ($currencies as $cur) {
            Currency::firstOrCreate(['code' => $cur['code']], $cur);
        }

        $countries = [
            ['code' => 'DJI', 'code2' => 'DJ', 'name' => 'Djibouti', 'dial_code' => '+253'],
            ['code' => 'ETH', 'code2' => 'ET', 'name' => 'Ethiopia', 'dial_code' => '+251'],
            ['code' => 'SOM', 'code2' => 'SO', 'name' => 'Somalia', 'dial_code' => '+252'],
            ['code' => 'USA', 'code2' => 'US', 'name' => 'United States', 'dial_code' => '+1'],
            ['code' => 'SAU', 'code2' => 'SA', 'name' => 'Saudi Arabia', 'dial_code' => '+966'],
            ['code' => 'ARE', 'code2' => 'AE', 'name' => 'United Arab Emirates', 'dial_code' => '+971'],
            ['code' => 'QAT', 'code2' => 'QA', 'name' => 'Qatar', 'dial_code' => '+974'],
            ['code' => 'EGY', 'code2' => 'EG', 'name' => 'Egypt', 'dial_code' => '+20'],
        ];

        // Map country → default currency
        $currencyMap = [
            'DJI' => 'DJF',
            'ETH' => 'ETB',
            'SOM' => 'SOS',
            'USA' => 'USD',
            'SAU' => 'SAR',
            'ARE' => 'AED',
            'QAT' => 'QAR',
            'EGY' => 'EGP',
        ];

        foreach ($countries as $c) {
            $country = Country::firstOrCreate(['code' => $c['code']], $c);

            $currency = Currency::where('code', $currencyMap[$c['code']])->first();

            if ($currency) {
                $country->currencies()->syncWithoutDetaching([
                    $currency->id => ['is_default' => true],
                ]);
            }
        }
    }
}