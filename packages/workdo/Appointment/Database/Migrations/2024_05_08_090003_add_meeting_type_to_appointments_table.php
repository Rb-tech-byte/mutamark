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
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'meeting_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('meeting_type')->nullable()->after('week_day');
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
        Schema::table('appointments', function (Blueprint $table) {
        });
    }
};
