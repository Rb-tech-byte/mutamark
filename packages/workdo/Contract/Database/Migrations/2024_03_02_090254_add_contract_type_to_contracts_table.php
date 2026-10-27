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
            if (!Schema::hasColumn('contracts', 'contract_id')) {
                $table->integer('contract_id')->nullable();
                $table->string('contract_type')->nullable();
                $table->integer('is_active')->default(1);
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
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('type');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('contract_id');
            $table->dropColumn('contract_type');
            $table->dropColumn('is_active');
        });
    }
};
