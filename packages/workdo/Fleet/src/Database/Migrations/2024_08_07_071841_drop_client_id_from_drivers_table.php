<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'client_id')) {
                $table->dropColumn('client_id');
            }

            if (Schema::hasColumn('drivers', 'customer')) {
                $table->renameColumn('customer', 'select_driver_type');
            }

        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
