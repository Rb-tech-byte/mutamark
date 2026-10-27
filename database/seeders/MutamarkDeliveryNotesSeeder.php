<?php

namespace Database\Seeders;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\DocumentVerification;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\ProductService\Models\ProductServiceItem;

class MutamarkDeliveryNotesSeeder extends Seeder
{
    /**
     * Run the database seeds for Delivery Notes.
     */
    public function run(): void
    {
        $creator = User::where('email', 'company@example.com')->first();
        if (!$creator) {
            $creator = User::first();
        }
        $creatorId = $creator->id;

        $warehouse = Warehouse::where('created_by', $creatorId)->first();
        $customer = User::where('created_by', $creatorId)
            ->where('id', '!=', $creatorId)
            ->first();
        if (!$customer) {
            $customer = $creator;
        }

        $invoice = SalesInvoice::where('created_by', $creatorId)->first();
        $products = ProductServiceItem::where('created_by', $creatorId)->take(3)->get();
        $vehicle = Vehicle::where('created_by', $creatorId)->first();
        $driver = Driver::where('created_by', $creatorId)->first();

        // 1. DN-2026-00001: Delivered (POD Verified)
        if (!DeliveryNote::where('delivery_number', 'DN-2026-00001')->exists()) {
            $hash1 = hash('sha256', "DN-1-DN-2026-00001-{$creatorId}");
            $dn1 = DeliveryNote::create([
                'delivery_number' => 'DN-2026-00001',
                'delivery_date' => now()->subDays(3)->toDateString(),
                'estimated_delivery_date' => now()->subDays(2)->toDateString(),
                'actual_delivery_date' => now()->subDays(2)->toDateString(),
                'invoice_id' => $invoice?->id,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse?->id ?? 1,
                'vehicle_id' => $vehicle?->id,
                'driver_id' => $driver?->id,
                'driver_name' => $driver?->name ?? 'Juma Hamisi Mussa',
                'driver_phone' => $driver?->phone ?? '+255 754 123 456',
                'vehicle_plate' => $vehicle?->plate_number ?? 'T 412 DKM (Isuzu Forward 5T)',
                'shipping_address' => 'Plot 88, Uhuru Street, Kariakoo Commercial Zone, Dar es Salaam',
                'destination_city' => 'Dar es Salaam',
                'contact_person' => 'Josephat Mwita',
                'contact_phone' => '+255 765 998 811',
                'status' => 'delivered',
                'tracking_number' => 'TRK-DSM-2026-8812',
                'total_packages' => 8,
                'total_weight_kg' => 450.00,
                'notes' => 'Priority morning delivery. Store goods in temperature-controlled ground warehouse.',
                'proof_of_delivery_recipient' => 'Josephat Mwita (Store Manager)',
                'proof_of_delivery_signature' => 'POD-SIG-8921-VERIFIED-JM',
                'proof_of_delivery_notes' => 'Packages inspected, security seals intact, zero damage detected.',
                'delivery_verified_at' => now()->subDays(2),
                'verification_hash' => $hash1,
                'stock_deducted' => true,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            if ($products->isNotEmpty()) {
                foreach ($products as $p) {
                    DeliveryNoteItem::create([
                        'delivery_note_id' => $dn1->id,
                        'product_id' => $p->id,
                        'product_name' => $p->name,
                        'sku' => $p->sku,
                        'quantity_ordered' => 20.00,
                        'quantity_shipped' => 20.00,
                        'quantity_received' => 20.00,
                        'unit' => $p->unit ?? 'pcs',
                        'batch_number' => 'BCH-2026-A1',
                    ]);
                }
            } else {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $dn1->id,
                    'product_name' => 'Industrial Protective Safety Gear',
                    'sku' => 'SAF-GR-01',
                    'quantity_ordered' => 25.00,
                    'quantity_shipped' => 25.00,
                    'quantity_received' => 25.00,
                    'unit' => 'sets',
                    'batch_number' => 'BCH-2026-A1',
                ]);
            }
        }

        // 2. DN-2026-00002: In Transit
        if (!DeliveryNote::where('delivery_number', 'DN-2026-00002')->exists()) {
            $hash2 = hash('sha256', "DN-2-DN-2026-00002-{$creatorId}");
            $dn2 = DeliveryNote::create([
                'delivery_number' => 'DN-2026-00002',
                'delivery_date' => now()->toDateString(),
                'estimated_delivery_date' => now()->addDays(1)->toDateString(),
                'invoice_id' => null,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse?->id ?? 1,
                'vehicle_id' => $vehicle?->id,
                'driver_id' => $driver?->id,
                'driver_name' => 'Baraka Said Khalfan',
                'driver_phone' => '+255 713 555 789',
                'vehicle_plate' => 'T 820 EFA (Scania R480 Long Haul)',
                'shipping_address' => 'Sokoine Road, Arusha Central Distribution Depot, Arusha',
                'destination_city' => 'Arusha',
                'contact_person' => 'Fatma Rajabu',
                'contact_phone' => '+255 784 332 100',
                'status' => 'in_transit',
                'tracking_number' => 'TRK-ARU-2026-4431',
                'total_packages' => 15,
                'total_weight_kg' => 1200.00,
                'notes' => 'Northern corridor dispatch. Driver must report via GPS tracking checkpoints.',
                'delivery_verified_at' => now(),
                'verification_hash' => $hash2,
                'stock_deducted' => true,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            DeliveryNoteItem::create([
                'delivery_note_id' => $dn2->id,
                'product_name' => 'High-Performance Lubricant Drums 200L',
                'sku' => 'LUB-DRUM-200',
                'quantity_ordered' => 10.00,
                'quantity_shipped' => 10.00,
                'quantity_received' => 0.00,
                'unit' => 'drums',
                'batch_number' => 'BCH-2026-LUB9',
            ]);
        }

        // 3. DN-2026-00003: Scheduled / Draft
        if (!DeliveryNote::where('delivery_number', 'DN-2026-00003')->exists()) {
            DeliveryNote::create([
                'delivery_number' => 'DN-2026-00003',
                'delivery_date' => now()->addDays(2)->toDateString(),
                'estimated_delivery_date' => now()->addDays(3)->toDateString(),
                'invoice_id' => null,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse?->id ?? 1,
                'shipping_address' => 'Government City Complex, Mtumba, Dodoma',
                'destination_city' => 'Dodoma',
                'contact_person' => 'Eng. Denis Makubi',
                'contact_phone' => '+255 754 888 221',
                'status' => 'scheduled',
                'tracking_number' => 'TRK-DOM-2026-1092',
                'total_packages' => 4,
                'total_weight_kg' => 180.00,
                'notes' => 'Government tender order. Gate pass required upon entry.',
                'stock_deducted' => false,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }
    }
}
