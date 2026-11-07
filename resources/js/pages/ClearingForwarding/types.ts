export type ServiceType = 'import' | 'export' | 'transit' | 'domestic';
export type TransportMode = 'sea' | 'air' | 'road' | 'rail';
export type CustomsRegime = 'home_use' | 'transit' | 'warehousing' | 'temporary_import' | 'export' | 're_export';
export type JobStatus =
    | 'draft'
    | 'documents_pending'
    | 'compliance_check'
    | 'ready_for_declaration'
    | 'declaration_submitted'
    | 'assessed'
    | 'duties_paid'
    | 'cargo_released'
    | 'in_transit'
    | 'delivered'
    | 'financially_pending'
    | 'completed'
    | 'cancelled';

export interface UserSummary {
    id: number;
    name: string;
    email: string;
    type?: string;
    mobile_no?: string;
}

export interface CFShipment {
    id: number;
    cf_job_id: number;
    bl_awb_number?: string | null;
    vessel_flight_truck?: string | null;
    voyage_number?: string | null;
    shipping_line_airline?: string | null;
    shipping_agent_id?: number | null;
    origin_country?: string | null;
    port_of_loading?: string | null;
    port_of_discharge?: string | null;
    terminal_icd?: string | null;
    final_destination?: string | null;
    etd?: string | null;
    atd?: string | null;
    eta?: string | null;
    ata?: string | null;
    manifest_rotation_number?: string | null;
}

export interface CFCargoLine {
    id: number;
    cf_job_id: number;
    commodity_description: string;
    package_type: string;
    package_count: number;
    gross_weight_kg: number;
    net_weight_kg?: number | null;
    cbm?: number | null;
    marks_numbers?: string | null;
    hs_code?: string | null;
    customs_value: number;
    currency: string;
    classification?: CFClassification | null;
}

export interface CFContainer {
    id: number;
    cf_job_id: number;
    container_number: string;
    size_type: string;
    seal_number?: string | null;
    shipping_line?: string | null;
    free_days: number;
    discharge_date?: string | null;
    gate_out_date?: string | null;
    demurrage_start_date?: string | null;
    empty_return_deadline?: string | null;
    empty_returned_at?: string | null;
    empty_depot_name?: string | null;
    demurrage_days?: number | null;
    demurrage_amount_tzs?: number | null;
    status: 'at_terminal' | 'cleared_gate_out' | 'in_transit' | 'empty_returned' | 'demurrage_accruing';
    interchange_receipt_number?: string | null;
    job?: CFJob;
}

export interface CFDocumentChecklist {
    id: number;
    cf_job_id: number;
    document_type: string;
    document_name: string;
    is_mandatory: boolean;
    is_original_received: boolean;
    document_reference?: string | null;
    file_path?: string | null;
    verification_status: 'pending' | 'verified' | 'rejected' | 'waived';
    verified_by?: number | null;
    verified_at?: string | null;
    rejection_reason?: string | null;
    verifier?: UserSummary | null;
}

export interface CFPermit {
    id: number;
    cf_job_id: number;
    agency: string;
    permit_type: string;
    application_number?: string | null;
    permit_number?: string | null;
    issue_date?: string | null;
    expiry_date?: string | null;
    status: 'required' | 'applied' | 'under_review' | 'approved' | 'rejected' | 'expired';
    special_conditions?: string | null;
    certificate_file?: string | null;
}

export interface CFClassification {
    id: number;
    cf_job_id: number;
    cf_cargo_line_id?: number | null;
    proposed_hs_code: string;
    approved_hs_code?: string | null;
    tariff_description?: string | null;
    duty_rate_percentage: number;
    vat_rate_percentage: number;
    excise_rate_percentage: number;
    classification_rationale?: string | null;
    wco_general_rule?: string | null;
    reviewed_by?: number | null;
    status: 'draft' | 'reviewed' | 'approved';
    reviewer?: UserSummary | null;
}

export interface CFValuation {
    id: number;
    cf_job_id: number;
    invoice_value_fob: number;
    freight_charges: number;
    insurance_charges: number;
    other_additions: number;
    customs_value_cif_foreign: number;
    currency: string;
    tra_exchange_rate: number;
    customs_value_tzs: number;
    import_duty_tzs: number;
    vat_tzs: number;
    railway_levy_tzs: number;
    customs_processing_fee_tzs: number;
    other_statutory_taxes_tzs: number;
    total_statutory_taxes_tzs: number;
    reviewed_by?: number | null;
    reviewer?: UserSummary | null;
}

