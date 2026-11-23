<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BusinessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        $registrar->forgetCachedPermissions();

        /*
         * Shared permission definitions.
         */
        $permissions = [
            'dashboard.view',

            // Sales
            'sales.view.own',
            'sales.view.all',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.refund',
            'sales.discount',
            'sales.void',

            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Categories
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Inventory
            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'inventory.count',

            // Purchases
            'purchases.view',
            'purchases.create',
            'purchases.approve',
            'purchases.receive',

            // Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // Expenses
            'expenses.view',
            'expenses.create',
            'expenses.approve',
            'expenses.delete',

            // Reports
            'reports.view.sales',
            'reports.view.inventory',
            'reports.view.profit',
            'reports.view.own_sales',

            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.assign_roles',

            // Settings
            'settings.view',
            'settings.manage',

            // Branches
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
        ];

        /*
         * Create shared permissions.
         */
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
         * Default permissions for each role.
         */
        $rolePermissions = [

            'Owner' => $permissions,

            'Manager' => [
                'dashboard.view',

                'sales.view.all',
                'sales.create',
                'sales.edit',
                'sales.discount',
                'sales.void',

                'products.view',
                'products.create',
                'products.edit',

                'categories.view',
                'categories.create',
                'categories.edit',

                'inventory.view',
                'inventory.adjust',
                'inventory.transfer',
                'inventory.count',

                'purchases.view',
                'purchases.create',
                'purchases.approve',
                'purchases.receive',

                'customers.view',
                'customers.create',
                'customers.edit',

                'suppliers.view',
                'suppliers.create',
                'suppliers.edit',

                'expenses.view',
                'expenses.create',
                'expenses.approve',

                'reports.view.sales',
                'reports.view.inventory',
                'reports.view.profit',

                'staff.view',
                'staff.create',
                'staff.edit',

                'settings.view',

                'branches.view',
                'branches.create',
                'branches.edit',
            ],

            'Cashier' => [
                'dashboard.view',

                'sales.view.own',
                'sales.create',
                'sales.discount',

                'products.view',
                'categories.view',

                'customers.view',
                'customers.create',
                'customers.edit',

                'reports.view.own_sales',
            ],

            'Inventory Officer' => [
                'dashboard.view',

                'sales.view.all',

                'products.view',
                'products.create',
                'products.edit',
                'categories.view',

                'inventory.view',
                'inventory.adjust',
                'inventory.transfer',
                'inventory.count',

                'purchases.view',
                'purchases.create',
                'purchases.receive',

                'suppliers.view',
                'suppliers.create',
                'suppliers.edit',

                'reports.view.inventory',
            ],
        ];

        /*
         * Preserve the previous team context.
         */
        $previousTeamId = $registrar->getPermissionsTeamId();

        try {

            /*
             * Assign permissions separately for every business.
             */
            Business::query()->each(function (Business $business) use (
                $registrar,
                $rolePermissions
            ) {

                $registrar->setPermissionsTeamId($business->id);

                foreach ($rolePermissions as $roleName => $permissions) {

                    $role = Role::firstOrCreate([
                        'name' => $roleName,
                        'guard_name' => 'web',
                        'business_id' => $business->id,
                    ]);

                    // Add defaults without removing custom permissions.
                    $role->givePermissionTo($permissions);
                }
            });

        } finally {

            /*
             * Restore the previous team context.
             */
            $registrar->setPermissionsTeamId($previousTeamId);

            $registrar->forgetCachedPermissions();
        }
    }
}
