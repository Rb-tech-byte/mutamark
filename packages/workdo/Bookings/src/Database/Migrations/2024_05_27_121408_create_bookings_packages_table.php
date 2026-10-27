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
        if (!Schema::hasTable('bookings_packages')) {

            Schema::create('bookings_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('item');
                $table->string('services');
                $table->string('delivery_time');
                $table->string('delivery_period');
                $table->string('price')->nullable();
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
        Schema::dropIfExists('bookings_packages');
    }
};
