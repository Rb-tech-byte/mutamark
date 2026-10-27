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
        if (!Schema::hasTable('mobile_service_requests')) {
            Schema::create('mobile_service_requests', function (Blueprint $table) {
                $table->id();
                $table->string('service_id');
                $table->string('customer_name');
                $table->string('email');
                $table->string('mobile_no');
                $table->text('address')->nullable();
                $table->string('priority');
                $table->string('mobile_model');
                $table->text('description');
                $table->text('special_requests')->nullable();
                $table->longtext('attachment')->nullable();
                $table->integer('is_approve')->nullable();
                $table->string('tracking_status')->nullable();;
                $table->string('tracking_status_log')->nullable();;
                $table->integer('is_technician_asign')->default(0);
                $table->integer('is_repair_end')->default(0);
                $table->integer('payment_status')->default(0);
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->foreignId('mobile_service_id')->nullable()->index();
                $table->foreignId('device_brand')->nullable()->index();
                $table->foreign('mobile_service_id')->references('id')->on('product_service_items')->onDelete('set null');
                $table->foreign('device_brand')->references('id')->on('mobile_device_brands')->onDelete('set null');

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
        Schema::dropIfExists('mobile_service_requests');
    }
};
