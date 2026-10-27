<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('data_integrity_exceptions')) Schema::create('data_integrity_exceptions', function (Blueprint $table) {
            $table->id(); $table->string('source_table'); $table->unsignedBigInteger('source_id');
            $table->string('exception_type'); $table->text('encrypted_snapshot'); $table->string('snapshot_sha256', 64);
            $table->text('resolution'); $table->timestamp('recorded_at');
            $table->unique(['source_table', 'source_id', 'exception_type'], 'integrity_exception_source_unique');
        });
        DB::transaction(function () {
            $orphans = DB::table('delivery_note_items as item')->leftJoin('delivery_notes as parent', 'parent.id', '=', 'item.delivery_note_id')->whereNull('parent.id')->select('item.*')->lockForUpdate()->get();
            foreach ($orphans as $item) {
                $json = json_encode($item, JSON_THROW_ON_ERROR);
                DB::table('data_integrity_exceptions')->insertOrIgnore([
                    'source_table' => 'delivery_note_items', 'source_id' => $item->id, 'exception_type' => 'missing_parent',
                    'encrypted_snapshot' => Crypt::encryptString($json), 'snapshot_sha256' => hash('sha256', $json),
                    'resolution' => 'Orphan preserved in encrypted quarantine. No delivery header reconstructed and no inventory movement performed.', 'recorded_at' => now(),
                ]);
                DB::table('delivery_note_items')->where('id', $item->id)->delete();
            }
        });
    }
    public function down(): void { /* Preserve recovered history. */ }
};
