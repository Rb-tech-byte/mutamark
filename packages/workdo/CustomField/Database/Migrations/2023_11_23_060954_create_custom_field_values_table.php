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
        if(!Schema::hasTable('custom_field_values'))
        {
            Schema::create('custom_field_values', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('record_id');
                $table->unsignedBigInteger('field_id');
                $table->string('value');
                $table->timestamps();
                $table->unique(
                    [
                        'record_id',
                        'field_id',
                    ]
                );
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
        Schema::dropIfExists('custom_field_values');
    }
};
