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
        Schema::table('email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('email_templates', 'workspace_id')) {
                $table->integer('workspace_id')->default(0)->after('created_by');
            }
        });

        Schema::table('email_template_langs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_template_langs', 'workspace_id')) {
                $table->integer('workspace_id')->default(0)->after('module_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'workspace_id')) {
                $table->dropColumn('workspace_id');
            }
        });

        Schema::table('email_template_langs', function (Blueprint $table) {
            if (Schema::hasColumn('email_template_langs', 'workspace_id')) {
                $table->dropColumn('workspace_id');
            }
        });
    }
};
