<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (\Illuminate\Database\Schema\Blueprint $table) { $table->decimal('budget' ,15,2)->nullable()->default(0)->change(); });
        }
        if (Schema::hasTable('project_milestones')) {
            Schema::table('project_milestones', function (\Illuminate\Database\Schema\Blueprint $table) { $table->decimal('cost' ,15,2)->nullable()->default(0)->change(); });
        }
    }

    public function down(): void
    {
    }
};
