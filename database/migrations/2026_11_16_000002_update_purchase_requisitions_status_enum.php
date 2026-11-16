<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_requisitions') && Schema::hasColumn('purchase_requisitions', 'status')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `purchase_requisitions` MODIFY COLUMN `status` ENUM('draft','submitted','approved','rejected','converted','converted_to_rfq','converted_to_po','cancelled') NOT NULL DEFAULT 'draft'");
            }
        }
    }

    public function down(): void
    {
        // Keep widened enum values on rollback to avoid truncating existing rows.
    }
};

