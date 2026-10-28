<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AddOn;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Workdo\Pos\Models\PosBillingCounter;
use Workdo\Pos\Models\PosDiscount;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\ProductServiceCategory;
use Workdo\ProductService\Models\ProductServiceTax;
use Workdo\ProductService\Models\WarehouseStock;

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
                    'manage-stock', 'create-stock',
                    'manage-product-service-item', 'view-product-service-item', 'create-product-service-item', 'edit-product-service-item', 'delete-product-service-item',
                    'manage-product-service-categories', 'create-product-service-categories', 'edit-product-service-categories',
                    'manage-product-service-units', 'create-product-service-units',
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
                    'manage-product-service-item', 'view-product-service-item', 'create-product-service-item',
                    'manage-pos', 'view-pos', 'create-pos', 'manage-pos-orders', 'view-pos-orders',
                    'manage-pos-returns', 'manage-pos-barcodes', 'manage-pos-billing-counters', 'manage-pos-discounts', 'manage-pos-reports',
                    'manage-sales-proposals', 'view-sales-proposals', 'create-sales-proposals', 'edit-sales-proposals', 'sent-sales-proposals',
                    'manage-sales-orders', 'view-sales-orders', 'create-sales-orders', 'edit-sales-orders',
                    'manage-proformas', 'view-proformas', 'create-proformas', 'convert-proformas',
                    'manage-sales-invoices', 'view-sales-invoices', 'create-sales-invoices', 'print-sales-invoices',
                    'manage-customers', 'view-customers', 'create-customers', 'edit-customers',
                    'manage-delivery-notes', 'view-delivery-notes', 'create-delivery-notes',
                    'manage-profile', 'edit-profile',
                ]
            ],
            'pos_cashier' => [
                'label' => 'POS Cashier & Retail Desk',
                'permissions' => [
                    'manage-dashboard',
                    'manage-pos', 'view-pos', 'create-pos',
                    'manage-pos-orders', 'view-pos-orders',
                    'manage-pos-returns', 'manage-pos-barcodes', 'manage-pos-discounts', 'manage-pos-reports',
                    'view-product-service-item',
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
                'role' => 'pos_cashier',
                'secondary_role' => 'staff',
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
                    'is_disable' => 0,
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

        // -----------------------------------------------------------------
        // Core Add-Ons Activation (ProductService, Pos, Inventory, etc.)
        // -----------------------------------------------------------------
        $coreAddons = [
            ['module' => 'ProductService', 'name' => 'Product & Service', 'package_name' => 'product-service', 'priority' => 0],
            ['module' => 'Account', 'name' => 'Accounting', 'package_name' => 'account', 'priority' => 10],
            ['module' => 'Hrm', 'name' => 'HRM', 'package_name' => 'hrm', 'priority' => 20],
            ['module' => 'Inventory', 'name' => 'Inventory', 'package_name' => null, 'priority' => 30],
            ['module' => 'Pos', 'name' => 'POS', 'package_name' => 'pos', 'priority' => 40],
            ['module' => 'Lead', 'name' => 'CRM', 'package_name' => 'lead', 'priority' => 50],
            ['module' => 'Taskly', 'name' => 'Project', 'package_name' => 'taskly', 'priority' => 60],
            ['module' => 'Fleet', 'name' => 'Fleet', 'package_name' => 'fleet', 'priority' => 70],
            ['module' => 'LandingPage', 'name' => 'CMS', 'package_name' => 'landing-page', 'priority' => 80],
            ['module' => 'MobileServiceManagement', 'name' => 'Mobile Service Management', 'package_name' => 'mobile-service-management', 'priority' => 90],
        ];

        foreach ($coreAddons as $ca) {
            AddOn::updateOrCreate(
                ['module' => $ca['module']],
                [
                    'name' => $ca['name'],
                    'package_name' => $ca['package_name'],
                    'priority' => $ca['priority'],
                    'is_enable' => 1,
                    'monthly_price' => 0,
                    'yearly_price' => 0,
                ]
            );
        }

        // -----------------------------------------------------------------
        // Warehouses
        // -----------------------------------------------------------------
        $warehousesData = [
            [
                'name' => 'Dar es Salaam Central Warehouse',
                'address' => 'Plot 45, Nyerere Road, Industrial Area',
                'city' => 'Dar es Salaam',
                'zip_code' => '11101',
                'phone' => '+255 22 211 4455',
                'email' => 'dar.warehouse@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ],
            [
                'name' => 'Arusha Regional Depot',
                'address' => 'Njiro Road, Near Complex',
                'city' => 'Arusha',
                'zip_code' => '23100',
                'phone' => '+255 27 254 8899',
                'email' => 'arusha.depot@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ],
            [
                'name' => 'Mwanza Lake-Zone Depot',
                'address' => 'Capripoint, Kenyatta Road',
                'city' => 'Mwanza',
                'zip_code' => '33100',
                'phone' => '+255 28 250 1122',
                'email' => 'mwanza.depot@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ],
        ];

        $warehouseModels = [];
        foreach ($warehousesData as $wd) {
            $wh = Warehouse::updateOrCreate(
                ['name' => $wd['name'], 'created_by' => $companyId],
                $wd
            );
            $warehouseModels[] = $wh;
        }

        // -----------------------------------------------------------------
        // POS Billing Counters & Operating Bank Account
        // -----------------------------------------------------------------
        $bankAccountId = null;
        if (Schema::hasTable('bank_accounts')) {
            $glAccountId = null;
            if (Schema::hasTable('chart_of_accounts')) {
                $glAccountId = DB::table('chart_of_accounts')
                    ->where('created_by', $companyId)
                    ->whereIn('account_code', ['1010', '1020', '1000'])
                    ->value('id');
            }

            $bankAccount = DB::table('bank_accounts')->where('created_by', $companyId)->first();
            if (!$bankAccount) {
                try {
                    $bankAccountId = DB::table('bank_accounts')->insertGetId([
                        'account_number'  => '0150123456700',
                        'account_name'    => 'Mutamark Enterprises Operating Account',
                        'bank_name'       => 'CRDB Bank Plc',
                        'branch_name'     => 'Tower Branch, Dar es Salaam',
                        'account_type'    => '0',
                        'gl_account_id'   => $glAccountId,
                        'opening_balance' => 50000000.00,
                        'current_balance' => 50000000.00,
                        'is_active'       => 1,
                        'creator_id'      => $companyId,
                        'created_by'      => $companyId,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                } catch (\Throwable $e) {
                    $bankAccountId = null;
                }
            } else {
                $bankAccountId = $bankAccount->id;
                if ($glAccountId && empty($bankAccount->gl_account_id) && Schema::hasColumn('bank_accounts', 'gl_account_id')) {
                    DB::table('bank_accounts')->where('id', $bankAccount->id)->update(['gl_account_id' => $glAccountId]);
                }
            }

            // Verify the ID actually exists before assigning to avoid foreign key violations
            if ($bankAccountId && !DB::table('bank_accounts')->where('id', $bankAccountId)->exists()) {
                $bankAccountId = null;
            }
        }

        $countersData = [
            [
                'name' => 'Counter 01 - Dar Showroom Retail Desk',
                'code' => 'CTR-01',
                'bank_account_id' => $bankAccountId,
                'status' => 1,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ],
            [
                'name' => 'Counter 02 - Express POS Checkout',
                'code' => 'CTR-02',
                'bank_account_id' => $bankAccountId,
                'status' => 1,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ],
        ];

        foreach ($countersData as $cd) {
            PosBillingCounter::updateOrCreate(
                ['code' => $cd['code'], 'created_by' => $companyId],
                $cd
            );
        }

        // -----------------------------------------------------------------
        // Product Categories & VAT Tax
        // -----------------------------------------------------------------
        $categoriesData = [
            ['name' => 'Enterprise Hardware', 'color' => '#2563eb'],
            ['name' => 'Networking & Telecom', 'color' => '#0284c7'],
            ['name' => 'POS & Auto-ID', 'color' => '#16a34a'],
            ['name' => 'Power & Datacenter', 'color' => '#d97706'],
        ];

        $catMap = [];
        foreach ($categoriesData as $cat) {
            $cModel = ProductServiceCategory::updateOrCreate(
                ['name' => $cat['name'], 'created_by' => $companyId],
                [
                    'color' => $cat['color'],
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ]
            );
            $catMap[$cat['name']] = $cModel->id;
        }

        $vatTax = ProductServiceTax::updateOrCreate(
            ['tax_name' => 'VAT 18%', 'created_by' => $companyId],
            [
                'rate' => 18.00,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ]
        );

        // -----------------------------------------------------------------
        // 6 High-Quality Demo Products with Images
        // -----------------------------------------------------------------
        $productsData = [
            [
                'name' => 'High-Capacity Industrial Barcode Scanner',
                'sku' => 'MUT-HW-BCSCAN',
                'sale_price' => 350000.00,
                'purchase_price' => 210000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_scanner.jpg',
                'category_id' => $catMap['POS & Auto-ID'] ?? null,
                'description' => 'Industrial grade 2D/QR wireless barcode scanner with charging cradle and ultra-fast optical sensor.',
                'is_active' => true,
            ],
            [
                'name' => 'Direct Thermal Receipt Printer 80mm',
                'sku' => 'MUT-HW-PRINTER',
                'sale_price' => 280000.00,
                'purchase_price' => 170000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_printer.jpg',
                'category_id' => $catMap['POS & Auto-ID'] ?? null,
                'description' => 'High-speed 260mm/s auto-cutter thermal receipt printer with USB, LAN and Serial interfaces.',
                'is_active' => true,
            ],
            [
                'name' => 'Industrial 4G/5G Enterprise Gateway Router',
                'sku' => 'MUT-HW-RTR5G',
                'sale_price' => 750000.00,
                'purchase_price' => 490000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_router.jpg',
                'category_id' => $catMap['Networking & Telecom'] ?? null,
                'description' => 'Ruggedized dual-SIM failover Gigabit router for enterprise branch networks and remote sites.',
                'is_active' => true,
            ],
            [
                'name' => 'High-Density 48-Port Fiber Distribution Panel',
                'sku' => 'MUT-HW-FIB48',
                'sale_price' => 420000.00,
                'purchase_price' => 260000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_fiber_panel.jpg',
                'category_id' => $catMap['Networking & Telecom'] ?? null,
                'description' => '1U 19-inch rackmount optical distribution frame equipped with 48 SC/APC duplex couplers.',
                'is_active' => true,
            ],
            [
                'name' => 'Online Pure Sine Wave Rackmount UPS 3kVA',
                'sku' => 'MUT-HW-UPS3K',
                'sale_price' => 1850000.00,
                'purchase_price' => 1200000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_ups_system.jpg',
                'category_id' => $catMap['Power & Datacenter'] ?? null,
                'description' => 'Double conversion online UPS with 2700W active power, zero transfer time, and LCD management.',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise 24-Port Gigabit PoE+ Managed Switch',
                'sku' => 'MUT-HW-SW24P',
                'sale_price' => 920000.00,
                'purchase_price' => 610000.00,
                'type' => 'product',
                'unit' => 'Pcs',
                'image' => 'pos_switch.jpg',
                'category_id' => $catMap['Enterprise Hardware'] ?? null,
                'description' => 'Layer 2+ managed PoE switch with 370W total power budget and 4x 10G SFP+ uplink ports.',
                'is_active' => true,
            ],
        ];

        foreach ($productsData as $pd) {
            $item = ProductServiceItem::updateOrCreate(
                ['sku' => $pd['sku'], 'created_by' => $companyId],
                array_merge($pd, [
                    'tax_ids' => [$vatTax->id],
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ])
            );

            // Seed inventory in all warehouses
            foreach ($warehouseModels as $wh) {
                WarehouseStock::updateOrCreate(
                    ['product_id' => $item->id, 'warehouse_id' => $wh->id],
                    ['quantity' => rand(25, 60)]
                );
            }
        }

        // Active POS Discount
        PosDiscount::updateOrCreate(
            ['name' => '10% Retail Hardware Promo', 'created_by' => $companyId],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_quantity' => 1,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(90)->toDateString(),
                'is_active' => 1,
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ]
        );

        echo "Successfully seeded Mutamark Niche Users, Roles, Core Modules, Warehouses and Products with Stock!\n";
    }
}
