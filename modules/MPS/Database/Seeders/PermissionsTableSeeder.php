<?php

namespace Modules\MPS\Database\Seeders;

use Illuminate\Support\Str;
use Modules\MPS\Models\Role;
use Illuminate\Database\Seeder;
use Modules\MPS\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        //POs
        Permission::firstOrCreate(['name' => 'pos-sales']);
        Permission::firstOrCreate(['name' => 'read-orders']);
        Permission::firstOrCreate(['name' => 'create-orders']);
        Permission::firstOrCreate(['name' => 'update-orders']);
        Permission::firstOrCreate(['name' => 'delete-orders']);

        // sales
        Permission::firstOrCreate(['name' => 'read-sales']);
        Permission::firstOrCreate(['name' => 'update-sales']);
        Permission::firstOrCreate(['name' => 'create-sales']);
        Permission::firstOrCreate(['name' => 'delete-sales']);
        Permission::firstOrCreate(['name' => 'email-sales']);
        Permission::firstOrCreate(['name' => 'pdf-sales']);
        Permission::firstOrCreate(['name' => 'import-sales']);
        Permission::firstOrCreate(['name' => 'export-sales']);
        // deliveries
        Permission::firstOrCreate(['name' => 'read-deliveries']);
        Permission::firstOrCreate(['name' => 'update-deliveries']);
        Permission::firstOrCreate(['name' => 'create-deliveries']);
        Permission::firstOrCreate(['name' => 'delete-deliveries']);
        Permission::firstOrCreate(['name' => 'email-deliveries']);
        // recurring-sales
        Permission::firstOrCreate(['name' => 'read-recurring-sales']);
        Permission::firstOrCreate(['name' => 'update-recurring-sales']);
        Permission::firstOrCreate(['name' => 'create-recurring-sales']);
        Permission::firstOrCreate(['name' => 'delete-recurring-sales']);
        Permission::firstOrCreate(['name' => 'email-recurring-sales']);

        // customers
        Permission::firstOrCreate(['name' => 'read-customers']);
        Permission::firstOrCreate(['name' => 'update-customers']);
        Permission::firstOrCreate(['name' => 'create-customers']);
        Permission::firstOrCreate(['name' => 'delete-customers']);
        Permission::firstOrCreate(['name' => 'import-customers']);
        Permission::firstOrCreate(['name' => 'export-customers']);
        Permission::firstOrCreate(['name' => 'transaction-customers']);

        // purchases
        Permission::firstOrCreate(['name' => 'read-purchases']);
        Permission::firstOrCreate(['name' => 'update-purchases']);
        Permission::firstOrCreate(['name' => 'create-purchases']);
        Permission::firstOrCreate(['name' => 'delete-purchases']);
        Permission::firstOrCreate(['name' => 'email-purchases']);
        Permission::firstOrCreate(['name' => 'pdf-purchases']);
        Permission::firstOrCreate(['name' => 'import-purchases']);
        Permission::firstOrCreate(['name' => 'export-purchases']);

        // suppliers
        Permission::firstOrCreate(['name' => 'read-suppliers']);
        Permission::firstOrCreate(['name' => 'update-suppliers']);
        Permission::firstOrCreate(['name' => 'create-suppliers']);
        Permission::firstOrCreate(['name' => 'delete-suppliers']);
        Permission::firstOrCreate(['name' => 'import-suppliers']);
        Permission::firstOrCreate(['name' => 'export-suppliers']);
        Permission::firstOrCreate(['name' => 'transaction-suppliers']);

        // quotations
        Permission::firstOrCreate(['name' => 'read-quotations']);
        Permission::firstOrCreate(['name' => 'update-quotations']);
        Permission::firstOrCreate(['name' => 'create-quotations']);
        Permission::firstOrCreate(['name' => 'delete-quotations']);
        Permission::firstOrCreate(['name' => 'email-quotations']);
        Permission::firstOrCreate(['name' => 'pdf-quotations']);

        // return-orders
        Permission::firstOrCreate(['name' => 'read-return-orders']);
        Permission::firstOrCreate(['name' => 'update-return-orders']);
        Permission::firstOrCreate(['name' => 'create-return-orders']);
        Permission::firstOrCreate(['name' => 'delete-return-orders']);
        Permission::firstOrCreate(['name' => 'email-return-orders']);
        Permission::firstOrCreate(['name' => 'pdf-return-orders']);

        // payments
        Permission::firstOrCreate(['name' => 'read-payments']);
        Permission::firstOrCreate(['name' => 'update-payments']);
        Permission::firstOrCreate(['name' => 'create-payments']);
        Permission::firstOrCreate(['name' => 'delete-payments']);
        Permission::firstOrCreate(['name' => 'email-payments']);
        // Permission::firstOrCreate(['name' => 'transactions-payments']);

        // expenses
        Permission::firstOrCreate(['name' => 'read-expenses']);
        Permission::firstOrCreate(['name' => 'update-expenses']);
        Permission::firstOrCreate(['name' => 'create-expenses']);
        Permission::firstOrCreate(['name' => 'delete-expenses']);
        Permission::firstOrCreate(['name' => 'email-expenses']);
        Permission::firstOrCreate(['name' => 'import-expenses']);
        Permission::firstOrCreate(['name' => 'export-expenses']);

        // incomes
        Permission::firstOrCreate(['name' => 'read-incomes']);
        Permission::firstOrCreate(['name' => 'update-incomes']);
        Permission::firstOrCreate(['name' => 'create-incomes']);
        Permission::firstOrCreate(['name' => 'delete-incomes']);
        Permission::firstOrCreate(['name' => 'email-incomes']);
        Permission::firstOrCreate(['name' => 'import-incomes']);
        Permission::firstOrCreate(['name' => 'export-incomes']);

        // items
        Permission::firstOrCreate(['name' => 'read-items']);
        Permission::firstOrCreate(['name' => 'update-items']);
        Permission::firstOrCreate(['name' => 'create-items']);
        Permission::firstOrCreate(['name' => 'delete-items']);
        Permission::firstOrCreate(['name' => 'label-items']);
        Permission::firstOrCreate(['name' => 'trail-items']);
        Permission::firstOrCreate(['name' => 'import-items']);
        Permission::firstOrCreate(['name' => 'export-items']);

        // modifiers
        Permission::firstOrCreate(['name' => 'read-modifiers']);
        Permission::firstOrCreate(['name' => 'update-modifiers']);
        Permission::firstOrCreate(['name' => 'create-modifiers']);
        Permission::firstOrCreate(['name' => 'delete-modifiers']);
        Permission::firstOrCreate(['name' => 'import-modifiers']);
        Permission::firstOrCreate(['name' => 'export-modifiers']);

        // gift-cards
        Permission::firstOrCreate(['name' => 'read-gift-cards']);
        Permission::firstOrCreate(['name' => 'update-gift-cards']);
        Permission::firstOrCreate(['name' => 'create-gift-cards']);
        Permission::firstOrCreate(['name' => 'delete-gift-cards']);
        Permission::firstOrCreate(['name' => 'log-gift-cards']);

        // stock-adjustments
        Permission::firstOrCreate(['name' => 'read-stock-adjustments']);
        Permission::firstOrCreate(['name' => 'update-stock-adjustments']);
        Permission::firstOrCreate(['name' => 'create-stock-adjustments']);
        Permission::firstOrCreate(['name' => 'delete-stock-adjustments']);

        // asset-transfers
        Permission::firstOrCreate(['name' => 'read-asset-transfers']);
        Permission::firstOrCreate(['name' => 'update-asset-transfers']);
        Permission::firstOrCreate(['name' => 'create-asset-transfers']);
        Permission::firstOrCreate(['name' => 'delete-asset-transfers']);
        // stock-transfers
        Permission::firstOrCreate(['name' => 'read-stock-transfers']);
        Permission::firstOrCreate(['name' => 'update-stock-transfers']);
        Permission::firstOrCreate(['name' => 'create-stock-transfers']);
        Permission::firstOrCreate(['name' => 'delete-stock-transfers']);

        // users
        Permission::firstOrCreate(['name' => 'read-users']);
        Permission::firstOrCreate(['name' => 'update-users']);
        Permission::firstOrCreate(['name' => 'create-users']);
        Permission::firstOrCreate(['name' => 'delete-users']);
        // roles
        Permission::firstOrCreate(['name' => 'read-roles']);
        Permission::firstOrCreate(['name' => 'update-roles']);
        Permission::firstOrCreate(['name' => 'create-roles']);
        Permission::firstOrCreate(['name' => 'delete-roles']);
        Permission::firstOrCreate(['name' => 'update-permissions']);
        // user salaries
        Permission::firstOrCreate(['name' => 'read-salaries']);
        Permission::firstOrCreate(['name' => 'update-salaries']);
        Permission::firstOrCreate(['name' => 'create-salaries']);
        Permission::firstOrCreate(['name' => 'delete-salaries']);

        // Settings
        // accounts
        Permission::firstOrCreate(['name' => 'read-accounts']);
        Permission::firstOrCreate(['name' => 'update-accounts']);
        Permission::firstOrCreate(['name' => 'create-accounts']);
        Permission::firstOrCreate(['name' => 'delete-accounts']);
        Permission::firstOrCreate(['name' => 'transaction-accounts']);
        // locations
        Permission::firstOrCreate(['name' => 'read-locations']);
        Permission::firstOrCreate(['name' => 'update-locations']);
        Permission::firstOrCreate(['name' => 'create-locations']);
        Permission::firstOrCreate(['name' => 'delete-locations']);
        // promos
        Permission::firstOrCreate(['name' => 'read-promos']);
        Permission::firstOrCreate(['name' => 'update-promos']);
        Permission::firstOrCreate(['name' => 'create-promos']);
        Permission::firstOrCreate(['name' => 'delete-promos']);
        // brands
        Permission::firstOrCreate(['name' => 'read-brands']);
        Permission::firstOrCreate(['name' => 'update-brands']);
        Permission::firstOrCreate(['name' => 'create-brands']);
        Permission::firstOrCreate(['name' => 'delete-brands']);
        Permission::firstOrCreate(['name' => 'import-brands']);
        Permission::firstOrCreate(['name' => 'export-brands']);
        // categories
        Permission::firstOrCreate(['name' => 'read-categories']);
        Permission::firstOrCreate(['name' => 'update-categories']);
        Permission::firstOrCreate(['name' => 'create-categories']);
        Permission::firstOrCreate(['name' => 'delete-categories']);
        Permission::firstOrCreate(['name' => 'import-categories']);
        Permission::firstOrCreate(['name' => 'export-categories']);
        // taxes
        Permission::firstOrCreate(['name' => 'read-taxes']);
        Permission::firstOrCreate(['name' => 'update-taxes']);
        Permission::firstOrCreate(['name' => 'create-taxes']);
        Permission::firstOrCreate(['name' => 'delete-taxes']);
        // fields
        Permission::firstOrCreate(['name' => 'read-fields']);
        Permission::firstOrCreate(['name' => 'update-fields']);
        Permission::firstOrCreate(['name' => 'create-fields']);
        Permission::firstOrCreate(['name' => 'delete-fields']);
        // customer-groups
        Permission::firstOrCreate(['name' => 'read-customer-groups']);
        Permission::firstOrCreate(['name' => 'update-customer-groups']);
        Permission::firstOrCreate(['name' => 'create-customer-groups']);
        Permission::firstOrCreate(['name' => 'delete-customer-groups']);
        // halls
        Permission::firstOrCreate(['name' => 'read-halls']);
        Permission::firstOrCreate(['name' => 'update-halls']);
        Permission::firstOrCreate(['name' => 'create-halls']);
        Permission::firstOrCreate(['name' => 'delete-halls']);
        // tables
        Permission::firstOrCreate(['name' => 'read-tables']);
        Permission::firstOrCreate(['name' => 'update-tables']);
        Permission::firstOrCreate(['name' => 'create-tables']);
        Permission::firstOrCreate(['name' => 'delete-tables']);
        // units
        Permission::firstOrCreate(['name' => 'read-units']);
        Permission::firstOrCreate(['name' => 'update-units']);
        Permission::firstOrCreate(['name' => 'create-units']);
        Permission::firstOrCreate(['name' => 'delete-units']);

        // reports
        Permission::firstOrCreate(['name' => 'read-report']);
        Permission::firstOrCreate(['name' => 'registers-report']);
        Permission::firstOrCreate(['name' => 'expiry-alerts-report']);
        Permission::firstOrCreate(['name' => 'quantity-alerts-report']);
        Permission::firstOrCreate(['name' => 'items-report']);
        Permission::firstOrCreate(['name' => 'sales-report']);
        Permission::firstOrCreate(['name' => 'purchases-report']);
        Permission::firstOrCreate(['name' => 'payments-report']);
        Permission::firstOrCreate(['name' => 'expenses-report']);
        Permission::firstOrCreate(['name' => 'incomes-report']);
        Permission::firstOrCreate(['name' => 'stock-transfers-report']);
        Permission::firstOrCreate(['name' => 'stock-adjustments-report']);
        Permission::firstOrCreate(['name' => 'time-clock-report']);
        Permission::firstOrCreate(['name' => 'activity-logs-report']);

        // calendar & events
        Permission::firstOrCreate(['name' => 'calendar']);
        Permission::firstOrCreate(['name' => 'read-events']);
        Permission::firstOrCreate(['name' => 'create-events']);
        Permission::firstOrCreate(['name' => 'update-events']);
        Permission::firstOrCreate(['name' => 'delete-events']);

        // Attachments & media
        Permission::firstOrCreate(['name' => 'delete-media']);
        Permission::firstOrCreate(['name' => 'alerts-report']);
        Permission::firstOrCreate(['name' => 'item-label-design']);

        $admin       = Role::findByName('admin');
        $super       = Role::findByName('super');
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $permission->assignRole($super);
            if (!Str::contains($permission->name, ['delete', 'destroy', 'import', 'export'])) {
                $permission->assignRole($admin);
            }
        }
    }
}
