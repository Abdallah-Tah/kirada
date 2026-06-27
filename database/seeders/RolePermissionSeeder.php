<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ──────────────────────────────────────────────
        //  Permissions grouped by module
        // ──────────────────────────────────────────────

        $permissions = [
            // Admin — user management
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Admin — landlord management
            'landlords.view', 'landlords.create', 'landlords.edit', 'landlords.delete',

            // Admin — subscriptions
            'subscriptions.view', 'subscriptions.manage',

            // Admin — system
            'system.settings', 'reports.view', 'audit.view',

            // Landlord — properties
            'properties.view', 'properties.create', 'properties.edit', 'properties.delete',

            // Landlord — units
            'units.view', 'units.create', 'units.edit', 'units.delete',

            // Landlord — tenants
            'tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete',

            // Landlord — leases
            'leases.view', 'leases.create', 'leases.edit', 'leases.delete',

            // Landlord — rent invoices
            'invoices.view', 'invoices.create', 'invoices.edit',

            // Landlord — payments
            'payments.view', 'payments.confirm',

            // Landlord — maintenance
            'maintenance.view', 'maintenance.respond',

            // Landlord — messaging
            'messages.send', 'messages.view',

            // Tenant
            'rent.view', 'invoices.view.own', 'payments.upload', 'maintenance.create', 'maintenance.view.own',
            'documents.view.own', 'messages.send.own', 'messages.view.own',

            // Maintenance staff
            'maintenance.assigned', 'maintenance.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ──────────────────────────────────────────────
        //  Roles with permissions
        // ──────────────────────────────────────────────

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'landlords.view', 'landlords.create', 'landlords.edit', 'landlords.delete',
            'subscriptions.view', 'subscriptions.manage',
            'system.settings', 'reports.view', 'audit.view',
        ]);

        $landlord = Role::firstOrCreate(['name' => 'landlord']);
        $landlord->givePermissionTo([
            'properties.view', 'properties.create', 'properties.edit', 'properties.delete',
            'units.view', 'units.create', 'units.edit', 'units.delete',
            'tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete',
            'leases.view', 'leases.create', 'leases.edit', 'leases.delete',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'payments.view', 'payments.confirm',
            'maintenance.view', 'maintenance.respond',
            'messages.send', 'messages.view',
        ]);

        $tenant = Role::firstOrCreate(['name' => 'tenant']);
        $tenant->givePermissionTo([
            'rent.view', 'invoices.view.own', 'payments.upload',
            'maintenance.create', 'maintenance.view.own',
            'documents.view.own', 'messages.send.own', 'messages.view.own',
        ]);

        $maintenance = Role::firstOrCreate(['name' => 'maintenance']);
        $maintenance->givePermissionTo([
            'maintenance.assigned', 'maintenance.update',
            'messages.view', 'messages.send',
        ]);
    }
}