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
        if (!Schema::hasTable('fleet_logbooks')) {
            Schema::create('fleet_logbooks', function (Blueprint $table) {
                $table->id();
                $table->string('driver_name');
                $table->string('vehicle_name');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('start_odometer');
                $table->string('end_odometer');
                $table->string('rate');
                $table->string('total_distance');
                $table->string('total_price');
                $table->longText('notes')->nullable();
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
        Schema::dropIfExists('fleet_logbooks');
    }
};
