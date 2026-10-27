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
        if(!Schema::hasTable('contracts'))
        {
            Schema::create('contracts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('subject')->nullable();
                $table->integer('user_id');
                $table->integer('project_id')->nullable();
                $table->string('value')->nullable();
                $table->integer('type');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('notes')->nullable();
                $table->longText('client_signature')->nullable();
                $table->longText('owner_signature')->nullable();
                $table->string('status')->default('pending');
                $table->longtext('description')->nullable();
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
        Schema::dropIfExists('contracts');
    }
};
