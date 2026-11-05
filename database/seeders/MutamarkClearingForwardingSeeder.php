<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\ClearingForwarding\CFJob;
use App\Models\ClearingForwarding\CFShipment;
use App\Models\ClearingForwarding\CFCargoLine;
use App\Models\ClearingForwarding\CFContainer;
use App\Models\ClearingForwarding\CFDocumentChecklist;
use App\Models\ClearingForwarding\CFPermit;
use App\Models\ClearingForwarding\CFClassification;
use App\Models\ClearingForwarding\CFValuation;
use App\Models\ClearingForwarding\CFDeclaration;
use App\Models\ClearingForwarding\CFCustomsEvent;
use App\Models\ClearingForwarding\CFChargeEstimate;
use App\Models\ClearingForwarding\CFDisbursement;
use App\Models\ClearingForwarding\CFTransportOrder;
use App\Models\ClearingForwarding\CFTrackingEvent;
use App\Models\ClearingForwarding\CFDelivery;
use App\Models\ClearingForwarding\CFProofOfDelivery;
use App\Models\ClearingForwarding\CFJobCosting;

class MutamarkClearingForwardingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions
        $permissions = [
            ['name' => 'manage-cf-dashboard', 'module' => 'clearing_forwarding', 'label' => 'Manage C&F Dashboard'],
            ['name' => 'manage-cf-jobs', 'module' => 'clearing_forwarding', 'label' => 'Manage C&F Shipment Jobs'],
            ['name' => 'create-cf-jobs', 'module' => 'clearing_forwarding', 'label' => 'Create C&F Shipment Jobs'],
            ['name' => 'edit-cf-jobs', 'module' => 'clearing_forwarding', 'label' => 'Edit C&F Shipment Jobs'],
            ['name' => 'delete-cf-jobs', 'module' => 'clearing_forwarding', 'label' => 'Delete C&F Shipment Jobs'],
            ['name' => 'verify-cf-documents', 'module' => 'clearing_forwarding', 'label' => 'Verify C&F Documents'],
            ['name' => 'manage-cf-permits', 'module' => 'clearing_forwarding', 'label' => 'Manage C&F Regulatory Permits'],
            ['name' => 'manage-cf-classifications', 'module' => 'clearing_forwarding', 'label' => 'Manage HS Classification & Valuation'],
            ['name' => 'manage-cf-declarations', 'module' => 'clearing_forwarding', 'label' => 'Manage Customs Declarations & TANCIS'],
            ['name' => 'manage-cf-disbursements', 'module' => 'clearing_forwarding', 'label' => 'Manage Statutory Duties & Disbursements'],
            ['name' => 'manage-cf-transport', 'module' => 'clearing_forwarding', 'label' => 'Manage Haulage & Transport Orders'],
            ['name' => 'manage-cf-pod', 'module' => 'clearing_forwarding', 'label' => 'Manage Delivery & Proof of Delivery'],
            ['name' => 'manage-cf-costing', 'module' => 'clearing_forwarding', 'label' => 'Manage Job Costing & Profitability'],
            ['name' => 'view-cf-reports', 'module' => 'clearing_forwarding', 'label' => 'View C&F Operations & Demurrage Reports'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['module' => $perm['module'], 'label' => $perm['label'], 'guard_name' => 'web']
            );
        }

        // Grant all C&F permissions to 'company' role
        $companyRole = Role::where('name', 'company')->first();
        if ($companyRole) {
            $companyRole->givePermissionTo(collect($permissions)->pluck('name')->toArray());
        }

        // 2. Specialized C&F Roles
        $cfManagerRole = Role::firstOrCreate(['name' => 'Clearing & Forwarding Manager', 'guard_name' => 'web', 'module' => 'clearing_forwarding']);
        $cfManagerRole->syncPermissions(collect($permissions)->pluck('name')->toArray());

        $declarantRole = Role::firstOrCreate(['name' => 'Customs Declarant', 'guard_name' => 'web', 'module' => 'clearing_forwarding']);
        $declarantRole->syncPermissions([
            'manage-cf-dashboard',
            'manage-cf-jobs',
            'verify-cf-documents',
            'manage-cf-permits',
            'manage-cf-classifications',
            'manage-cf-declarations',
            'manage-cf-disbursements',
        ]);

        $transportRole = Role::firstOrCreate(['name' => 'Transport Coordinator', 'guard_name' => 'web', 'module' => 'clearing_forwarding']);
        $transportRole->syncPermissions([
            'manage-cf-dashboard',
            'manage-cf-jobs',
            'manage-cf-transport',
            'manage-cf-pod',
            'view-cf-reports',
        ]);

        // 3. Realistic Demo/Initial Data
        $client = User::where('type', 'client')->first() ?: User::where('email', 'like', '%client%')->first() ?: User::first();
        $staff = User::where('type', 'company')->first() ?: User::first();

        if ($client && $staff && CFJob::count() === 0) {
            // Job 1: Commercial Sea Import (Electronics from Shanghai to Dar es Salaam)
            $job1 = CFJob::create([
                'job_number' => 'CF-2026-00001',
                'customer_id' => $client->id,
                'consignee_name' => 'Mutamark Technologies Ltd',
                'consignor_name' => 'Shenzhen Electronics Export Co., Ltd',
                'service_type' => 'import',
                'transport_mode' => 'sea',
                'customs_regime' => 'home_use',
                'status' => 'cargo_released',
                'priority' => 'urgent',
                'case_owner_id' => $staff->id,
                'declarant_id' => $staff->id,
                'opened_at' => now()->subDays(12),
                'customer_reference' => 'PO-TECH-2026-889',
                'notes' => 'Priority sea import consignment. Containers subject to 14 free days from discharge.',
            ]);

            CFShipment::create([
                'cf_job_id' => $job1->id,
                'bl_awb_number' => 'MSKU928172601',
                'vessel_flight_truck' => 'MV MAERSK ATLANTIC',
                'voyage_number' => 'V-2601E',
                'shipping_line_airline' => 'Maersk Line',
                'origin_country' => 'China',
                'port_of_loading' => 'Shanghai Port',
                'port_of_discharge' => 'Dar es Salaam Port (TICTS)',
                'terminal_icd' => 'TICTS Terminal 2',
                'final_destination' => 'Dar es Salaam, Tanzania',
                'etd' => now()->subDays(30),
                'atd' => now()->subDays(29),
                'eta' => now()->subDays(5),
                'ata' => now()->subDays(4),
                'manifest_rotation_number' => 'ROT/DAR/2026/0412',
            ]);

            CFCargoLine::create([
                'cf_job_id' => $job1->id,
                'commodity_description' => 'Industrial Network Routers, High-Power Servers & Fiber Optic Adapters',
                'package_type' => 'cartons',
                'package_count' => 450,
                'gross_weight_kg' => 14500.000,
                'net_weight_kg' => 13800.000,
                'cbm' => 58.500,
                'marks_numbers' => 'MUTAMARK/DAR/1-450',
                'hs_code' => '8517.62.00',
                'customs_value' => 84500.00,
                'currency' => 'USD',
            ]);

            CFContainer::create([
                'cf_job_id' => $job1->id,
                'container_number' => 'MSKU7829104',
                'size_type' => '40HC',
                'seal_number' => 'ML-TZ-99201',
                'shipping_line' => 'Maersk Line',
                'free_days' => 14,
                'discharge_date' => now()->subDays(4),
                'gate_out_date' => now()->subDays(1),
                'demurrage_start_date' => now()->addDays(10),
                'empty_return_deadline' => now()->addDays(10),
                'status' => 'cleared_gate_out',
            ]);

            // Document checklist
            $docs = [
                ['type' => 'bill_of_lading', 'name' => 'Original Bill of Lading (3/3)', 'mandatory' => true, 'original' => true, 'status' => 'verified', 'ref' => 'MSKU928172601'],
                ['type' => 'commercial_invoice', 'name' => 'Commercial Invoice (Attested)', 'mandatory' => true, 'original' => true, 'status' => 'verified', 'ref' => 'INV-SZ-2026-09'],
                ['type' => 'packing_list', 'name' => 'Detailed Packing List', 'mandatory' => true, 'original' => true, 'status' => 'verified', 'ref' => 'PL-SZ-2026-09'],
                ['type' => 'certificate_of_origin', 'name' => 'CCPIT Certificate of Origin', 'mandatory' => true, 'original' => true, 'status' => 'verified', 'ref' => 'CO-CN-26-881'],
                ['type' => 'tbs_certificate', 'name' => 'TBS Standards PVoC / CoC', 'mandatory' => true, 'original' => false, 'status' => 'verified', 'ref' => 'TBS-PVOC-2026-11'],
            ];

            foreach ($docs as $d) {
                CFDocumentChecklist::create([
                    'cf_job_id' => $job1->id,
                    'document_type' => $d['type'],
                    'document_name' => $d['name'],
                    'is_mandatory' => $d['mandatory'],
                    'is_original_received' => $d['original'],
                    'document_reference' => $d['ref'],
                    'verification_status' => $d['status'],
                    'verified_by' => $staff->id,
                    'verified_at' => now()->subDays(6),
                ]);
            }

            // Permits
            CFPermit::create([
                'cf_job_id' => $job1->id,
                'agency' => 'TBS',
                'permit_type' => 'PVoC Conformity Certificate',
                'application_number' => 'APP-TBS-2026-441',
                'permit_number' => 'TBS/COC/2026/08912',
                'issue_date' => now()->subDays(10),
                'expiry_date' => now()->addMonths(6),
                'status' => 'approved',
                'special_conditions' => 'Standard compliance verified at origin inspection.',
            ]);

            // Classification & Valuation
            CFClassification::create([
                'cf_job_id' => $job1->id,
                'proposed_hs_code' => '8517.62.00',
                'approved_hs_code' => '8517.62.00',
                'tariff_description' => 'Machines for the reception, conversion and transmission or regeneration of voice, images or other data',
                'duty_rate_percentage' => 0.00, // zero duty ICT equipment
                'vat_rate_percentage' => 18.00,
                'excise_rate_percentage' => 0.00,
                'classification_rationale' => 'EAC Common External Tariff Chapter 85: Telecommunication & networking equipment.',
                'reviewed_by' => $staff->id,
                'status' => 'approved',
            ]);

            CFValuation::create([
                'cf_job_id' => $job1->id,
                'invoice_value_fob' => 84500.00,
                'freight_charges' => 3800.00,
                'insurance_charges' => 850.00,
                'customs_value_cif_foreign' => 89150.00,
                'currency' => 'USD',
                'tra_exchange_rate' => 2650.0000,
                'customs_value_tzs' => 236247500.00,
                'import_duty_tzs' => 0.00,
                'vat_tzs' => 42524550.00,
                'railway_levy_tzs' => 3543712.50,
                'customs_processing_fee_tzs' => 200000.00,
                'total_statutory_taxes_tzs' => 46268262.50,
                'reviewed_by' => $staff->id,
            ]);

            // Declaration
            $decl = CFDeclaration::create([
                'cf_job_id' => $job1->id,
                'customs_office' => 'Dar es Salaam Port (TICTS)',
                'declaration_type' => 'TANSAD',
                'declaration_number' => '26DAR-IM4-009182',
                'tancis_reference' => 'TANCIS-2026-08129',
                'regime_code' => 'IM4 (Home Use Direct)',
                'declarant_user_id' => $staff->id,
                'submission_date' => now()->subDays(3),
                'assessment_number' => 'ASS-2026-TRA-0491',
                'assessment_date' => now()->subDays(3),
                'assessed_amount_tzs' => 46268262.50,
                'payment_control_number' => '991200881920',
                'payment_date' => now()->subDays(2),
                'release_order_number' => 'REL-TRA-DAR-2026-778',
                'release_date' => now()->subDays(1),
                'status' => 'released',
                'customs_channel' => 'green',
            ]);

            CFCustomsEvent::create([
                'cf_declaration_id' => $decl->id,
                'cf_job_id' => $job1->id,
                'event_type' => 'release_granted',
                'officer_name' => 'Customs Inspector J. Mmari',
                'event_time' => now()->subDays(1),
                'location' => 'TICTS Gate 5',
                'findings' => 'All duties verified settled via GePG Control No. 991200881920. Electronic release order endorsed.',
                'outcome' => 'Clean Outturn & Gate-Out Authorized',
                'recorded_by' => $staff->id,
            ]);

            // Disbursements
            CFDisbursement::create([
                'cf_job_id' => $job1->id,
                'payee_name' => 'Tanzania Revenue Authority (TRA)',
                'charge_category' => 'statutory_duty',
                'description' => 'Customs VAT & Railway Development Levy Settlement',
                'payment_method' => 'control_number_gepg',
                'disbursed_amount' => 46268262.50,
                'currency' => 'TZS',
                'amount_tzs' => 46268262.50,
                'payment_reference' => '991200881920',
                'receipt_voucher_number' => 'TRA-REC-26-8812',
                'payment_date' => now()->subDays(2),
                'is_reconciled' => true,
                'authorized_by' => $staff->id,
            ]);

            CFDisbursement::create([
                'cf_job_id' => $job1->id,
                'payee_name' => 'Tanzania Ports Authority (TPA) / TICTS',
                'charge_category' => 'port_charges',
                'description' => 'Terminal Handling Charges & Wharfage Fee',
                'payment_method' => 'bank_transfer',
                'disbursed_amount' => 3120000.00,
                'currency' => 'TZS',
                'amount_tzs' => 3120000.00,
                'payment_reference' => 'TPA-INV-44120',
                'payment_date' => now()->subDays(2),
                'is_reconciled' => true,
                'authorized_by' => $staff->id,
            ]);

            CFDisbursement::create([
                'cf_job_id' => $job1->id,
                'payee_name' => 'Maersk Line Tanzania',
                'charge_category' => 'shipping_line',
                'description' => 'Delivery Order (DO) & Container Cleaning Fee',
                'payment_method' => 'bank_transfer',
                'disbursed_amount' => 1850000.00,
                'currency' => 'TZS',
                'amount_tzs' => 1850000.00,
                'payment_reference' => 'ML-TZ-DO-26-09',
                'payment_date' => now()->subDays(2),
                'is_reconciled' => true,
                'authorized_by' => $staff->id,
            ]);

            // Transport Order
            $to = CFTransportOrder::create([
                'cf_job_id' => $job1->id,
                'order_number' => 'TO-2026-00001',
                'transporter_type' => 'internal_fleet',
                'carrier_name' => 'Mutamark Heavy Haulage Fleet',
                'driver_name' => 'Rashid M. Bakari',
                'driver_phone' => '+255 754 112 890',
                'driver_license' => 'TZ-DL-098214',
                'vehicle_plate' => 'T 842 DKM / T 119 CWB',
                'pickup_location' => 'TICTS Terminal 2, Dar es Salaam Port',
                'delivery_destination' => 'Mutamark Main Warehouse, Chang\'ombe Industrial Area',
                'route_corridor' => 'Port - Kilwa Road - Chang\'ombe',
                'cargo_summary' => '1x40HC Container MSKU7829104 containing 450 cartons of network equipment',
                'agreed_freight_cost' => 1400000.00,
                'currency' => 'TZS',
                'dispatch_date' => now()->subHours(6),
                'expected_delivery_date' => now()->addHours(2),
                'status' => 'in_transit',
            ]);

            // Tracking Events
            CFTrackingEvent::create([
                'cf_job_id' => $job1->id,
                'cf_transport_order_id' => $to->id,
                'milestone_code' => 'cargo_landed',
                'location_name' => 'Dar es Salaam Port',
                'event_time' => now()->subDays(4),
                'remarks' => 'Vessel berthed. Container MSKU7829104 safely discharged to yard.',
                'is_customer_visible' => true,
                'recorded_by' => $staff->id,
            ]);

            CFTrackingEvent::create([
                'cf_job_id' => $job1->id,
                'cf_transport_order_id' => $to->id,
                'milestone_code' => 'declaration_passed',
                'location_name' => 'TRA Customs TANCIS Hub',
                'event_time' => now()->subDays(3),
                'remarks' => 'TANSAD assessed and released under Green Channel with zero query.',
                'is_customer_visible' => true,
                'recorded_by' => $staff->id,
            ]);

            CFTrackingEvent::create([
                'cf_job_id' => $job1->id,
                'cf_transport_order_id' => $to->id,
                'milestone_code' => 'gate_out_port',
                'location_name' => 'TICTS Gate 5',
                'event_time' => now()->subHours(6),
                'remarks' => 'Loaded onto flatbed T 842 DKM. Port exit clearance verified.',
                'is_customer_visible' => true,
                'recorded_by' => $staff->id,
            ]);

            // Costing & Reconciliation
            $costing = CFJobCosting::create([
                'cf_job_id' => $job1->id,
                'currency' => 'TZS',
                'agency_fee_revenue' => 4500000.00,
                'documentation_fee_revenue' => 850000.00,
                'transport_markup_revenue' => 450000.00,
                'total_service_revenue' => 5800000.00,
                'statutory_duties_disbursed' => 46268262.50,
                'port_shipping_disbursed' => 4970000.00,
                'transport_haulage_cost' => 1400000.00,
                'total_disbursements_and_direct_costs' => 52638262.50,
                'gross_profit' => 4400000.00,
                'net_margin_percentage' => 75.86,
                'customer_advance_received' => 55000000.00,
                'customer_invoiced_total' => 57038262.50,
                'balance_due_from_customer' => 2038262.50,
                'is_financially_reconciled' => false,
            ]);

            // Job 2: Regional Transit Cargo (Dar Port to Kigali, Rwanda)
            $job2 = CFJob::create([
                'job_number' => 'CF-2026-00002',
                'customer_id' => $client->id,
                'consignee_name' => 'Kigali Central Medical Depot, Rwanda',
                'consignor_name' => 'EuroPharma Export BV, Netherlands',
                'service_type' => 'transit',
                'transport_mode' => 'road',
                'customs_regime' => 'transit',
                'status' => 'in_transit',
                'priority' => 'critical',
                'case_owner_id' => $staff->id,
                'declarant_id' => $staff->id,
                'opened_at' => now()->subDays(8),
                'customer_reference' => 'RW-MOH-2026-TRANSIT-09',
                'notes' => 'Transit medical consignment via Central Corridor to Rusumo One-Stop Border Post.',
            ]);

            CFShipment::create([
                'cf_job_id' => $job2->id,
                'bl_awb_number' => 'CMA-TZ-881920',
                'vessel_flight_truck' => 'MV CMA CGM AFRICA',
                'voyage_number' => 'V-990S',
                'shipping_line_airline' => 'CMA CGM',
                'origin_country' => 'Netherlands',
                'port_of_loading' => 'Rotterdam',
                'port_of_discharge' => 'Dar es Salaam Port',
                'terminal_icd' => 'DICD Ubungo',
                'final_destination' => 'Kigali, Rwanda (via Rusumo)',
                'eta' => now()->subDays(6),
                'ata' => now()->subDays(5),
            ]);

            CFCargoLine::create([
                'cf_job_id' => $job2->id,
                'commodity_description' => 'Medical Consumables, Sterilized Surgical Kits & Diagnostic Reagents',
                'package_type' => 'pallets',
                'package_count' => 28,
                'gross_weight_kg' => 22400.000,
                'net_weight_kg' => 21000.000,
                'cbm' => 64.000,
                'marks_numbers' => 'KIGALI/MED/1-28',
                'hs_code' => '3004.90.00',
                'customs_value' => 142000.00,
                'currency' => 'USD',
            ]);

            CFContainer::create([
                'cf_job_id' => $job2->id,
                'container_number' => 'CMAU9918231',
                'size_type' => '40HC',
                'seal_number' => 'CMA-EAC-7712',
                'shipping_line' => 'CMA CGM',
                'free_days' => 21,
                'discharge_date' => now()->subDays(5),
                'gate_out_date' => now()->subDays(2),
                'demurrage_start_date' => now()->addDays(16),
                'empty_return_deadline' => now()->addDays(16),
                'status' => 'in_transit',
            ]);

            CFTransportOrder::create([
                'cf_job_id' => $job2->id,
                'order_number' => 'TO-2026-00002',
                'transporter_type' => 'internal_fleet',
                'carrier_name' => 'Mutamark Cross-Border Fleet',
                'driver_name' => 'Emmanuel K. Ndunguru',
                'driver_phone' => '+255 784 991 204',
                'driver_license' => 'TZ-DL-771209',
                'vehicle_plate' => 'T 912 DPA / T 801 CXV',
                'pickup_location' => 'Dar Port DICD Ubungo',
                'delivery_destination' => 'Kigali Central Stores, Rwanda',
                'route_corridor' => 'Central Corridor: Dar - Morogoro - Dodoma - Singida - Isaka - Rusumo - Kigali',
                'cargo_summary' => '1x40HC Container CMAU9918231 containing 28 pallets medical supplies',
                'agreed_freight_cost' => 8500000.00,
                'currency' => 'TZS',
                'dispatch_date' => now()->subDays(2),
                'expected_delivery_date' => now()->addDays(2),
                'status' => 'in_transit',
            ]);

            CFTrackingEvent::create([
                'cf_job_id' => $job2->id,
                'milestone_code' => 'checkpoint_cleared',
                'location_name' => 'Isaka Dry Port Weighbridge',
                'event_time' => now()->subHours(8),
                'remarks' => 'Transit bond active (T1). Axle weight compliance checked and verified.',
                'is_customer_visible' => true,
                'recorded_by' => $staff->id,
            ]);
        }
    }
}

