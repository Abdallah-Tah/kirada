<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $isProduction = app()->environment('production');

        $djibouti = Country::where('code', 'DJI')->first();

        if ($isProduction) {
            // In production, only create the initial admin.
            // Replace this email with the real admin email before seeding.
            $adminEmail = env('KIRADA_ADMIN_EMAIL', 'admin@kirada.dj');
            $adminPassword = env('KIRADA_ADMIN_PASSWORD', 'ChangeMe123!');

            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'System Admin',
                    'password' => Hash::make($adminPassword),
                    'email_verified_at' => now(),
                    'country_id' => $djibouti?->id,
                    'preferred_language' => 'en',
                    'phone_country_code' => $djibouti?->dial_code,
                ]
            );
            $admin->assignRole('admin');

            $this->command->info("Production admin created: {$adminEmail}");
            $this->command->warn('Change the admin password immediately after first login!');
            return;
        }

        // Dev/test accounts (non-production only)
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

            // Start trial for landlord accounts
            if ($u['role'] === 'landlord') {
                app(SubscriptionService::class)->startTrial($user);
            }
        }
    }
}