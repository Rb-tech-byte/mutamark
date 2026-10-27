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
        Schema::table('fleet_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('fleet_customers', 'customer')) {
                $table->string('customer')->nullable()->after('user_id');
                $table->string('client_id')->nullable()->after('customer');
                
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
        Schema::table('fleet_customers', function (Blueprint $table) {

        });
    }
};
