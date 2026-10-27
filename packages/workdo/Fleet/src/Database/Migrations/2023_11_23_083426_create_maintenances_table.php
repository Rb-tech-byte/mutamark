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
        if (!Schema::hasTable('maintenances')) {
            Schema::create('maintenances', function (Blueprint $table) {
                $table->id();
                $table->string('service_type');
                $table->integer('service_for');
                $table->integer('vehicle_name');
                $table->integer('maintenance_type');
                $table->string('service_name');
                $table->string('charge');
                $table->string('charge_bear_by');
                $table->date('maintenance_date');
                $table->string('priority');
                $table->integer('total_cost');
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
        Schema::dropIfExists('maintenances');
    }
};
