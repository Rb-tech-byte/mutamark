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
        if (!Schema::hasTable('appointment_callbacks')) {
            Schema::create('appointment_callbacks', function (Blueprint $table) {
                $table->id();
                $table->integer('schedule_id')->nullable();
                $table->integer('unique_id')->nullable();
                $table->integer('user_id')->nullable();
                $table->integer('appointment_id')->nullable();
                $table->longText('reason')->nullable();
                $table->date('date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('start_url')->nullable();
                $table->string('join_url')->nullable();
                $table->string('status')->default('Pending');
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
        Schema::dropIfExists('appointment_callbacks');
    }
};
