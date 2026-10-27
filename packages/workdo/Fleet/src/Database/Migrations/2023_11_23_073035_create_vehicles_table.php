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
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('department_id');
                $table->integer('vehicle_type');
                $table->integer('fuel_type');
                $table->date('registration_date');
                $table->date('register_ex_date')->nullable();
                $table->integer('lincense_plate');
                $table->integer('vehical_id_num');
                $table->year('model_year');
                $table->integer('driver_name');
                $table->integer('seat_capacity');
                $table->string('status')->nullable();
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
        Schema::dropIfExists('vehicles');
    }
};
