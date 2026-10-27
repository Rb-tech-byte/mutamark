<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        $relations=[['purchase_orders','vendor_id','vendors'],['purchase_orders','warehouse_id','warehouses'],['purchase_order_items','purchase_order_id','purchase_orders'],['purchase_order_items','product_id','product_service_items']];
        foreach($relations as [$table,$column,$parent]) {
            if(DB::table($table.' as c')->leftJoin($parent.' as p','p.id','=','c.'.$column)->whereNotNull('c.'.$column)->whereNull('p.id')->exists()) throw new RuntimeException('Review orphan purchase-order relationships before applying constraints.');
        }
        Schema::table('purchase_order_items',fn(Blueprint $table)=>$table->decimal('quantity_ordered',18,4)->change());
        foreach($relations as [$table,$column,$parent]) {
            if(!collect(Schema::getForeignKeys($table))->contains(fn($key)=>$key['columns']===[$column])) Schema::table($table,fn(Blueprint $blueprint)=>$blueprint->foreign($column,'mutamark_po_'.substr(hash('sha256',$table.$column),0,16))->references('id')->on($parent)->restrictOnDelete());
        }
    }
    public function down(): void { /* Preserve commercial integrity. */ }
};
