<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $quantities = [
            'warehouse_stocks' => ['quantity'],
            'sales_order_items' => ['quantity', 'fulfilled_quantity'],
            'purchase_order_items' => ['quantity_received'],
            'delivery_note_items' => ['quantity_ordered', 'quantity_shipped', 'quantity_received'],
            'grn_items' => ['ordered_quantity', 'received_quantity', 'accepted_quantity', 'rejected_quantity'],
            'stock_adjustment_items' => ['system_quantity', 'counted_quantity', 'difference_quantity'],
            'transfer_items' => ['requested_quantity', 'dispatched_quantity', 'received_quantity', 'discrepancy_quantity'],
        ];
        foreach ($quantities as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) Schema::table($table, fn (Blueprint $blueprint) => $blueprint->decimal($column, 18, 4)->default(0)->change());
            }
        }
    }

    public function down(): void
    {
        // Retain precision: shrinking columns could truncate business quantities.
    }
};
