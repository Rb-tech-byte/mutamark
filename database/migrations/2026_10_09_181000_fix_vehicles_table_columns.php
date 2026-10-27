<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('lincense_plate' ,50)->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('vehical_id_num' ,100)->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('department_id')->nullable()->default(1)->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->date('registration_date')->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('driver_name')->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('vehicle_type')->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('fuel_type')->nullable()->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->integer('seat_capacity')->nullable()->default(4)->change(); });
            Schema::table('vehicles', function (\Illuminate\Database\Schema\Blueprint $table) { $table->string('model_year' ,10)->nullable()->change(); });
        }
    }

    public function down(): void
    {
    }
};
