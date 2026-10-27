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
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->default(0)->after('guard_name');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('add_on')->nullable()->change();
            $table->string('label')->nullable()->change();
        });

        Schema::table('add_ons', function (Blueprint $table) {
            $table->string('package_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
