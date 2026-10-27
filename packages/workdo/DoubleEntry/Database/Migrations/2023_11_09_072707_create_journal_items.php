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
        if (!Schema::hasTable('journal_items'))
        {
            Schema::create('journal_items', function (Blueprint $table) {
                $table->id();
                $table->integer('journal')->default(0);
                $table->integer('account')->default(0);
                $table->text('description')->nullable();
                $table->float('debit')->default(0.0);
                $table->float('credit')->default(0.0);
                $table->integer('workspace')->default(0);
                $table->integer('created_by')->default(0);
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
        Schema::dropIfExists('');
    }
};
