<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rfq_items') && !Schema::hasColumn('rfq_items', 'quantity_required')) {
            Schema::table('rfq_items', function (Blueprint $table) {
                $table->decimal('quantity_required', 12, 2)->default(1)->after('quantity');
            });

            if (Schema::hasColumn('rfq_items', 'quantity')) {
                DB::statement('UPDATE `rfq_items` SET `quantity_required` = `quantity`');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rfq_items') && Schema::hasColumn('rfq_items', 'quantity_required')) {
            Schema::table('rfq_items', function (Blueprint $table) {
                $table->dropColumn('quantity_required');
            });
        }
    }
};

