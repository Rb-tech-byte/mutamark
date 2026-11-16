<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Border & Transit Management (Module 7)
        if (!Schema::hasTable('cf_border_movements')) {
            Schema::create('cf_border_movements', function (Blueprint $table) {
                $table->id();
                $table->string('movement_number', 60)->unique();
                $table->unsignedBigInteger('cf_job_id')->nullable();
                $table->string('corridor', 120); // e.g. Tanzania-Zambia (Tunduma/Nakonde), TZ-KE, TZ-UG, TZ-RW, TZ-BI, TZ-DRC
                $table->string('truck_registration', 60);
                $table->string('trailer_number', 60)->nullable();
                $table->string('driver_name', 120)->nullable();
                $table->string('cargo_type', 120)->nullable();
                $table->enum('movement_type', ['import', 'export', 'transit', 'empty_return'])->default('transit');
                $table->string('origin_country', 80)->default('Tanzania');
                $table->string('destination_country', 80)->nullable();
                $table->dateTime('border_entry_at')->nullable();
                $table->dateTime('border_exit_at')->nullable();
                $table->string('customs_reference', 100)->nullable();
                $table->string('rotation_number', 100)->nullable();
                $table->string('transit_doc_number', 100)->nullable(); // C.32 / T1
                $table->string('gate_pass_number', 100)->nullable();
                $table->enum('clearance_status', ['arrived', 'customs_processing', 'held', 'released', 'exited'])->default('arrived');
                $table->text('delay_reason')->nullable();
                $table->decimal('border_expenses', 15, 2)->default(0);
                $table->string('currency', 10)->default('TZS');
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index(['created_by', 'clearance_status']);
                $table->index(['cf_job_id']);
            });
        }

        // 2. Freight Forwarding Bookings & Demurrage/Detention (Module 5)
        if (!Schema::hasTable('cf_freight_bookings')) {
            Schema::create('cf_freight_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_number', 60)->unique();
                $table->unsignedBigInteger('cf_job_id')->nullable();
                $table->enum('freight_mode', ['sea', 'air', 'road', 'rail', 'multimodal'])->default('sea');
                $table->string('shipping_line_carrier', 150)->nullable();
                $table->string('vessel_flight_voyage', 120)->nullable();
                $table->string('port_of_loading', 120)->nullable();
                $table->string('port_of_discharge', 120)->nullable();
                $table->date('etd')->nullable();
                $table->date('eta')->nullable();
                $table->string('delivery_order_number', 80)->nullable();
                $table->decimal('freight_cost', 15, 2)->default(0);
                $table->decimal('port_handling_charges', 15, 2)->default(0);
                $table->integer('free_days')->default(14);
                $table->integer('demurrage_days')->default(0);
                $table->decimal('demurrage_amount', 15, 2)->default(0);
                $table->decimal('detention_amount', 15, 2)->default(0);
                $table->string('currency', 10)->default('USD');
                $table->enum('status', ['quoted', 'booked', 'in_transit', 'arrived', 'do_issued', 'handed_over'])->default('booked');
                $table->text('shipping_instructions')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index(['created_by', 'status']);
                $table->index(['cf_job_id']);
            });
        }

        // 3. Configurable Customs Procedures, CPC Codes & Tax Rates (Module 4)
        if (!Schema::hasTable('cf_customs_rules')) {
            Schema::create('cf_customs_rules', function (Blueprint $table) {
                $table->id();
                $table->string('cpc_code', 30);
                $table->string('procedure_name', 150);
                $table->string('country_code', 10)->default('TZ');
                $table->decimal('import_duty_rate', 6, 2)->default(0);
                $table->decimal('vat_rate', 6, 2)->default(18.00);
                $table->decimal('excise_duty_rate', 6, 2)->default(0);
                $table->decimal('rdl_rate', 6, 2)->default(1.50); // Railway Development Levy
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index(['created_by', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cf_customs_rules');
        Schema::dropIfExists('cf_freight_bookings');
        Schema::dropIfExists('cf_border_movements');
    }
};

