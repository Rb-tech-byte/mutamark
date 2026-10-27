<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Services\DocumentTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DocumentTemplateController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-settings')];
    }
    /**
     * Get authenticated creator/company ID safely.
     */
    protected function getCompanyId(): int
    {
        if (function_exists('creatorId')) {
            $cid = creatorId();
            if ($cid) {
                return (int) $cid;
            }
        }
        return Auth::check() ? Auth::id() : 1;
    }

    /**
     * Display a listing of Document Templates.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        // Seed defaults if company has none
        if (DocumentTemplate::where('created_by', $companyId)->count() === 0) {
            DocumentTemplateService::seedDefaultTemplates($companyId);
        }

        $query = DocumentTemplate::where('created_by', $companyId);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('header_title', 'like', "%{$term}%")
                    ->orWhere('document_type', 'like', "%{$term}%");
            });
        }

        if ($request->filled('document_type') && $request->document_type !== 'all') {
            $query->where('document_type', $request->document_type);
        }

        $templates = $query->orderBy('document_type')->orderBy('is_default', 'desc')->get();

        // Document type stats
        $allTemplates = DocumentTemplate::where('created_by', $companyId)->get();
        $totalCount = $allTemplates->count();
        $salesCount = $allTemplates->whereIn('document_type', ['quotation', 'proforma_invoice', 'sales_order', 'delivery_note', 'sales_invoice', 'receipt_voucher'])->count();
        $procurementCount = $allTemplates->whereIn('document_type', ['purchase_requisition', 'rfq', 'comparative_statement', 'purchase_order', 'goods_received_note'])->count();
        $financialCount = $allTemplates->whereIn('document_type', ['credit_note', 'debit_note', 'payslip', 'contract'])->count();

        return Inertia::render('settings/DocumentTemplates/Index', [
            'templates' => $templates,
            'kpi' => [
                'total_templates' => $totalCount,
                'sales_count' => $salesCount,
                'procurement_count' => $procurementCount,
                'financial_count' => $financialCount,
            ],
            'filters' => $request->only(['search', 'document_type']),
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|string',
            'header_title' => 'required|string|max:255',
            'header_subtitle' => 'nullable|string|max:255',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'font_family' => 'nullable|string',
            'paper_size' => 'nullable|string',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'show_company_logo' => 'boolean',
            'show_qr_code' => 'boolean',
            'show_digital_seal' => 'boolean',
            'show_signature_block' => 'boolean',
            'show_bank_details' => 'boolean',
            'show_tax_breakdown' => 'boolean',
            'show_amount_in_words' => 'boolean',
            'watermark_text' => 'nullable|string|max:50',
            'is_default' => 'boolean',
        ]);

        $companyId = $this->getCompanyId();
        $userId = Auth::id() ?: 1;

        $tpl = DocumentTemplateService::createTemplate($validated, $userId, $companyId);

        return redirect()->back()->with('success', "Template '{$tpl->name}' created successfully.");
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, DocumentTemplate $template)
    {
        \App\Services\CompanyAccess::check($template);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header_title' => 'required|string|max:255',
            'header_subtitle' => 'nullable|string|max:255',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'font_family' => 'nullable|string',
            'paper_size' => 'nullable|string',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'show_company_logo' => 'boolean',
            'show_qr_code' => 'boolean',
            'show_digital_seal' => 'boolean',
            'show_signature_block' => 'boolean',
            'show_bank_details' => 'boolean',
            'show_tax_breakdown' => 'boolean',
            'show_amount_in_words' => 'boolean',
            'watermark_text' => 'nullable|string|max:50',
            'is_default' => 'boolean',
        ]);

        $updated = DocumentTemplateService::updateTemplate($template, $validated);

        return redirect()->back()->with('success', "Template '{$updated->name}' updated successfully.");
    }

    /**
     * Set a template as default.
     */
    public function setDefault(DocumentTemplate $template)
    {
        \App\Services\CompanyAccess::check($template);
        DocumentTemplateService::setAsDefault($template);

        return redirect()->back()->with('success', "Template '{$template->name}' set as default for {$template->document_type_label}.");
    }

    /**
     * Delete a template.
     */
    public function destroy(DocumentTemplate $template)
    {
        \App\Services\CompanyAccess::check($template);
        if ($template->is_default) {
            $other = DocumentTemplate::where('created_by', $template->created_by)
                ->where('document_type', $template->document_type)
                ->where('id', '!=', $template->id)
                ->first();
            if ($other) {
                $other->update(['is_default' => true]);
            }
        }

        DocumentTemplateService::deleteTemplate($template);

        return redirect()->back()->with('success', "Template '{$template->name}' deleted.");
    }
}
