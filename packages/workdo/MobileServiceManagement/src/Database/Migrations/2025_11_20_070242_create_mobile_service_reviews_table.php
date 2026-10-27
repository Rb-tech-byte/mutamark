<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mobile_service_reviews')) {
            Schema::create('mobile_service_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mobile_service_id')->nullable()->index();
                $table->string('name');
                $table->string('email');
                $table->string('rating');
                $table->longText('review');
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('mobile_service_id')->references('id')->on('product_service_items')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_service_reviews');
    }
};
