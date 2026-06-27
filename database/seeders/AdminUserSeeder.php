<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $djibouti = Country::where('code', 'DJI')->first();

        $users = [
            [
                'email' => 'admin@kirada.dj',
                'name' => 'System Admin',
                'role' => 'admin',
            ],
            [
                'email' => 'landlord@kirada.dj',
                'name' => 'Test Landlord',
                'role' => 'landlord',
            ],
            [
                'email' => 'tenant@kirada.dj',
                'name' => 'Test Tenant',
                'role' => 'tenant',
            ],
            [
                'email' => 'maintenance@kirada.dj',
                'name' => 'Test Maintenance',
                'role' => 'maintenance',
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'country_id' => $djibouti?->id,
                    'preferred_language' => 'en',
                    'phone_country_code' => $djibouti?->dial_code,
                ]
            );

            $user->assignRole($u['role']);
        }
    }
}