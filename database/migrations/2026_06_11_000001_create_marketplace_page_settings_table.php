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
        if (!Schema::hasTable('marketplace_page_settings')) {
            Schema::create('marketplace_page_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 191);
                $table->string('module', 191);
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_page_settings');
    }
};
