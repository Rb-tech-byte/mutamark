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
        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->integer('customer_name');
                $table->integer('vehicle_name');
                $table->integer('driver_name');
                $table->string('trip_type');
                $table->timestamp('start_date');
                $table->timestamp('end_date');
                $table->string('start_address');
                $table->string('end_address');
                $table->integer('total_price')->default(0);
                $table->string('status');
                $table->string('notes');
                $table->integer('workspace')->nullable();
                $table->integer('created_by');
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
        Schema::dropIfExists('bookings');
    }
};
