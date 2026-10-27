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
        // 1. Extend bank_accounts table with currency tracking
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'currency')) {
                $table->string('currency', 10)->default('TZS')->after('account_type');
            }
            if (!Schema::hasColumn('bank_accounts', 'currency_rate')) {
                $table->decimal('currency_rate', 18, 6)->default(1.000000)->after('currency');
            }
        });

        // 2. Create currencies table
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique(); // TZS, USD, EUR, GBP, KES, UGX, etc.
                $table->string('name', 100);
                $table->string('symbol', 10)->default('$');
                $table->decimal('exchange_rate', 18, 6)->default(1.000000); // 1 Unit = X TZS
                $table->decimal('buy_rate', 18, 6)->default(1.000000);
                $table->decimal('sell_rate', 18, 6)->default(1.000000);
                $table->decimal('bot_indicative_rate', 18, 6)->nullable(); // Bank of Tanzania official rate
                $table->boolean('is_base')->default(false); // true for TZS
                $table->boolean('is_active')->default(true);
                $table->boolean('auto_sync')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Create currency_exchange_rate_histories table
        if (!Schema::hasTable('currency_exchange_rate_histories')) {
            Schema::create('currency_exchange_rate_histories', function (Blueprint $table) {
                $table->id();
                $table->string('currency_code', 10)->index();
                $table->date('rate_date')->index();
                $table->decimal('exchange_rate', 18, 6);
                $table->decimal('buy_rate', 18, 6);
                $table->decimal('sell_rate', 18, 6);
                $table->decimal('bot_indicative_rate', 18, 6)->nullable();
                $table->string('source', 50)->default('bot_indicative'); // bot_indicative, central_bank, manual
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // 4. Create forex_revaluations table (IAS 21 Real-time Unrealized Forex Engine)
        if (!Schema::hasTable('forex_revaluations')) {
            Schema::create('forex_revaluations', function (Blueprint $table) {
                $table->id();
                $table->string('revaluation_number', 50)->unique();
                $table->date('revaluation_date')->index();
                $table->string('currency_code', 10);
                $table->decimal('market_exchange_rate', 18, 6);
                $table->decimal('book_exchange_rate', 18, 6);
                $table->decimal('foreign_currency_balance', 18, 2)->default(0.00);
                $table->decimal('book_value_base', 18, 2)->default(0.00); // TZS
                $table->decimal('revalued_value_base', 18, 2)->default(0.00); // TZS
                $table->decimal('unrealized_gain_loss', 18, 2)->default(0.00); // Positive = Gain, Negative = Loss
                $table->string('status', 30)->default('posted'); // draft, posted, reversed
                $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('reversal_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
                $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
                $table->foreignId('gl_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->string('verification_seal', 64)->nullable();
                $table->foreignId('revalued_by')->nullable();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forex_revaluations');
        Schema::dropIfExists('currency_exchange_rate_histories');
        Schema::dropIfExists('currencies');

        Schema::table('bank_accounts', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('bank_accounts', 'currency')) $colsToDrop[] = 'currency';
            if (Schema::hasColumn('bank_accounts', 'currency_rate')) $colsToDrop[] = 'currency_rate';
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
