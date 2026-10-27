<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('signature_version')->default(1);
            $table->string('content_hash', 64)->nullable(); $table->longText('signed_payload')->nullable();
            $table->unsignedBigInteger('signer_id')->nullable(); $table->timestamp('revoked_at')->nullable();
            $table->string('source_model')->nullable(); $table->unsignedBigInteger('source_id')->nullable();
        });
        Schema::table('financial_audit_logs', fn (Blueprint $table) => $table->string('payload_hash', 64)->nullable());
    }
    public function down(): void { /* Preserve signature and compliance history. */ }
};
