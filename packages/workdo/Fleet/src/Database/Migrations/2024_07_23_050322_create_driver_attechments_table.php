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
        if(!Schema::hasTable('driver_attechments'))
        {
            Schema::create('driver_attechments', function (Blueprint $table) {
                $table->id();
                $table->string('driver_id');
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_size');
                $table->string('file_status');
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
        Schema::dropIfExists('driver_attechments');
    }
};
