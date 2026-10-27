<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('timesheets')) {
            Schema::create('timesheets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->unsignedBigInteger('task_id')->nullable()->index();
                $table->date('date');
                $table->integer('hours')->default(0);
                $table->integer('minutes')->default(0);
                $table->double('hourly_rate')->default(0);
                $table->double('total_cost')->default(0);
                $table->string('type')->default('Project');
                $table->string('status')->default('Approved');
                $table->longText('notes')->nullable();
                $table->integer('workspace_id')->nullable();
                $table->integer('created_by')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
