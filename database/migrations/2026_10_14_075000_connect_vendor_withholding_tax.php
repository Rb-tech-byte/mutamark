<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('vendor_payments', function (Blueprint $table) { $table->decimal('wht_base_amount',18,2)->default(0); $table->decimal('wht_rate',5,2)->default(0); $table->decimal('wht_amount',18,2)->default(0); });
        Schema::table('withholding_taxes', fn (Blueprint $table) => $table->foreignId('gl_account_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete());
    }
    public function down(): void { /* Preserve statutory payment history. */ }
};
