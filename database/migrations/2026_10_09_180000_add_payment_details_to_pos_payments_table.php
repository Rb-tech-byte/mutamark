<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_payments') && !Schema::hasColumn('pos_payments', 'payment_method')) {
            Schema::table('pos_payments', function (Blueprint $table) {
                $table->string('payment_method')->nullable()->default('cash')->after('discount_amount');
                $table->string('reference_number')->nullable()->after('payment_method');
                $table->double('paid_amount')->nullable()->after('reference_number');
                $table->double('change_amount')->nullable()->after('paid_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_payments') && Schema::hasColumn('pos_payments', 'payment_method')) {
            Schema::table('pos_payments', function (Blueprint $table) {
                $table->dropColumn(['payment_method', 'reference_number', 'paid_amount', 'change_amount']);
            });
        }
    }
};
