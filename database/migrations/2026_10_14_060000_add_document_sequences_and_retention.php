<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('prefix', 16);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unique(['company_id', 'prefix', 'year']);
        });
        if (!Schema::hasColumn('documents', 'deleted_at')) {
            Schema::table('documents', fn (Blueprint $table) => $table->softDeletes());
        }
    }

    public function down(): void
    {
        Schema::table('documents', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::dropIfExists('document_sequences');
    }
};
