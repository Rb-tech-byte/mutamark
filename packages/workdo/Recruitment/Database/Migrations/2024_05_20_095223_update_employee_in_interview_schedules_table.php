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
        if (Schema::hasColumn('interview_schedules', 'user_id')) {
            Schema::table('interview_schedules', function (Blueprint $table) {
                $table->integer('user_id')->nullable()->change();
            });
        }

        if (Schema::hasColumn('interview_schedules', 'employee')) {
            Schema::table('interview_schedules', function (Blueprint $table) {
                $table->integer('employee')->nullable()->change();
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
        Schema::table('', function (Blueprint $table) {
        });
    }
};
