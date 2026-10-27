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
        if (!Schema::hasTable('bookings_business_hours')) {

            Schema::create('bookings_business_hours', function (Blueprint $table) {
                $table->id();
                $table->string('day_name');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('day_off')->default('off');
                $table->string('break_hours')->nullable();
                $table->integer('workspace_id')->default(0);
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
        Schema::dropIfExists('bookings_business_hours');
    }
};
