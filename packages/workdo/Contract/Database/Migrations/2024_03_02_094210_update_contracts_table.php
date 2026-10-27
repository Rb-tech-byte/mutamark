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
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasTable('contracts') && !Schema::hasColumn('contracts', 'user_id') ) {
                $table->integer('user_id')->nullable()->change();
                $table->integer('type')->nullable()->change();
                $table->date('start_date')->nullable()->change();
                $table->date('end_date')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
};
