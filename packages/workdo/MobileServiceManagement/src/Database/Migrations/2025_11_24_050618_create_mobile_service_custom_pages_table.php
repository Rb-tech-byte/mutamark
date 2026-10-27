<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_service_custom_pages')) {
            Schema::create('mobile_service_custom_pages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('title');
                $table->string('slug');
                $table->string('enable_page_footer')->default('on');
                $table->longText('contents');
                $table->boolean('is_editable')->default(true);
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
        Schema::dropIfExists('mobile_service_custom_pages');
    }
};
