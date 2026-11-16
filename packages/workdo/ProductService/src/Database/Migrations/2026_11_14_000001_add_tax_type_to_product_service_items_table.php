<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_service_items') && !Schema::hasColumn('product_service_items', 'tax_type')) {
            Schema::table('product_service_items', function (Blueprint $table) {
                $table->string('tax_type', 20)->default('exclusive')->after('tax_ids');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_service_items') && Schema::hasColumn('product_service_items', 'tax_type')) {
            Schema::table('product_service_items', function (Blueprint $table) {
                $table->dropColumn('tax_type');
            });
        }
    }
};

