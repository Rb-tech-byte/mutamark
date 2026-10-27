<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentTemplateService
{
    /**
     * Seed baseline default templates across SRS Module 25 document types.
     */
    public static function seedDefaultTemplates(int $companyId): void
    {
        $defaults = [
            [
                'document_type' => 'sales_invoice',
                'name' => 'Corporate Tax Invoice (Standard)',
                'header_title' => 'TAX INVOICE',
                'header_subtitle' => 'Authorized VAT Commercial Invoice',
                'terms_and_conditions' => "1. Payment is strictly due within 30 days from invoice date.\n2. Overdue balances accrue late interest of 2% per month.\n3. Goods sold remain the property of Mutamark Enterprises until paid in full.",
                'footer_text' => 'Thank you for your business. For billing queries, contact accounts@mutamark.co.tz',
                'primary_color' => '#4f46e5',
                'secondary_color' => '#0f172a',
                'accent_color' => '#10b981',
            ],
            [
                'document_type' => 'quotation',
                'name' => 'Commercial Sales Quotation',
                'header_title' => 'PROFORMA & SALES QUOTATION',
                'header_subtitle' => 'Formal Commercial Offer',
                'terms_and_conditions' => "1. Quotation valid for 30 calendar days from issue date.\n2. Prices are in Tanzanian Shillings (TZS) inclusive of standard 18% VAT.\n3. Delivery within Dar es Salaam: 2-3 business days upon confirmation.",
                'footer_text' => 'We look forward to serving your enterprise requirements.',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'accent_color' => '#f59e0b',
            ],
            [
                'document_type' => 'delivery_note',
                'name' => 'Fulfillment & Dispatch Delivery Note',
                'header_title' => 'GOODS DELIVERY & DISPATCH NOTE',
                'header_subtitle' => 'Proof of Delivery & Warehouse Dispatch',
                'terms_and_conditions' => "1. Customer must inspect delivered items before signing.\n2. Discrepancies or damages must be reported within 24 hours of receipt.",
                'footer_text' => 'Warehouse Dispatch & Logistics Division - Mutamark Enterprises',
                'primary_color' => '#0284c7',
                'secondary_color' => '#0f172a',
                'accent_color' => '#14b8a6',
            ],
            [
                'document_type' => 'purchase_requisition',
                'name' => 'Departmental Purchase Requisition Slip',
                'header_title' => 'PURCHASE REQUISITION VOUCHER',
                'header_subtitle' => 'Internal Material & Expenditure Request',
                'terms_and_conditions' => "1. Expenditure justified against approved quarterly operational budget.\n2. Procurement department will issue RFQ or commit PO upon approval.",
                'footer_text' => 'Procurement & Supply Chain Management Office',
                'primary_color' => '#7c3aed',
                'secondary_color' => '#1e1b4b',
                'accent_color' => '#8b5cf6',
            ],
            [
                'document_type' => 'rfq',
                'name' => 'Formal Invitation to Tender (RFQ)',
                'header_title' => 'REQUEST FOR QUOTATION (RFQ)',
                'header_subtitle' => 'Competitive Supplier Bidding Tender Document',
                'terms_and_conditions' => "1. Bids must be submitted before the specified closing deadline.\n2. Mutamark reserves the right to accept or reject any quotation without assigning reasons.",
                'footer_text' => 'Tender Evaluation Committee - Supply Chain Division',
                'primary_color' => '#4338ca',
                'secondary_color' => '#1e293b',
                'accent_color' => '#06b6d4',
            ],
            [
                'document_type' => 'comparative_statement',
                'name' => 'Comparative Evaluation Analysis Statement',
                'header_title' => 'TENDER COMPARATIVE EVALUATION STATEMENT',
                'header_subtitle' => 'Side-by-Side Supplier Quotation Evaluation',
                'terms_and_conditions' => "1. Lowest responsive evaluated bid selected in compliance with procurement regulations.",
                'footer_text' => 'Chief Financial Officer & Procurement Director Sign-Off',
                'primary_color' => '#059669',
                'secondary_color' => '#064e3b',
                'accent_color' => '#10b981',
            ],
            [
                'document_type' => 'purchase_order',
                'name' => 'Official Purchase Order (PO)',
                'header_title' => 'OFFICIAL PURCHASE ORDER',
                'header_subtitle' => 'Binding Supplier Procurement Contract',
                'terms_and_conditions' => "1. Acceptance of this PO binds supplier to delivery timelines and unit prices.\n2. GRN inspection required prior to invoice processing.",
                'footer_text' => 'Supply Chain Directorate - Mutamark Enterprises',
                'primary_color' => '#0d9488',
                'secondary_color' => '#134e4a',
                'accent_color' => '#f59e0b',
            ],
            [
                'document_type' => 'goods_received_note',
                'name' => 'Warehouse Goods Received Note (GRN)',
                'header_title' => 'GOODS RECEIVED NOTE (GRN)',
                'header_subtitle' => 'Inward Stock Receipt & Quality Inspection Voucher',
                'terms_and_conditions' => "1. Goods inspected and accepted into warehouse stock register.\n2. Basis for Accounts Payable 3-way matching clearance.",
                'footer_text' => 'Storekeeper & Quality Assurance Department',
                'primary_color' => '#0891b2',
                'secondary_color' => '#164e63',
                'accent_color' => '#10b981',
            ],
            [
                'document_type' => 'receipt_voucher',
                'name' => 'Customer Official Payment Receipt',
                'header_title' => 'OFFICIAL PAYMENT RECEIPT',
                'header_subtitle' => 'Payment Acknowledgment Voucher',
                'terms_and_conditions' => "1. Payment received and credited to customer account ledger.\n2. Electronic receipt verified by cryptographic audit hash.",
                'footer_text' => 'Treasury & Accounts Receivable Office',
                'primary_color' => '#16a34a',
                'secondary_color' => '#14532d',
                'accent_color' => '#22c55e',
            ],
        ];

        foreach ($defaults as $def) {
            $key = "tpl_{$def['document_type']}_default";
            DocumentTemplate::firstOrCreate(
                ['template_key' => $key, 'created_by' => $companyId],
                [
                    'name' => $def['name'],
                    'document_type' => $def['document_type'],
                    'header_title' => $def['header_title'],
                    'header_subtitle' => $def['header_subtitle'],
                    'terms_and_conditions' => $def['terms_and_conditions'],
                    'footer_text' => $def['footer_text'],
                    'primary_color' => $def['primary_color'],
                    'secondary_color' => $def['secondary_color'],
                    'accent_color' => $def['accent_color'],
                    'is_default' => true,
                    'creator_id' => $companyId,
                ]
            );
        }
    }

    /**
     * Create a new custom template.
     */
    public static function createTemplate(array $data, int $userId, int $companyId): DocumentTemplate
    {
        return DB::transaction(function () use ($data, $userId, $companyId) {
            $key = 'tpl_' . $data['document_type'] . '_' . Str::random(8);

            if (!empty($data['is_default'])) {
                DocumentTemplate::where('created_by', $companyId)
                    ->where('document_type', $data['document_type'])
                    ->update(['is_default' => false]);
            }

            return DocumentTemplate::create([
                'template_key' => $key,
                'name' => $data['name'],
                'document_type' => $data['document_type'],
                'header_title' => $data['header_title'],
                'header_subtitle' => $data['header_subtitle'] ?? null,
                'header_text' => $data['header_text'] ?? null,
                'footer_text' => $data['footer_text'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'primary_color' => $data['primary_color'] ?? '#4f46e5',
                'secondary_color' => $data['secondary_color'] ?? '#0f172a',
                'accent_color' => $data['accent_color'] ?? '#10b981',
                'font_family' => $data['font_family'] ?? 'Inter',
                'paper_size' => $data['paper_size'] ?? 'A4',
                'orientation' => $data['orientation'] ?? 'portrait',
                'show_company_logo' => $data['show_company_logo'] ?? true,
                'show_qr_code' => $data['show_qr_code'] ?? true,
                'show_digital_seal' => $data['show_digital_seal'] ?? true,
                'show_signature_block' => $data['show_signature_block'] ?? true,
                'show_bank_details' => $data['show_bank_details'] ?? true,
                'show_tax_breakdown' => $data['show_tax_breakdown'] ?? true,
                'show_amount_in_words' => $data['show_amount_in_words'] ?? true,
                'watermark_text' => $data['watermark_text'] ?? null,
                'is_default' => !empty($data['is_default']),
                'creator_id' => $userId,
                'created_by' => $companyId,
            ]);
        });
    }

    /**
     * Update an existing template.
     */
    public static function updateTemplate(DocumentTemplate $template, array $data): DocumentTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            if (!empty($data['is_default']) && !$template->is_default) {
                DocumentTemplate::where('created_by', $template->created_by)
                    ->where('document_type', $template->document_type)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }

            $template->update($data);
            return $template->fresh();
        });
    }

    /**
     * Set a template as default for its document type.
     */
    public static function setAsDefault(DocumentTemplate $template): DocumentTemplate
    {
        return DB::transaction(function () use ($template) {
            DocumentTemplate::where('created_by', $template->created_by)
                ->where('document_type', $template->document_type)
                ->update(['is_default' => false]);

            $template->update(['is_default' => true]);
            return $template->fresh();
        });
    }

    /**
     * Delete template.
     */
    public static function deleteTemplate(DocumentTemplate $template): bool
    {
        return $template->delete();
    }
}
