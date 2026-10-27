<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_tracking_statuses')) {
            Schema::create('mobile_tracking_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('icon_name');
                $table->string('status_color', 10)->default('#FF6B6B');
                $table->string('status_name');
                $table->integer('order')->default(0);


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
        Schema::dropIfExists('mobile_tracking_statuses');
    }
};
