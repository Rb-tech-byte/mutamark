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
        if (!Schema::hasTable('insurances')) {
            Schema::create('insurances', function (Blueprint $table) {
                $table->id();
                $table->string('insurance_provider');
                $table->integer('vehicle_name');
                $table->date('start_date');
                $table->date('end_date');
                $table->date('scheduled_date');
                $table->integer('scheduled_period');
                $table->integer('deductible');
                $table->integer('charge_payable');
                $table->integer('policy_number');
                $table->string('policy_document');
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
        Schema::dropIfExists('insurances');
    }
};