export interface CFCustomsEvent {
    id: number;
    cf_declaration_id: number;
    cf_job_id: number;
    event_type: string;
    officer_name?: string | null;
    event_time: string;
    location?: string | null;
    instructions?: string | null;
    findings?: string | null;
    outcome?: string | null;
    attachment_path?: string | null;
    recorded_by?: number | null;
    recorder?: UserSummary | null;
}

export type DeclarationStatus =
    | 'draft'
    | 'ready_for_review'
    | 'internally_approved'
    | 'worksheet_generated'
    | 'tancis_submitted_pending_evidence'
    | 'lodgement_evidence_recorded'
    | 'submitted'
    | 'queried'
    | 'amended'
    | 'under_examination'
    | 'assessed'
    | 'payment_pending'
    | 'paid'
    | 'inspection_pending'
    | 'inspection_completed'
    | 'customs_released'
    | 'released'
    | 'cancelled';

export interface CFDeclaration {
    id: number;
    cf_job_id: number;
    customs_office: string;
    declaration_type: string;
    declaration_number?: string | null;
    tancis_reference?: string | null;
    regime_code?: string | null;
    declarant_user_id?: number | null;
    submission_date?: string | null;
    assessment_number?: string | null;
    assessment_date?: string | null;
    assessed_amount_tzs: number;
    total_tax_tzs?: number;
    payment_control_number?: string | null;
    payment_date?: string | null;
    release_order_number?: string | null;
    release_date?: string | null;
    status: DeclarationStatus | string;
    customs_channel?: string | null;
    declarant?: UserSummary | null;
    events?: CFCustomsEvent[];
    job?: CFJob;

    // TANCIS Manual Addendum & Evidence Tracking (Section 18 & 19)
    worksheet_version?: number;
    worksheet_generated_at?: string | null;
    worksheet_generated_by?: number | null;
    worksheet_generator?: UserSummary | null;
    manual_entry_source?: string;
    external_submission_time?: string | null;
    lodgement_evidence_path?: string | null;
    import_duty_assessed_tzs?: number;
    vat_assessed_tzs?: number;
    rdl_assessed_tzs?: number;
    cpf_assessed_tzs?: number;
    excise_assessed_tzs?: number;
    other_taxes_assessed_tzs?: number;
    penalties_tzs?: number;
    assessment_document_path?: string | null;
    is_assessment_verified?: boolean;
    assessment_verified_by?: number | null;
    assessment_verified_at?: string | null;
    assessment_verifier?: UserSummary | null;
    payment_receipt_path?: string | null;
    is_payment_verified?: boolean;
    payment_verified_by?: number | null;
    payment_verified_at?: string | null;
    release_order_path?: string | null;
    is_release_verified?: boolean;
    release_verified_by?: number | null;
    release_verified_at?: string | null;
    release_verifier?: UserSummary | null;
}

export interface CFChargeEstimate {
    id: number;
    cf_job_id: number;
    charge_category: string;
    charge_name: string;
    is_disbursement: boolean;
    estimated_amount: number;
    currency: string;
    exchange_rate: number;
    amount_tzs: number;
    notes?: string | null;
}

export interface CFDisbursement {
    id: number;
    cf_job_id: number;
    payee_name: string;
    payee_vendor_id?: number | null;
    charge_category: string;
    description: string;
    payment_method: string;
    disbursed_amount: number;
    currency: string;
    amount_tzs: number;
    payment_reference?: string | null;
    receipt_voucher_number?: string | null;
    receipt_file_path?: string | null;
    payment_date: string;
    is_reconciled: boolean;
    authorized_by?: number | null;
    authorizer?: UserSummary | null;
}

export interface CFTrackingEvent {
    id: number;
    cf_job_id: number;
    cf_transport_order_id?: number | null;
    milestone_code: string;
    location_name: string;
    latitude?: number | null;
    longitude?: number | null;
    event_time: string;
    remarks?: string | null;
    photo_evidence_path?: string | null;
    is_customer_visible: boolean;
    recorded_by?: number | null;
    recorder?: UserSummary | null;
}

