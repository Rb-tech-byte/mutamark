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
        if (!Schema::hasTable('insurance_booking')) {
            Schema::create('insurance_booking', function (Blueprint $table) {
                $table->id();
                $table->integer('insurance_id');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('amount');
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
        Schema::dropIfExists('insurance_booking');
    }
};
