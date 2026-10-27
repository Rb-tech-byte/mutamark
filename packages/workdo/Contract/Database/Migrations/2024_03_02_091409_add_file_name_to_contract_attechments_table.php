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
        Schema::table('contract_attechments', function (Blueprint $table) {
            if (!Schema::hasColumn('contract_attechments', 'file_name')) {
                $table->string('file_name');
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
        Schema::table('contract_attechments', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
