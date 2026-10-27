<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Warehouse;
use Workdo\Pos\Models\Pos;
use Workdo\Pos\Models\PosItem;
use Workdo\Pos\Models\PosPayment;
use Workdo\Pos\Models\PosBillingCounter;
use Workdo\Pos\Models\PosDiscount;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\ProductServiceTax;

class MutamarkPosSeeder extends Seeder
{
    public function run(): void
    {
        $companyUser = User::where('email', 'company@example.com')->first() ?? User::find(2);
        if (!$companyUser) {
            $this->command->error("Company user not found.");
            return;
        }

        $creatorId = $companyUser->id;
        $warehouse = Warehouse::where('created_by', $creatorId)->first();
        if (!$warehouse) {
            $warehouse = Warehouse::first();
        }

        $bankAccount = DB::table('bank_accounts')->where('created_by', $creatorId)->first();
        $bankAccountId = $bankAccount ? $bankAccount->id : 1;

        // 1. Billing Counters
        $countersData = [
            [
                'name' => 'Counter 01 - Dar Showroom Retail Desk',
                'code' => 'CTR-01',
                'bank_account_id' => $bankAccountId,
                'status' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ],
            [
                'name' => 'Counter 02 - Express POS Checkout',
                'code' => 'CTR-02',
                'bank_account_id' => $bankAccountId,
                'status' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ],
        ];

        foreach ($countersData as $c) {
            PosBillingCounter::updateOrCreate(
                ['code' => $c['code'], 'created_by' => $creatorId],
                $c
            );
        }

        $counter1 = PosBillingCounter::where('code', 'CTR-01')->where('created_by', $creatorId)->first();
        $counter2 = PosBillingCounter::where('code', 'CTR-02')->where('created_by', $creatorId)->first();

        // 2. Active Discounts
        PosDiscount::updateOrCreate(
            ['name' => '10% Retail Hardware Promo', 'created_by' => $creatorId],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_quantity' => 1,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
                'is_active' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]
        );

        // 3. Customers
        $client = User::whereHas('roles', function($q) {
            $q->where('name', 'client');
        })->where('created_by', $creatorId)->first();
        $clientId = $client ? $client->id : null;

        // 4. Products for POS
        $scanner = ProductServiceItem::where('name', 'like', '%Scanner%')->first();
        $printer = ProductServiceItem::where('name', 'like', '%Printer%')->first();

        if (!$scanner || !$printer) {
            $products = ProductServiceItem::where('type', '!=', 'service')->take(2)->get();
            $scanner = $products[0] ?? null;
            $printer = $products[1] ?? null;
        }

        if (!$scanner || !$printer) {
            $this->command->warn("Not enough products to seed POS sales.");
            return;
        }

        $vat18 = ProductServiceTax::where('rate', 18)->where('created_by', $creatorId)->first();
        $taxIds = $vat18 ? [$vat18->id] : null;

        // Clear previous mock POS if running repeatedly
        Pos::where('created_by', $creatorId)->where('sale_number', 'like', 'POS-%')->delete();

        $salesScenarios = [
            [
                'sale_number' => 'POS-00001',
                'counter' => $counter1,
                'date' => now()->subDays(3)->toDateString(),
                'payment_method' => 'cash',
                'ref' => null,
                'tender' => 700000.00,
                'items' => [
                    ['product' => $scanner, 'qty' => 1, 'price' => 350000.00],
                    ['product' => $printer, 'qty' => 1, 'price' => 280000.00],
                ],
            ],
            [
                'sale_number' => 'POS-00002',
                'counter' => $counter1,
                'date' => now()->subDays(2)->toDateString(),
                'payment_method' => 'digital_gateway',
                'ref' => 'PESAPAL-TZ-9821482',
                'tender' => 280000.00,
                'items' => [
                    ['product' => $printer, 'qty' => 1, 'price' => 280000.00],
                ],
            ],
            [
                'sale_number' => 'POS-00003',
                'counter' => $counter2,
                'date' => now()->subDays(1)->toDateString(),
                'payment_method' => 'bank_transfer',
                'ref' => 'CRDB-FT-20261009-8812',
                'tender' => 700000.00,
                'items' => [
                    ['product' => $scanner, 'qty' => 2, 'price' => 350000.00],
                ],
            ],
            [
                'sale_number' => 'POS-00004',
                'counter' => $counter2,
                'date' => now()->toDateString(),
                'payment_method' => 'digital_gateway',
                'ref' => 'SELCOM-TXN-773419',
                'tender' => 1062000.00,
                'items' => [
                    ['product' => $scanner, 'qty' => 2, 'price' => 350000.00],
                    ['product' => $printer, 'qty' => 1, 'price' => 280000.00],
                ],
            ],
            [
                'sale_number' => 'POS-00005',
                'counter' => $counter1,
                'date' => now()->toDateString(),
                'payment_method' => 'cash',
                'ref' => null,
                'tender' => 400000.00,
                'items' => [
                    ['product' => $scanner, 'qty' => 1, 'price' => 350000.00],
                ],
            ],
        ];

        foreach ($salesScenarios as $sc) {
            $pos = new Pos();
            $pos->sale_number = $sc['sale_number'];
            $pos->customer_id = $clientId;
            $pos->warehouse_id = $warehouse->id;
            $pos->billing_counter_id = $sc['counter']->id;
            $pos->bank_account_id = $sc['counter']->bank_account_id;
            $pos->pos_date = $sc['date'];
            $pos->status = 'completed';
            $pos->creator_id = $creatorId;
            $pos->created_by = $creatorId;
            $pos->created_at = $sc['date'] . ' 10:30:00';
            $pos->save();

            $subtotalSum = 0;
            $taxSum = 0;
            $totalSum = 0;

            foreach ($sc['items'] as $it) {
                $p = $it['product'];
                $qty = $it['qty'];
                $price = $it['price'];
                $subtotal = $qty * $price;
                $taxAmount = $subtotal * 0.18; // TRA 18% VAT
                $total = $subtotal + $taxAmount;

                $subtotalSum += $subtotal;
                $taxSum += $taxAmount;
                $totalSum += $total;

                $posItem = new PosItem();
                $posItem->pos_id = $pos->id;
                $posItem->product_id = $p->id;
                $posItem->quantity = $qty;
                $posItem->price = $price;
                $posItem->tax_ids = $taxIds;
                $posItem->subtotal = $subtotal;
                $posItem->tax_amount = $taxAmount;
                $posItem->total_amount = $total;
                $posItem->item_discount_value = 0;
                $posItem->item_discount_amount = 0;
                $posItem->creator_id = $creatorId;
                $posItem->created_by = $creatorId;
                $posItem->save();
            }

            $tender = max($sc['tender'], $totalSum);
            $change = max(0, $tender - $totalSum);

            $posPayment = new PosPayment();
            $posPayment->pos_id = $pos->id;
            $posPayment->discount = 0;
            $posPayment->amount = $totalSum;
            $posPayment->discount_amount = $totalSum;
            $posPayment->payment_method = $sc['payment_method'];
            $posPayment->reference_number = $sc['ref'];
            $posPayment->paid_amount = $tender;
            $posPayment->change_amount = $change;
            $posPayment->creator_id = $creatorId;
            $posPayment->created_by = $creatorId;
            $posPayment->save();
        }

        $this->command->info("Mutamark POS data seeded successfully with dynamic payment methods (Cash, Gateway, Bank Transfer) in TZS!");
    }
}
