<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'customer_category')) {
                    $table->string('customer_category', 60)->default('corporate')->after('company_name');
                }
                if (!Schema::hasColumn('customers', 'brela_number')) {
                    $table->string('brela_number', 80)->nullable()->after('tax_number');
                }
                if (!Schema::hasColumn('customers', 'vat_number')) {
                    $table->string('vat_number', 80)->nullable()->after('brela_number');
                }
                if (!Schema::hasColumn('customers', 'status')) {
                    $table->string('status', 30)->default('active')->after('payment_terms');
                }
                if (!Schema::hasColumn('customers', 'supporting_documents')) {
                    $table->json('supporting_documents')->nullable()->after('notes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                foreach (['customer_category', 'brela_number', 'vat_number', 'status', 'supporting_documents'] as $col) {
                    if (Schema::hasColumn('customers', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

