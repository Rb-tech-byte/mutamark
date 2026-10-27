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
        if (!Schema::hasTable('mobile_service_live_trackings')) {
            Schema::create('mobile_service_live_trackings', function (Blueprint $table) {
                $table->id();
                $table->string('service_id');
                $table->foreignId('tracking_status_id')->nullable()->index();
                $table->dateTime('date');
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->foreign('tracking_status_id')->references('id')->on('mobile_tracking_statuses')->onDelete('set null');
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
        Schema::dropIfExists('mobile_service_live_trackings');
    }
};
