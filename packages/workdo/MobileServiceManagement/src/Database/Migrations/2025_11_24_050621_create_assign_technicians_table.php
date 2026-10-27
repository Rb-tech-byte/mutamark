<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mobile_service_assign_technicians')) {
            Schema::create('mobile_service_assign_technicians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mobile_service_id')->nullable()->index();
                $table->foreignId('technician_id');
                $table->date('completion_date');
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('technician_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('mobile_service_id')->references('id')->on('product_service_items')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('mobile_service_assign_technicians');
    }
};
