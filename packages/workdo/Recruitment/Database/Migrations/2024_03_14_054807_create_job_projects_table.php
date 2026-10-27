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
        if (!Schema::hasTable('job_projects')) {
            Schema::create('job_projects', function (Blueprint $table) {
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
                $table->string('reference')->nullable();
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
        Schema::dropIfExists('job_projects');
    }
};
