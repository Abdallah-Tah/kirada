<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
            'notifications.manage',

            // Landlord — payments
            'payments.view', 'payments.confirm',

            // Landlord — maintenance
            'maintenance.view', 'maintenance.respond',

            // Landlord — messaging
            'messages.send', 'messages.view',

            // Landlord — documents and team
            'documents.view', 'documents.manage',
            'team.view', 'team.invite', 'team.manage',

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
            'notifications.manage',
            'payments.view', 'payments.confirm',
            'maintenance.view', 'maintenance.respond',
            'messages.send', 'messages.view',
            'documents.view', 'documents.manage',
            'reports.view', 'audit.view', 'team.view', 'team.invite', 'team.manage',
        ]);

        $landlordAdmin = Role::firstOrCreate(['name' => 'landlord-admin']);
        $landlordAdmin->syncPermissions([
            'properties.view', 'properties.create', 'properties.edit',
            'units.view', 'units.create', 'units.edit',
            'tenants.view', 'tenants.create', 'tenants.edit',
            'leases.view', 'leases.create', 'leases.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'notifications.manage',
            'payments.view', 'payments.confirm',
            'maintenance.view', 'maintenance.respond',
            'messages.send', 'messages.view',
            'documents.view', 'documents.manage',
            'reports.view', 'audit.view', 'team.view', 'team.invite', 'team.manage',
        ]);

        $propertyManager = Role::firstOrCreate(['name' => 'property-manager']);
        $propertyManager->syncPermissions([
            'properties.view', 'properties.create', 'properties.edit',
            'units.view', 'units.create', 'units.edit',
            'tenants.view', 'tenants.create', 'tenants.edit',
            'leases.view', 'leases.create', 'leases.edit',
            'invoices.view',
            'maintenance.view', 'maintenance.respond',
            'messages.send', 'messages.view',
            'documents.view', 'documents.manage',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions([
            'properties.view', 'units.view', 'tenants.view', 'leases.view',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'payments.view', 'payments.confirm',
            'documents.view', 'documents.manage', 'reports.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'properties.view', 'units.view', 'tenants.view', 'leases.view',
            'invoices.view', 'payments.view', 'maintenance.view',
            'messages.view', 'documents.view', 'reports.view', 'team.view',
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
