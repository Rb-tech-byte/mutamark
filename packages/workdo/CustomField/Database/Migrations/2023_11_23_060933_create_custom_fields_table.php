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
        if(!Schema::hasTable('custom_fields'))
        {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('type');
                $table->string('module');
                $table->string('is_required')->default(0);
                $table->string('sub_module');
                $table->integer('created_by')->default(0);
                $table->integer('workspace_id')->default(0);
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
        Schema::dropIfExists('custom_fields');
    }
};
