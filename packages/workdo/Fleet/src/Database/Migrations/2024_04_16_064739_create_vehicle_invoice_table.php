<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('vehicle_invoice')) {
            Schema::create('vehicle_invoice', function (Blueprint $table) {
                $table->id();
                $table->integer('invoice_id')->nullable();
                $table->string('product_type')->nullable();
                $table->integer('item')->nullable();
                $table->string('start_location')->nullable();
                $table->string('end_location')->nullable();
                $table->string('trip_type')->nullable();
                $table->integer('rate')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('description')->nullable();
                $table->string('distance')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_invoice');
    }
};
