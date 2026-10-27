<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mobile_service_payments')) {
            Schema::create('mobile_service_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number', 50);
                $table->date('payment_date');
                $table->foreignId('mobile_service_id');
                $table->foreignId('bank_account_id')->nullable();
                $table->string('reference_number', 100)->nullable();
                $table->decimal('service_price', 15, 2);
                $table->decimal('tax_amount', 15, 2);
                $table->decimal('repair_charge', 15, 2);
                $table->json('extra_charges')->nullable();
                $table->decimal('payment_amount', 15, 2);
                $table->enum('payment_status', ['pending', 'cleared', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('mobile_service_id')->references('id')->on('mobile_service_requests')->onDelete('cascade');
                $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('mobile_service_payments');
    }
};
