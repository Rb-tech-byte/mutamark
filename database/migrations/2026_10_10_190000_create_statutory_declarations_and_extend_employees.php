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
        // 1. Extend employees table with dedicated statutory fields if not present
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'nssf_number')) {
                $table->string('nssf_number', 50)->nullable()->after('tax_payer_id');
            }
            if (!Schema::hasColumn('employees', 'nhif_number')) {
                $table->string('nhif_number', 50)->nullable()->after('nssf_number');
            }
            if (!Schema::hasColumn('employees', 'wcf_exempt')) {
                $table->boolean('wcf_exempt')->default(false)->after('nhif_number');
            }
            if (!Schema::hasColumn('employees', 'sdl_exempt')) {
                $table->boolean('sdl_exempt')->default(false)->after('wcf_exempt');
            }
        });

        // 2. Create hrm_statutory_declarations table
        if (!Schema::hasTable('hrm_statutory_declarations')) {
            Schema::create('hrm_statutory_declarations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
                $table->string('declaration_number', 50)->unique();
                $table->string('statutory_type', 50)->default('tra_paye'); // tra_paye, nssf_pension, wcf_fund, sdl_levy, combined_return
                $table->string('title');
                $table->string('filing_month', 20)->default('2026-10'); // YYYY-MM
                $table->date('due_date');
                $table->string('gepg_control_number', 50)->nullable();
                $table->decimal('total_taxable_gross', 15, 2)->default(0.00);
                $table->decimal('employee_deduction_amount', 15, 2)->default(0.00);
                $table->decimal('employer_contribution_amount', 15, 2)->default(0.00);
                $table->decimal('penalty_amount', 15, 2)->default(0.00);
                $table->decimal('total_payable_amount', 15, 2)->default(0.00);
                $table->string('payment_status', 30)->default('draft'); // draft, submitted, paid, reconciled
                $table->date('payment_date')->nullable();
                $table->string('payment_method', 50)->nullable(); // bank_transfer, gepg_gateway, lipa_namba
                $table->string('bank_reference', 100)->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->text('notes')->nullable();
                $table->string('verification_seal', 64)->nullable();
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
        Schema::dropIfExists('hrm_statutory_declarations');

        Schema::table('employees', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('employees', 'nssf_number')) $colsToDrop[] = 'nssf_number';
            if (Schema::hasColumn('employees', 'nhif_number')) $colsToDrop[] = 'nhif_number';
            if (Schema::hasColumn('employees', 'wcf_exempt')) $colsToDrop[] = 'wcf_exempt';
            if (Schema::hasColumn('employees', 'sdl_exempt')) $colsToDrop[] = 'sdl_exempt';
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
