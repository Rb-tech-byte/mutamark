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
        if (!Schema::hasTable('drivers')) {
            
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('lincese_number')->nullable();
                $table->integer('lincese_type')->nullable();
                $table->date('expiry_date')->nullable();
                $table->date('join_date')->nullable();
                $table->string('address')->nullable();
                $table->date('dob')->nullable();
                $table->string('Working_time')->nullable();
                $table->string('driver_status')->nullable();
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
        Schema::dropIfExists('drivers');
    }
};
