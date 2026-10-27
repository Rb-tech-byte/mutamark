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
        if (!Schema::hasTable('bookings_appointments')) {

            Schema::create('bookings_appointments', function (Blueprint $table) {
                $table->id();
                $table->integer('appointment_id');
                $table->date('date');
                $table->integer('service');
                $table->string('package');
                $table->string('staff');
                $table->string('client_id');
                $table->string('start_time');
                $table->string('end_time');
                $table->string('your_country');
                $table->string('your_state');
                $table->string('your_city');
                $table->string('your_address');
                $table->integer('your_zip_code');
                $table->string('our_country');
                $table->string('our_state');
                $table->string('our_city');
                $table->integer('our_zip_code');
                $table->string('payment')->nullable();
                $table->integer('stage_id')->nullable();
                $table->integer('workspace')->nullable();
                $table->integer('created_by')->default(0);
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
        Schema::dropIfExists('bookings_appointments');
    }
};
