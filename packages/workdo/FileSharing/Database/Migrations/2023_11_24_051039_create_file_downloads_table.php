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
        if (!Schema::hasTable('file_downloads')) {
            Schema::create('file_downloads', function (Blueprint $table) {
                $table->id();
                $table->integer('file_id')->nullable();
                $table->string('file_path');
                $table->string('ip_address')->nullable();
                $table->text('details');
                $table->dateTime('date');
                $table->integer('workspace')->default(0);
                $table->integer('created_by')->default(0);
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
        Schema::dropIfExists('file_downloads');
    }
};
