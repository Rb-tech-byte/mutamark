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
        if (!Schema::hasTable('journal_entries'))
        {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('reference')->nullable();
                $table->text('description')->nullable();
                $table->integer('journal_id')->default(0);
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
        Schema::dropIfExists('journal_entries');
    }
};
