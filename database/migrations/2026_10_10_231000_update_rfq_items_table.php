<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            if (!Schema::hasColumn('rfq_items', 'target_unit_price')) {
                $table->decimal('target_unit_price', 15, 2)->default(0)->after('unit');
            }
            if (!Schema::hasColumn('rfq_items', 'quantity')) {
                $table->decimal('quantity', 12, 2)->default(1)->after('sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            if (Schema::hasColumn('rfq_items', 'target_unit_price')) {
                $table->dropColumn('target_unit_price');
            }
            if (Schema::hasColumn('rfq_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
