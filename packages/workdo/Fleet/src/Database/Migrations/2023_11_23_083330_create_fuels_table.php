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
        if (!Schema::hasTable('fuels')) {
            Schema::create('fuels', function (Blueprint $table) {
                $table->id();
                $table->integer('driver_name');
                $table->integer('vehicle_name');
                $table->timestamp('fill_date');
                $table->integer('quantity');
                $table->integer('cost');
                $table->integer('total_cost');
                $table->string('odometer_reading');
                $table->string('fuel_type');
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
        Schema::dropIfExists('fuels');
    }
};
