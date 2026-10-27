<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_service_policies')) {
            Schema::create('mobile_service_policies', function (Blueprint $table) {
                $table->id();
                $table->string('policy_name');
                $table->longText('description')->nullable();
                $table->time('response_time')->nullable();
                $table->time('resolution_time')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();

                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_service_policies');
    }
};
