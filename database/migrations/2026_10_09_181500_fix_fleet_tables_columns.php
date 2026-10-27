<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('customer_name' ,191)->nullable()->change(); });
            Schema::table('bookings', function (\Illuminate\Database\Schema\Blueprint $table) { $table->text('notes')->nullable()->change(); });
            Schema::table('bookings', function (\Illuminate\Database\Schema\Blueprint $table) { $table->double('total_price')->nullable()->default(0)->change(); });
        }

        if (Schema::hasTable('fuels')) {
            Schema::table('fuels', function (\Illuminate\Database\Schema\Blueprint $table) { $table->text('notes')->nullable()->change(); });
            Schema::table('fuels', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('fuel_type' ,100)->nullable()->change(); });
            Schema::table('fuels', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('odometer_reading' ,50)->nullable()->change(); });
            Schema::table('fuels', function (\Illuminate\Database\Schema\Blueprint $table) { $table->double('cost')->nullable()->default(0)->change(); });
            Schema::table('fuels', function (\Illuminate\Database\Schema\Blueprint $table) { $table->double('total_cost')->nullable()->default(0)->change(); });
        }

        if (Schema::hasTable('maintenances')) {
            Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('service_for')->nullable()->change(); });
            Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('maintenance_type')->nullable()->change(); });
            Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('charge_bear_by' ,100)->nullable()->default('Company')->change(); });
            Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('charge' ,100)->nullable()->default('0')->change(); });
            Schema::table('maintenances', function (\Illuminate\Database\Schema\Blueprint $table) { $table->double('total_cost')->nullable()->default(0)->change(); });
        }
    }

    public function down(): void
    {
    }
};
