<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('purchase_orders')) Schema::create('purchase_orders',function(Blueprint $table){
            $table->id(); $table->string('po_number')->unique(); $table->date('po_date'); $table->date('expected_delivery_date')->nullable();
            $table->unsignedBigInteger('vendor_id'); $table->unsignedBigInteger('warehouse_id'); $table->unsignedBigInteger('requisition_id')->nullable(); $table->unsignedBigInteger('rfq_id')->nullable();
            $table->decimal('subtotal',18,2)->default(0); $table->decimal('tax_amount',18,2)->default(0); $table->decimal('discount_amount',18,2)->default(0); $table->decimal('total_amount',18,2)->default(0);
            $table->string('currency',10)->default('TZS'); $table->string('status')->default('draft'); $table->unsignedBigInteger('approved_by')->nullable(); $table->timestamp('approved_at')->nullable();
            $table->text('delivery_terms')->nullable(); $table->text('payment_terms')->nullable(); $table->text('notes')->nullable(); $table->unsignedBigInteger('creator_id'); $table->unsignedBigInteger('created_by'); $table->timestamps();
        });
        if (!Schema::hasTable('purchase_order_items')) Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('product_service_items')->restrictOnDelete(); $table->string('product_name');
            $table->decimal('quantity_ordered',18,4); $table->decimal('quantity_received',18,4)->default(0); $table->decimal('unit_price',18,2); $table->decimal('tax_rate',5,2)->default(0);
            $table->decimal('tax_amount',18,2)->default(0); $table->decimal('total_amount',18,2); $table->timestamps();
        });
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->timestamp('supplier_accepted_at')->nullable());
        Schema::table('purchase_invoices', fn (Blueprint $table) => $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->restrictOnDelete());
        Schema::table('goods_received_notes', fn (Blueprint $table) => $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->restrictOnDelete());
        Schema::table('purchase_requisitions', fn (Blueprint $table) => $table->foreignId('converted_po_id')->nullable()->constrained('purchase_orders')->restrictOnDelete());
    }
    public function down(): void { /* Preserve procurement links and history. */ }
};
