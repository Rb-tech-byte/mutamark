<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MutamarkNicheUsersSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('type', 'superadmin')->first();
        if (!$superAdmin) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('1234'),
                'type' => 'superadmin',
                'is_enable_login' => 1,
                'email_verified_at' => now(),
            ]);
        } else {
            $superAdmin->update([
                'password' => Hash::make('1234'),
                'is_enable_login' => 1,
            ]);
        }
        $superAdmin->assignRole('superadmin');

        $company = User::where('type', 'company')->first();
        if (!$company) {
            $company = User::create([
                'name' => 'Mutamark Enterprises Ltd',
                'email' => 'company@example.com',
                'password' => Hash::make('1234'),
                'type' => 'company',
                'is_enable_login' => 1,
                'creator_id' => $superAdmin->id,
                'created_by' => $superAdmin->id,
                'email_verified_at' => now(),
            ]);
        } else {
            $company->update([
                'password' => Hash::make('1234'),
                'is_enable_login' => 1,
            ]);
        }
        $company->assignRole('company');
        $companyId = $company->id;

        // Ensure base company roles exist
        User::MakeRole($companyId);

        // Define Niche Roles for Company
        $nicheRolesConfig = [
            'finance_manager' => [
                'label' => 'Head of Finance & Tax Compliance',
                'permissions' => [
                    'manage-dashboard',
                    'manage-account', 'manage-chart-of-account', 'manage-journal-entry',
                    'manage-bank-account', 'manage-bank-transaction', 'manage-bank-transfer',
                    'manage-customer-payment', 'manage-vendor-payment', 'manage-cleared-customer-payments',
                    'manage-cleared-vendor-payments', 'manage-sales-invoices', 'view-sales-invoices',
                    'create-sales-invoices', 'edit-sales-invoices', 'post-sales-invoices', 'print-sales-invoices',
                    'manage-purchase-invoices', 'view-purchase-invoices', 'create-purchase-invoices',
                    'edit-purchase-invoices', 'post-purchase-invoices', 'print-purchase-invoices',
                    'manage-tra-fiscal', 'manage-three-way-matching', 'manage-financial-audit-trail',
                    'manage-forex', 'manage-settings', 'manage-profile', 'edit-profile',
                ]
            ],
            'warehouse_manager' => [
                'label' => 'Logistics & Warehouse Manager',
                'permissions' => [
                    'manage-dashboard',
                    'manage-warehouses', 'view-warehouses', 'create-warehouses', 'edit-warehouses',
                    'manage-transfers', 'view-transfers', 'create-transfers', 'dispatch-transfers', 'receive-transfers',
                    'manage-stock-adjustments', 'view-stock-adjustments', 'create-stock-adjustments', 'post-stock-adjustments',
                    'manage-inventory-serials', 'view-inventory-serials', 'create-inventory-serials',
                    'manage-delivery-notes', 'view-delivery-notes', 'create-delivery-notes', 'dispatch-delivery-notes',
                    'manage-goods-received-notes', 'view-goods-received-notes', 'post-goods-received-notes',
                    'manage-profile', 'edit-profile',
                ]
            ],
            'sales_manager' => [
                'label' => 'Commercial Sales & CRM Executive',
                'permissions' => [
                    'manage-dashboard',
                    'manage-sales-proposals', 'view-sales-proposals', 'create-sales-proposals', 'edit-sales-proposals', 'sent-sales-proposals',
                    'manage-sales-orders', 'view-sales-orders', 'create-sales-orders', 'edit-sales-orders',
                    'manage-proformas', 'view-proformas', 'create-proformas', 'convert-proformas',
                    'manage-sales-invoices', 'view-sales-invoices', 'create-sales-invoices', 'print-sales-invoices',
                    'manage-customers', 'view-customers', 'create-customers', 'edit-customers',
                    'manage-delivery-notes', 'view-delivery-notes', 'create-delivery-notes',
                    'manage-profile', 'edit-profile',
                ]
            ],
            'hr_manager' => [
                'label' => 'HR & Statutory Payroll Specialist',
                'permissions' => [
                    'manage-dashboard',
                    'manage-hrm', 'manage-employees', 'view-employees', 'create-employees', 'edit-employees',
                    'manage-branches', 'manage-departments', 'manage-designations',
                    'manage-attendance', 'manage-leaves', 'manage-payrolls', 'manage-any-payrolls',
                    'manage-statutory-declarations', 'manage-profile', 'edit-profile',
                ]
            ]
        ];

        $allPermissionNames = Permission::pluck('name')->toArray();

        foreach ($nicheRolesConfig as $roleName => $cfg) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'created_by' => $companyId, 'guard_name' => 'web'],
                ['label' => $cfg['label'], 'editable' => false, 'created_by' => $companyId, 'guard_name' => 'web']
            );

            // Filter valid permissions
            $validPerms = array_intersect($cfg['permissions'], $allPermissionNames);
            if (!empty($validPerms)) {
                $role->syncPermissions($validPerms);
            }
        }

        // 3. Define Niche System Users
        $nicheUsers = [
            [
                'email' => 'amina.kibona@mutamark.co.tz',
                'name' => 'Amina Salum Kibona',
                'type' => 'employee',
                'role' => 'finance_manager',
                'secondary_role' => 'staff',
                'mobile_no' => '+255 754 112 001',
            ],
            [
                'email' => 'baraka.mwangi@mutamark.co.tz',
                'name' => 'Baraka Joseph Mwangi',
                'type' => 'employee',
                'role' => 'warehouse_manager',
                'secondary_role' => 'staff',
                'mobile_no' => '+255 768 445 002',
            ],
            [
                'email' => 'zawadi.mushi@mutamark.co.tz',
                'name' => 'Zawadi Emmanuel Mushi',
                'type' => 'employee',
                'role' => 'sales_manager',
                'secondary_role' => 'staff',
                'mobile_no' => '+255 784 990 003',
            ],
            [
                'email' => 'juma.mwalimu@mutamark.co.tz',
                'name' => 'Juma Bakari Mwalimu',
                'type' => 'employee',
                'role' => 'hr_manager',
                'secondary_role' => 'staff',
                'mobile_no' => '+255 777 332 004',
            ],
            [
                'email' => 'daudi.mfaume@mutamark.co.tz',
                'name' => 'Daudi Hamisi Mfaume',
                'type' => 'employee',
                'role' => 'staff',
                'secondary_role' => null,
                'mobile_no' => '+255 713 554 005',
            ],
            [
                'email' => 'finance@safariholdings.co.tz',
                'name' => 'Safari Holdings Ltd',
                'type' => 'client',
                'role' => 'client',
                'secondary_role' => null,
                'mobile_no' => '+255 754 112 233',
            ],
            [
                'email' => 'sales@darindustrial.co.tz',
                'name' => 'Dar Industrial Hardware Supplies Ltd',
                'type' => 'vendor',
                'role' => 'vendor',
                'secondary_role' => null,
                'mobile_no' => '+255 713 554 433',
            ],
        ];

        foreach ($nicheUsers as $nu) {
            $user = User::updateOrCreate(
                ['email' => $nu['email']],
                [
                    'name' => $nu['name'],
                    'password' => Hash::make('1234'), // Set to 1234 for easy testing, also works with password123
                    'type' => $nu['type'],
                    'mobile_no' => $nu['mobile_no'],
                    'is_enable_login' => 1,
                    'is_active' => 1,
                    'email_verified_at' => now(),
                    'created_by' => $companyId,
                    'creator_id' => $companyId,
                ]
            );

            // Assign roles
            $rolesToAssign = [];
            if (!empty($nu['role'])) {
                $rolesToAssign[] = $nu['role'];
            }
            if (!empty($nu['secondary_role'])) {
                $rolesToAssign[] = $nu['secondary_role'];
            }

            if (!empty($rolesToAssign)) {
                $user->syncRoles($rolesToAssign);
            }
        }

        echo "Successfully seeded Mutamark Niche Users and Roles!\n";
    }
}