export interface CFProofOfDelivery {
    id: number;
    cf_delivery_id: number;
    cf_job_id: number;
    recipient_name: string;
    recipient_title?: string | null;
    recipient_id_type: string;
    recipient_id_number?: string | null;
    recipient_phone?: string | null;
    signature_data?: string | null;
    signed_at: string;
    photo_evidence_paths?: string[] | null;
    official_stamp_received: boolean;
    verification_hash?: string | null;
    verified_by?: number | null;
    verifier?: UserSummary | null;
}

export interface CFDelivery {
    id: number;
    cf_job_id: number;
    cf_transport_order_id?: number | null;
    delivery_number: string;
    delivery_date: string;
    delivery_address: string;
    packages_delivered: number;
    cargo_condition: 'sound_and_intact' | 'shortage' | 'damaged' | 'seal_broken' | 'disputed';
    condition_notes?: string | null;
    delivery_note_file_path?: string | null;
    status: 'scheduled' | 'delivered_pending_pod' | 'pod_confirmed' | 'discrepancy_reported';
    proof_of_delivery?: CFProofOfDelivery | null;
}

export interface CFTransportOrder {
    id: number;
    cf_job_id: number;
    order_number: string;
    transporter_type: 'internal_fleet' | 'third_party_carrier';
    transporter_vendor_id?: number | null;
    vehicle_id?: number | null;
    driver_id?: number | null;
    trailer_number?: string | null;
    carrier_name?: string | null;
    driver_name: string;
    driver_phone?: string | null;
    driver_license?: string | null;
    vehicle_plate: string;
    pickup_location: string;
    delivery_destination: string;
    route_corridor?: string | null;
    cargo_summary?: string | null;
    agreed_freight_cost: number;
    currency: string;
    dispatch_date?: string | null;
    expected_delivery_date?: string | null;
    actual_delivery_date?: string | null;
    status: 'draft' | 'dispatched' | 'in_transit' | 'at_border' | 'delivered' | 'cancelled';
    waybill_file_path?: string | null;
    tracking_events?: CFTrackingEvent[];
    deliveries?: CFDelivery[];
    job?: CFJob;
}

export interface CFJobCosting {
    id: number;
    cf_job_id: number;
    currency: string;
    agency_fee_revenue: number;
    documentation_fee_revenue: number;
    transport_markup_revenue: number;
    total_service_revenue: number;
    statutory_duties_disbursed: number;
    port_shipping_disbursed: number;
    transport_haulage_cost: number;
    other_third_party_costs: number;
    total_disbursements_and_direct_costs: number;
    gross_profit: number;
    net_margin_percentage: number;
    customer_advance_received: number;
    customer_invoiced_total: number;
    balance_due_from_customer: number;
    is_financially_reconciled: boolean;
    reconciled_by?: number | null;
    reconciled_at?: string | null;
    reconciler?: UserSummary | null;
}

export interface CFJob {
    id: number;
    job_number: string;
    customer_id: number;
    consignee_name?: string | null;
    consignor_name?: string | null;
    service_type: ServiceType;
    transport_mode: TransportMode;
    customs_regime: CustomsRegime;
    status: JobStatus;
    priority: 'normal' | 'urgent' | 'critical';
    case_owner_id?: number | null;
    declarant_id?: number | null;
    branch_id?: number | null;
    workspace_id: number;
    created_by?: number | null;
    opened_at?: string | null;
    closed_at?: string | null;
    customer_reference?: string | null;
    notes?: string | null;
    created_at: string;
    updated_at: string;

    // Relationships
    customer?: UserSummary;
    case_owner?: UserSummary;
    declarant?: UserSummary;
    shipment?: CFShipment;
    cargo_lines?: CFCargoLine[];
    containers?: CFContainer[];
    documents?: CFDocumentChecklist[];
    permits?: CFPermit[];
    classifications?: CFClassification[];
    valuation?: CFValuation;
    declarations?: CFDeclaration[];
    charge_estimates?: CFChargeEstimate[];
    disbursements?: CFDisbursement[];
    transport_orders?: CFTransportOrder[];
    tracking_events?: CFTrackingEvent[];
    deliveries?: CFDelivery[];
    job_costing?: CFJobCosting;
    jobCosting?: CFJobCosting;
}

