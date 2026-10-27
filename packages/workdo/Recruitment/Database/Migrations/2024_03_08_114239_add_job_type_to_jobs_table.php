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
        Schema::table('jobs', function (Blueprint $table) {
                if (!Schema::hasColumn('jobs', 'recruitment_type')) {
                    $table->string('recruitment_type')->nullable()->after('custom_question');
                }
            if (!Schema::hasColumn('jobs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('recruitment_type')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('jobs', 'job_type')) {
                $table->string('job_type')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('jobs', 'salary_from')) {
                $table->integer('salary_from')->nullable()->after('job_type');
            }
            if (!Schema::hasColumn('jobs', 'salary_to')) {
                $table->integer('salary_to')->nullable()->after('salary_from');
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
        Schema::table('jobs', function (Blueprint $table) {
        });
    }
};
