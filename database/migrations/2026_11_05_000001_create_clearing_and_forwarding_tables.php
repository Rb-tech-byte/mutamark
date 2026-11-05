y<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Job File
        Schema::create('cf_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique()->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('consignee_name')->nullable();
            $table->string('consignor_name')->nullable();
            $table->string('service_type')->default('import');  // import, export, transit, domestic
            $table->string('transport_mode')->default('sea');  // sea, air, road, rail
            $table->string('customs_regime')->default('home_use');  // home_use, transit, warehousing, temporary_import, export, re_export
            $table->string('status')->default('draft')->index();
            $table->string('priority')->default('normal');  // normal, urgent, critical
            $table->unsignedBigInteger('case_owner_id')->nullable();
            $table->unsignedBigInteger('declarant_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('workspace_id')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('customer_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Shipment Particulars
        Schema::create('cf_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('bl_awb_number')->nullable()->index();
            $table->string('vessel_flight_truck')->nullable();
            $table->string('voyage_number')->nullable();
            $table->string('shipping_line_airline')->nullable();
            $table->unsignedBigInteger('shipping_agent_id')->nullable();
            $table->string('origin_country')->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->string('terminal_icd')->nullable();
            $table->string('final_destination')->nullable();
            $table->date('etd')->nullable();
            $table->date('atd')->nullable();
            $table->date('eta')->nullable();
            $table->date('ata')->nullable();
            $table->string('manifest_rotation_number')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 3. Cargo Packages & Lines
        Schema::create('cf_cargo_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->text('commodity_description');
            $table->string('package_type')->default('packages');
            $table->decimal('package_count', 15, 2)->default(1);
            $table->decimal('gross_weight_kg', 15, 3)->default(0);
            $table->decimal('net_weight_kg', 15, 3)->nullable();
            $table->decimal('cbm', 15, 3)->nullable();
            $table->string('marks_numbers')->nullable();
            $table->string('hs_code')->nullable()->index();
            $table->decimal('customs_value', 18, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 4. Containers Tracker
        Schema::create('cf_containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('container_number')->index();
            $table->string('size_type')->default('20FT');  // 20FT, 40FT, 40HC, REEFER, FLAT_RACK, etc.
            $table->string('seal_number')->nullable();
            $table->string('shipping_line')->nullable();
            $table->integer('free_days')->default(14);
            $table->date('discharge_date')->nullable();
            $table->date('gate_out_date')->nullable();
            $table->date('demurrage_start_date')->nullable();
            $table->date('empty_return_deadline')->nullable();
            $table->date('empty_returned_at')->nullable();
            $table->string('empty_depot_name')->nullable();
            $table->string('status')->default('at_terminal');  // at_terminal, cleared_gate_out, in_transit, empty_returned, demurrage_accruing
            $table->string('interchange_receipt_number')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 5. Document Checklist
        Schema::create('cf_document_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('document_type');  // bill_of_lading, commercial_invoice, packing_list, certificate_of_origin, etc.
            $table->string('document_name');
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_original_received')->default(false);
            $table->string('document_reference')->nullable();
            $table->string('file_path')->nullable();
            $table->string('verification_status')->default('pending');  // pending, verified, rejected, waived
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 6. Permits & Licences
        Schema::create('cf_permits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('agency');  // TBS, TMDA, GCLA, TAEC, WMA, MIN_AGRIC, etc.
            $table->string('permit_type');  // import_permit, inspection_certificate, exemption_letter, license
            $table->string('application_number')->nullable();
            $table->string('permit_number')->nullable()->index();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('required');  // required, applied, approved, rejected, expired
            $table->text('special_conditions')->nullable();
            $table->string('certificate_file')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 7. HS Classifications Worksheet
        Schema::create('cf_classifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->unsignedBigInteger('cf_cargo_line_id')->nullable();
            $table->string('proposed_hs_code');
            $table->string('approved_hs_code')->nullable();
            $table->text('tariff_description')->nullable();
            $table->decimal('duty_rate_percentage', 5, 2)->default(0);
            $table->decimal('vat_rate_percentage', 5, 2)->default(18.0);
            $table->decimal('excise_rate_percentage', 5, 2)->default(0);
            $table->text('classification_rationale')->nullable();
            $table->string('wco_general_rule')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 8. Customs Valuation Worksheet
        Schema::create('cf_valuations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->decimal('invoice_value_fob', 18, 2)->default(0);
            $table->decimal('freight_charges', 18, 2)->default(0);
            $table->decimal('insurance_charges', 18, 2)->default(0);
            $table->decimal('other_additions', 18, 2)->default(0);
            $table->decimal('customs_value_cif_foreign', 18, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->decimal('tra_exchange_rate', 18, 4)->default(1);
            $table->decimal('customs_value_tzs', 18, 2)->default(0);
            $table->decimal('import_duty_tzs', 18, 2)->default(0);
            $table->decimal('vat_tzs', 18, 2)->default(0);
            $table->decimal('railway_levy_tzs', 18, 2)->default(0);
            $table->decimal('customs_processing_fee_tzs', 18, 2)->default(0);
            $table->decimal('other_statutory_taxes_tzs', 18, 2)->default(0);
            $table->decimal('total_statutory_taxes_tzs', 18, 2)->default(0);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 9. Customs Declarations
        Schema::create('cf_declarations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('customs_office')->default('Dar es Salaam Port');
            $table->string('declaration_type')->default('TANSAD');
            $table->string('declaration_number')->nullable()->index();
            $table->string('tancis_reference')->nullable()->index();
            $table->string('regime_code')->nullable();
            $table->unsignedBigInteger('declarant_user_id')->nullable();
            $table->dateTime('submission_date')->nullable();
            $table->string('assessment_number')->nullable();
            $table->dateTime('assessment_date')->nullable();
            $table->decimal('assessed_amount_tzs', 18, 2)->default(0);
            $table->string('payment_control_number')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('release_order_number')->nullable();
            $table->dateTime('release_date')->nullable();
            $table->string('status')->default('draft')->index();  // draft, submitted, queried, under_examination, assessed, paid, released, cancelled
            $table->string('customs_channel')->nullable();  // green, yellow, red, blue
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 10. Customs Events & Queries
        Schema::create('cf_customs_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_declaration_id')->index();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('event_type');  // query_raised, query_answered, scanning_scheduled, physical_inspection, release_granted, customs_hold
            $table->string('officer_name')->nullable();
            $table->dateTime('event_time');
            $table->string('location')->nullable();
            $table->text('instructions')->nullable();
            $table->text('findings')->nullable();
            $table->string('outcome')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('cf_declaration_id')->references('id')->on('cf_declarations')->onDelete('cascade');
            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 11. Charge Estimates & Quotation
        Schema::create('cf_charge_estimates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('charge_category');  // statutory_duty, port_charges, shipping_line, terminal_handling, agency_fee, transport_haulage, etc.
            $table->string('charge_name');
            $table->boolean('is_disbursement')->default(true);
            $table->decimal('estimated_amount', 18, 2);
            $table->string('currency', 10)->default('TZS');
            $table->decimal('exchange_rate', 18, 4)->default(1);
            $table->decimal('amount_tzs', 18, 2);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 12. Disbursements Tracker
        Schema::create('cf_disbursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('payee_name');  // TRA, TPA, TICTS, Shipping Line, etc.
            $table->unsignedBigInteger('payee_vendor_id')->nullable();
            $table->string('charge_category');
            $table->string('description');
            $table->string('payment_method')->default('bank_transfer');  // bank_transfer, control_number_gepg, cheque, cash
            $table->decimal('disbursed_amount', 18, 2);
            $table->string('currency', 10)->default('TZS');
            $table->decimal('amount_tzs', 18, 2);
            $table->string('payment_reference')->nullable();
            $table->string('receipt_voucher_number')->nullable();
            $table->string('receipt_file_path')->nullable();
            $table->date('payment_date');
            $table->boolean('is_reconciled')->default(false);
            $table->unsignedBigInteger('authorized_by')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 13. Transport Orders
        Schema::create('cf_transport_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('order_number')->unique()->index();
            $table->string('transporter_type')->default('internal_fleet');  // internal_fleet, third_party_carrier
            $table->unsignedBigInteger('transporter_vendor_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('trailer_number')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('driver_license')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('pickup_location');
            $table->string('delivery_destination');
            $table->string('route_corridor')->nullable();
            $table->text('cargo_summary')->nullable();
            $table->decimal('agreed_freight_cost', 18, 2)->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->dateTime('dispatch_date')->nullable();
            $table->dateTime('expected_delivery_date')->nullable();
            $table->dateTime('actual_delivery_date')->nullable();
            $table->string('status')->default('draft')->index();  // draft, dispatched, in_transit, at_border, delivered, cancelled
            $table->string('waybill_file_path')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 14. In-Transit Tracking & Border Events
        Schema::create('cf_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->unsignedBigInteger('cf_transport_order_id')->nullable()->index();
            $table->string('milestone_code');  // cargo_landed, docs_verified, declaration_passed, duties_paid, gate_out_port, dispatched, checkpoint_cleared, border_cleared, delivered_pod
            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('event_time');
            $table->text('remarks')->nullable();
            $table->string('photo_evidence_path')->nullable();
            $table->boolean('is_customer_visible')->default(true);
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
            $table->foreign('cf_transport_order_id')->references('id')->on('cf_transport_orders')->onDelete('set null');
        });

        // 15. Deliveries
        Schema::create('cf_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->unsignedBigInteger('cf_transport_order_id')->nullable()->index();
            $table->string('delivery_number')->unique()->index();
            $table->date('delivery_date');
            $table->text('delivery_address');
            $table->decimal('packages_delivered', 15, 2)->default(0);
            $table->string('cargo_condition')->default('sound_and_intact');  // sound_and_intact, shortage, damaged, seal_broken, disputed
            $table->text('condition_notes')->nullable();
            $table->string('delivery_note_file_path')->nullable();
            $table->string('status')->default('scheduled');  // scheduled, delivered_pending_pod, pod_confirmed, discrepancy_reported
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
            $table->foreign('cf_transport_order_id')->references('id')->on('cf_transport_orders')->onDelete('set null');
        });

        // 16. Proof of Delivery (POD)
        Schema::create('cf_proof_of_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_delivery_id')->index();
            $table->unsignedBigInteger('cf_job_id')->index();
            $table->string('recipient_name');
            $table->string('recipient_title')->nullable();
            $table->string('recipient_id_type')->default('national_id_nida');  // national_id_nida, passport, driving_license, staff_id
            $table->string('recipient_id_number')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->longText('signature_data')->nullable();  // base64 canvas signature
            $table->dateTime('signed_at');
            $table->json('photo_evidence_paths')->nullable();
            $table->boolean('official_stamp_received')->default(false);
            $table->string('verification_hash')->nullable()->index();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();

            $table->foreign('cf_delivery_id')->references('id')->on('cf_deliveries')->onDelete('cascade');
            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });

        // 17. Job Costing & Profitability
        Schema::create('cf_job_costings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cf_job_id')->unique()->index();
            $table->string('currency', 10)->default('TZS');
            $table->decimal('agency_fee_revenue', 18, 2)->default(0);
            $table->decimal('documentation_fee_revenue', 18, 2)->default(0);
            $table->decimal('transport_markup_revenue', 18, 2)->default(0);
            $table->decimal('total_service_revenue', 18, 2)->default(0);
            $table->decimal('statutory_duties_disbursed', 18, 2)->default(0);
            $table->decimal('port_shipping_disbursed', 18, 2)->default(0);
            $table->decimal('transport_haulage_cost', 18, 2)->default(0);
            $table->decimal('other_third_party_costs', 18, 2)->default(0);
            $table->decimal('total_disbursements_and_direct_costs', 18, 2)->default(0);
            $table->decimal('gross_profit', 18, 2)->default(0);
            $table->decimal('net_margin_percentage', 5, 2)->default(0);
            $table->decimal('customer_advance_received', 18, 2)->default(0);
            $table->decimal('customer_invoiced_total', 18, 2)->default(0);
            $table->decimal('balance_due_from_customer', 18, 2)->default(0);
            $table->boolean('is_financially_reconciled')->default(false);
            $table->unsignedBigInteger('reconciled_by')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();

            $table->foreign('cf_job_id')->references('id')->on('cf_jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cf_job_costings');
        Schema::dropIfExists('cf_proof_of_deliveries');
        Schema::dropIfExists('cf_deliveries');
        Schema::dropIfExists('cf_tracking_events');
        Schema::dropIfExists('cf_transport_orders');
        Schema::dropIfExists('cf_disbursements');
        Schema::dropIfExists('cf_charge_estimates');
        Schema::dropIfExists('cf_customs_events');
        Schema::dropIfExists('cf_declarations');
        Schema::dropIfExists('cf_valuations');
        Schema::dropIfExists('cf_classifications');
        Schema::dropIfExists('cf_permits');
        Schema::dropIfExists('cf_document_checklists');
        Schema::dropIfExists('cf_containers');
        Schema::dropIfExists('cf_cargo_lines');
        Schema::dropIfExists('cf_shipments');
        Schema::dropIfExists('cf_jobs');
    }
};
