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
        if (!Schema::hasTable('fleet_customers')) {
            Schema::create('fleet_customers', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->longText('address');
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
        Schema::dropIfExists('fleet_customers');
    }
};
