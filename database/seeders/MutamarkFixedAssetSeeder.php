<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\AssetTransfer;
use App\Models\FixedAsset;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\WarrantyRegistration;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MutamarkFixedAssetSeeder extends Seeder
{
    /**
     * Seed enterprise fixed assets and warranty servicing for Mutamark ERP.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // 1. Asset Categories
        $categories = [
            [
                'name' => 'IT & Network Infrastructure',
                'code' => 'CAT-IT',
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 25.00,
                'useful_life_years' => 4,
            ],
            [
                'name' => 'Heavy Fleet & Transport Vehicles',
                'code' => 'CAT-FLEET',
                'depreciation_method' => 'reducing_balance',
                'depreciation_rate' => 20.00,
                'useful_life_years' => 5,
            ],
            [
                'name' => 'Power & Solar Inverter Systems',
                'code' => 'CAT-POWER',
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 15.00,
                'useful_life_years' => 7,
            ],
            [
                'name' => 'Office Furniture & Workspace Fixtures',
                'code' => 'CAT-FURN',
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 10.00,
                'useful_life_years' => 10,
            ],
        ];

        $catMap = [];
        foreach ($categories as $cat) {
            $created = AssetCategory::updateOrCreate(
                ['code' => $cat['code']],
                array_merge($cat, ['creator_id' => $adminId, 'created_by' => $adminId])
            );
            $catMap[$cat['code']] = $created->id;
        }

        // 2. Fixed Assets in TZS
        $assets = [
            [
                'asset_code' => 'AST-2026-00001',
                'name' => 'Dell PowerEdge R750 Enterprise Server',
                'asset_category_id' => $catMap['CAT-IT'],
                'purchase_date' => '2026-01-15',
                'purchase_cost' => 24500000.00, // 24.5M TZS
                'salvage_value' => 2000000.00,
                'current_value' => 19875000.00,
                'accumulated_depreciation' => 4625000.00,
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 25.00,
                'useful_life_years' => 4,
                'location' => 'Dar es Salaam HQ - Data Center Rack 04',
                'custodian_id' => $adminId,
                'serial_number' => 'SN-PE750-TZ-0019',
                'warranty_expiry_date' => '2029-01-15',
                'status' => 'active',
                'notes' => 'Primary production ERP & database cluster node with dual redundant power.',
            ],
            [
                'asset_code' => 'AST-2026-00002',
                'name' => 'Cisco Catalyst 9500 48-Port Fiber Core Switch',
                'asset_category_id' => $catMap['CAT-IT'],
                'purchase_date' => '2026-02-10',
                'purchase_cost' => 14800000.00, // 14.8M TZS
                'salvage_value' => 1500000.00,
                'current_value' => 12325000.00,
                'accumulated_depreciation' => 2475000.00,
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 25.00,
                'useful_life_years' => 4,
                'location' => 'Dar es Salaam HQ - Core Telecom Room',
                'custodian_id' => $adminId,
                'serial_number' => 'SN-CS9500-TZ-4412',
                'warranty_expiry_date' => '2028-02-10',
                'status' => 'active',
                'notes' => 'High-throughput 10G uplink switch for Mutamark campus backbone.',
            ],
            [
                'asset_code' => 'AST-2026-00003',
                'name' => 'Victron Energy 15kVA Solar Inverter & LiFePO4 Rack',
                'asset_category_id' => $catMap['CAT-POWER'],
                'purchase_date' => '2026-03-01',
                'purchase_cost' => 38000000.00, // 38.0M TZS
                'salvage_value' => 5000000.00,
                'current_value' => 34700000.00,
                'accumulated_depreciation' => 3300000.00,
                'depreciation_method' => 'straight_line',
                'depreciation_rate' => 15.00,
                'useful_life_years' => 7,
                'location' => 'Mwanza Regional Distribution Hub',
                'custodian_id' => $adminId,
                'serial_number' => 'SN-VIC15K-TZ-9821',
                'warranty_expiry_date' => '2031-03-01',
                'status' => 'active',
                'notes' => 'Backup off-grid solar power generator for cold-chain and server uptime.',
            ],
            [
                'asset_code' => 'AST-2026-00004',
                'name' => 'Toyota Hilux Double Cabin Field Utility (T 482 DXK)',
                'asset_category_id' => $catMap['CAT-FLEET'],
                'purchase_date' => '2026-01-20',
                'purchase_cost' => 95000000.00, // 95.0M TZS
                'salvage_value' => 20000000.00,
                'current_value' => 81500000.00,
                'accumulated_depreciation' => 13500000.00,
                'depreciation_method' => 'reducing_balance',
                'depreciation_rate' => 20.00,
                'useful_life_years' => 5,
                'location' => 'Arusha Tech & Logistics Center',
                'custodian_id' => $adminId,
                'serial_number' => 'VIN-THX-2026-TZ-041',
                'warranty_expiry_date' => '2029-01-20',
                'status' => 'active',
                'notes' => 'Regional fleet maintenance vehicle deployed for on-site customer service.',
            ],
        ];

        $assetMap = [];
        foreach ($assets as $assetData) {
            $createdAsset = FixedAsset::updateOrCreate(
                ['asset_code' => $assetData['asset_code']],
                array_merge($assetData, ['creator_id' => $adminId, 'created_by' => $adminId])
            );
            $assetMap[$assetData['asset_code']] = $createdAsset;
        }

        // 3. Asset Depreciations for Period 2026-10
        foreach ($assetMap as $asset) {
            $monthlyAmount = $asset->computeMonthlyDepreciation();
            if ($monthlyAmount > 0) {
                AssetDepreciation::updateOrCreate(
                    [
                        'asset_id' => $asset->id,
                        'period' => '2026-10',
                    ],
                    [
                        'fiscal_year' => 2026,
                        'opening_value' => (float) $asset->current_value + $monthlyAmount,
                        'depreciation_amount' => $monthlyAmount,
                        'closing_value' => (float) $asset->current_value,
                        'posted_by' => $adminId,
                        'posted_at' => now(),
                        'creator_id' => $adminId,
                        'created_by' => $adminId,
                    ]
                );
            }
        }

        // 4. Asset Transfer History
        AssetTransfer::updateOrCreate(
            ['transfer_number' => 'TRF-2026-00001'],
            [
                'asset_id' => $assetMap['AST-2026-00002']->id,
                'transfer_date' => '2026-08-15',
                'from_location' => 'Dar es Salaam HQ - Staging Warehouse',
                'to_location' => 'Dar es Salaam HQ - Core Telecom Room',
                'from_custodian_id' => $adminId,
                'to_custodian_id' => $adminId,
                'reason' => 'Commissioning and cutover to production core network rack.',
                'approved_by' => $adminId,
                'creator_id' => $adminId,
                'created_by' => $adminId,
            ]
        );

        // 5. Warranties (SRS 3.21)
        $warranties = [
            [
                'warranty_code' => 'WAR-2026-00001',
                'serial_number' => 'SN-MK2116-TZ-8841',
                'customer_name' => 'Tanzania Ports Authority (TPA)',
                'customer_phone' => '+255 747 003 357',
                'customer_email' => 'noc@tpa.go.tz',
                'start_date' => '2026-02-01',
                'end_date' => '2028-02-01',
                'warranty_period_months' => 24,
                'warranty_terms' => 'Full replacement warranty for manufacturing defects & hardware port failure.',
                'status' => 'active',
            ],
            [
                'warranty_code' => 'WAR-2026-00002',
                'serial_number' => 'SN-PE750-TZ-0019',
                'customer_name' => 'Internal Asset Custody (Mutamark IT HQ)',
                'customer_phone' => '+255 747 003 357',
                'customer_email' => 'admin@mutamark.co.tz',
                'start_date' => '2026-01-15',
                'end_date' => '2029-01-15',
                'warranty_period_months' => 36,
                'warranty_terms' => 'Dell ProSupport Plus 24x7 Mission Critical on-site technician response.',
                'status' => 'active',
            ],
            [
                'warranty_code' => 'WAR-2026-00003',
                'serial_number' => 'SN-ZBR411-TZ-0043',
                'customer_name' => 'Bakhresa Grain Milling Logistics',
                'customer_phone' => '+255 754 881 200',
                'customer_email' => 'stores@bakhresa.com',
                'start_date' => '2025-05-10',
                'end_date' => '2026-05-10',
                'warranty_period_months' => 12,
                'warranty_terms' => 'OEM Standard Zebra printhead and thermal roller 1-year coverage.',
                'status' => 'expired',
            ],
        ];

        $warMap = [];
        foreach ($warranties as $war) {
            $createdWar = WarrantyRegistration::updateOrCreate(
                ['warranty_code' => $war['warranty_code']],
                array_merge($war, ['creator_id' => $adminId, 'created_by' => $adminId])
            );
            $warMap[$war['warranty_code']] = $createdWar;
        }

        // 6. Service & Repair Tickets (RMA Workflow - SRS 3.21)
        $tickets = [
            [
                'ticket_number' => 'SRV-2026-00001',
                'warranty_id' => $warMap['WAR-2026-00001']->id,
                'customer_name' => 'Tanzania Ports Authority (TPA)',
                'contact_phone' => '+255 747 003 357',
                'product_name' => 'Mikrotik CCR2116 Router',
                'serial_number' => 'SN-MK2116-TZ-8841',
                'service_type' => 'warranty_claim',
                'issue_reported' => 'SFP+ optical port 1 intermittently flapping under high 10Gbps traffic.',
                'diagnostic_findings' => 'Optics transceiver module overheating; chassis fan speed calibrated.',
                'vendor_repair' => false,
                'estimated_cost' => 0.00,
                'actual_cost' => 0.00,
                'assigned_technician_id' => $adminId,
                'status' => 'diagnosing',
            ],
            [
                'ticket_number' => 'SRV-2026-00002',
                'warranty_id' => $warMap['WAR-2026-00003']->id,
                'customer_name' => 'Bakhresa Grain Milling Logistics',
                'contact_phone' => '+255 754 881 200',
                'product_name' => 'Zebra ZT411 Industrial Printer',
                'serial_number' => 'SN-ZBR411-TZ-0043',
                'service_type' => 'paid_repair',
                'issue_reported' => 'Thermal printhead lines missing across 300DPI barcode labels.',
                'diagnostic_findings' => 'Thermal printhead element scratched. Dispatched to manufacturer distributor.',
                'vendor_repair' => true,
                'vendor_name' => 'Redington Tanzania (Zebra Authorized Service Center)',
                'vendor_tracking_ref' => 'RMA-RED-TZ-2026-88',
                'estimated_cost' => 850000.00,
                'actual_cost' => 850000.00,
                'assigned_technician_id' => $adminId,
                'status' => 'sent_to_vendor',
            ],
            [
                'ticket_number' => 'SRV-2026-00003',
                'warranty_id' => null,
                'customer_name' => 'Mwanza Regional Distribution Hub',
                'contact_phone' => '+255 768 440 119',
                'product_name' => 'Solar Charge Controller MPPT 250/100',
                'serial_number' => 'SN-MPPT-TZ-3312',
                'service_type' => 'preventive_maintenance',
                'issue_reported' => 'Scheduled quarterly firmware update and heat sink dust cleaning.',
                'diagnostic_findings' => 'Capacitor integrity verified, firmware updated to v3.24.',
                'vendor_repair' => false,
                'estimated_cost' => 150000.00,
                'actual_cost' => 120000.00,
                'assigned_technician_id' => $adminId,
                'status' => 'repaired',
                'completed_at' => now(),
            ],
        ];

        foreach ($tickets as $tkt) {
            ServiceTicket::updateOrCreate(
                ['ticket_number' => $tkt['ticket_number']],
                array_merge($tkt, ['creator_id' => $adminId, 'created_by' => $adminId])
            );
        }

        $this->command->info('Mutamark Fixed Assets, Depreciation Schedules, Warranties, and RMA Tickets seeded successfully in TZS!');
    }
}
