<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $duplicates = DB::table('warehouse_stocks')->select('product_id', 'warehouse_id')
            ->groupBy('product_id', 'warehouse_id')->havingRaw('COUNT(*) > 1')->exists();
        if ($duplicates) throw new RuntimeException('Duplicate warehouse stock rows require reviewed reconciliation before adding uniqueness. No rows have been changed.');
        Schema::table('warehouse_stocks', fn (Blueprint $table) => $table->unique(['product_id', 'warehouse_id'], 'warehouse_stocks_product_warehouse_unique'));
    }
    public function down(): void
    {
        Schema::table('warehouse_stocks', fn (Blueprint $table) => $table->dropUnique('warehouse_stocks_product_warehouse_unique'));
    }
};
