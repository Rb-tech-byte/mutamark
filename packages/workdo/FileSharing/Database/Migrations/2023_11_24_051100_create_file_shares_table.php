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
        if (!Schema::hasTable('file_shares')) {
            Schema::create('file_shares', function (Blueprint $table) {
                $table->id();
                $table->string('user_id')->nullable();
                $table->string('file_path');
                $table->string('file_size');
                $table->string('file_status')->default('Available');
                $table->string('auto_destroy')->default('off');
                $table->string('filesharing_type');
                $table->string('email')->nullable();
                $table->integer('is_pass_enable')->default(0);
                $table->string('password')->nullable();
                $table->integer('total_downloads')->nullable();
                $table->longtext('description')->nullable();
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
        Schema::dropIfExists('file_shares');
    }
};
