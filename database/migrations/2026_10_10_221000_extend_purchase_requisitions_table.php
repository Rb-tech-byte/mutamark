<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requisitions', 'preferred_vendor_id')) {
                $table->unsignedBigInteger('preferred_vendor_id')->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('purchase_requisitions', 'converted_invoice_id')) {
                $table->unsignedBigInteger('converted_invoice_id')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('purchase_requisitions', 'verification_hash')) {
                $table->string('verification_hash', 64)->nullable()->after('converted_invoice_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requisitions', 'verification_hash')) {
                $table->dropColumn('verification_hash');
            }
            if (Schema::hasColumn('purchase_requisitions', 'converted_invoice_id')) {
                $table->dropColumn('converted_invoice_id');
            }
            if (Schema::hasColumn('purchase_requisitions', 'preferred_vendor_id')) {
                $table->dropColumn('preferred_vendor_id');
            }
        });
    }
};
