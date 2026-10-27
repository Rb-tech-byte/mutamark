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
        if (!Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->string('unique_id', 100)->unique();
                $table->integer('user_id')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->date('date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('appointment_id')->nullable();
                $table->longText('questions')->nullable();
                $table->string('meeting_type')->nullable();
                $table->longText('start_url')->nullable();
                $table->string('join_url')->nullable();
                $table->longText('cancel_description')->nullable();
                $table->string('status')->default('Pending');
                $table->string('send_feedback')->default('no');
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
        Schema::dropIfExists('schedules');
    }
};
