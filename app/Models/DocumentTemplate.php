<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $table = 'document_templates';

    protected $fillable = [
        'template_key',
        'name',
        'document_type',
        'header_title',
        'header_subtitle',
        'header_text',
        'footer_text',
        'terms_and_conditions',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'paper_size',
        'orientation',
        'show_company_logo',
        'show_qr_code',
        'show_digital_seal',
        'show_signature_block',
        'show_bank_details',
        'show_tax_breakdown',
        'show_amount_in_words',
        'watermark_text',
        'is_default',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'show_company_logo' => 'boolean',
        'show_qr_code' => 'boolean',
        'show_digital_seal' => 'boolean',
        'show_signature_block' => 'boolean',
        'show_bank_details' => 'boolean',
        'show_tax_breakdown' => 'boolean',
        'show_amount_in_words' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'document_type_label',
    ];

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'quotation' => 'Quotation (QT)',
            'proforma_invoice' => 'Proforma Invoice (PI)',
            'sales_order' => 'Sales Order (SO)',
            'delivery_note' => 'Delivery Note (DN)',
            'sales_invoice' => 'Tax Invoice (INV)',
            'receipt_voucher' => 'Receipt Voucher (RV)',
            'purchase_requisition' => 'Purchase Requisition (PR)',
            'rfq' => 'Request for Quotation (RFQ)',
            'comparative_statement' => 'Comparative Evaluation Statement',
            'purchase_order' => 'Purchase Order (PO)',
            'goods_received_note' => 'Goods Received Note (GRN)',
            'credit_note' => 'Credit Note (CN)',
            'debit_note' => 'Debit Note (DN)',
            'payslip' => 'Salary Payslip',
            'contract' => 'Commercial Contract',
            default => ucwords(str_replace('_', ' ', $this->document_type)),
        };
    }
}
