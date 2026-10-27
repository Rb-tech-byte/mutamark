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
        if (!Schema::hasTable('job_candidates')) {
            Schema::create('job_candidates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->date('dob')->nullable();
                $table->string('gender')->nullable();
                $table->text('address')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('resume')->nullable();
                $table->string('profile')->nullable();
                $table->longText('description')->nullable();
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
        Schema::dropIfExists('job_candidates');
    }
};
