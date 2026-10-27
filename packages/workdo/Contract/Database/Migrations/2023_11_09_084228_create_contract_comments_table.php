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
        if(!Schema::hasTable('contract_comments'))
        {
            Schema::create('contract_comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('contract_id');
                $table->string('user_id');
                $table->string('comment');
                $table->integer('workspace')->nullable();
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
        Schema::dropIfExists('contract_comments');
    }
};
