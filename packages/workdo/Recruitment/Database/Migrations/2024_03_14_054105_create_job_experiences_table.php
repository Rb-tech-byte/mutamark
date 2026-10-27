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
        if (!Schema::hasTable('job_experiences')) {
            Schema::create('job_experiences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('candidate_id');
                $table->foreign('candidate_id')->references('id')->on('job_candidates')->onUpdate('cascade')->onDelete('cascade');
                $table->string('title')->nullable();
                $table->string('organization')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('country')->nullable();
                $table->string('state')->nullable();
                $table->string('city')->nullable();
                $table->integer('zipcode')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('website')->nullable();
                $table->string('experience_proof')->nullable();
                $table->integer('is_reference_slider')->default(0);
                $table->string('full_name')->nullable();
                $table->string('reference_email')->nullable();
                $table->string('reference_phone')->nullable();
                $table->string('job_position')->nullable();
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
        Schema::dropIfExists('job_experiences');
    }
};
