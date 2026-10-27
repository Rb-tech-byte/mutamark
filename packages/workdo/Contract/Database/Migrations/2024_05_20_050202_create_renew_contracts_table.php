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
    
        if(!Schema::hasTable('renew_contracts'))
        {
            Schema::create('renew_contracts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('contract_id');
                $table->string('value')->nullable();
                $table->date('start_date');
                $table->date('end_date');
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
        Schema::dropIfExists('renew_contracts');
    }
};
