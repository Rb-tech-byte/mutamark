<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            $updates = [];
            if (Schema::hasColumn('users', 'storage_limit')) {
                $updates['storage_limit'] = -1;
            }
            if (Schema::hasColumn('users', 'total_user')) {
                $updates['total_user'] = -1;
            }
            if (Schema::hasColumn('users', 'plan_expire_date')) {
                $updates['plan_expire_date'] = null;
            }
            if (!empty($updates)) {
                DB::table('users')->update($updates);
            }
        }

        if (Schema::hasTable('plans')) {
            $planUpdates = [];
            if (Schema::hasColumn('plans', 'storage_limit')) {
                $planUpdates['storage_limit'] = -1;
            }
            if (Schema::hasColumn('plans', 'number_of_users')) {
                $planUpdates['number_of_users'] = -1;
            }
            if (!empty($planUpdates)) {
                DB::table('plans')->update($planUpdates);
            }
        }
    }

    public function down(): void
    {
        // No-op: unlimited ERP access should remain enabled.
    }
};

