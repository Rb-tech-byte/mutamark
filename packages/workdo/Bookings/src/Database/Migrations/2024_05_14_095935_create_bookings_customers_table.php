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
        if (!Schema::hasTable('bookings_customers')) {

            Schema::create('bookings_customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('number');
                $table->string('email');
                $table->string('client_id')->nullable();
                $table->string('customer')->nullable();
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
        Schema::dropIfExists('bookings_customers');
    }
};
