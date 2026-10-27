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
        if (Schema::hasTable('jobs') && !Schema::hasColumn('jobs', 'terms_and_conditions')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->text('terms_and_conditions')->nullable()->after('requirement');
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
        Schema::table('jobs', function (Blueprint $table) {

        });
    }
};
