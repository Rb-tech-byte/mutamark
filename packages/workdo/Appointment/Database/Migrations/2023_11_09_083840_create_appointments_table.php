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
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('question')->nullable();
                $table->string('appointment_type')->nullable();
                $table->string('week_day')->nullable();
                // $table->date('date')->nullable();
                // $table->time('start_time')->nullable();
                // $table->time('end_time')->nullable();
                $table->string('is_enabled')->nullable();
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
        Schema::dropIfExists('appointments');
    }
};
