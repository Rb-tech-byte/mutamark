<?php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use App\Services\DocumentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentVerificationController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-sales-invoices', except: ['verify', 'apiVerify', 'publicVerifyPortal'])];
    }
    /**
     * Authenticated Verification Ledger Page
     */
    public function index(Request $request): Response
    {
        $query = DocumentVerification::where('created_by', creatorId());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('party_name', 'like', "%{$search}%")
                  ->orWhere('verification_hash', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('document_type', $request->input('type'));
        }

        $verifications = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $metrics = [
            'total_verified' => DocumentVerification::where('created_by',creatorId())->count(),
            'total_volume' => (float) DocumentVerification::where('created_by',creatorId())->sum('total_amount'),
            'invoice_count' => DocumentVerification::where('created_by',creatorId())->where('document_type', 'invoice')->count(),
            'quotation_count' => DocumentVerification::where('created_by',creatorId())->where('document_type', 'quotation')->count(),
            'purchase_count' => DocumentVerification::where('created_by',creatorId())->where('document_type', 'purchase_order')->count(),
            'lipa_namba' => company_setting('lipa_namba_till_number') ?: (company_setting('lipa_namba') ?: ''),
        ];

        return Inertia::render('Verifications/Index', [
            'verifications' => $verifications,
            'metrics' => $metrics,
            'filters' => [
                'search' => $request->input('search', ''),
                'type' => $request->input('type', 'all'),
            ],
        ]);
    }

    /**
     * Public Document Verification Search & Validation Portal
     */
    public function publicVerifyPortal(Request $request): Response
    {
        $hash = trim((string) ($request->input('hash') ?: $request->input('q', '')));
        $documentNumber = trim((string) $request->input('document_number', ''));

        $verification = null;
        $hasSearched = false;

        if (!empty($hash)) {
            $hasSearched = true;
            $verification = DocumentVerificationService::findByHash($hash);
            // Also search by document number if hash lookup wasn't found directly
            if (!$verification) {
                $verification = DocumentVerification::where('document_number', $hash)->first();
                if ($verification) {
                    $hash = $verification->verification_hash;
                }
            }
        } elseif (!empty($documentNumber)) {
            $hasSearched = true;
            $verification = DocumentVerification::where('document_number', $documentNumber)->first();
            if ($verification) {
                $hash = $verification->verification_hash;
            }
        }

        return Inertia::render('Verify/Document', [
            'verification' => $verification,
            'hash' => $hash,
            'isVerified' => $verification !== null,
            'hasSearched' => $hasSearched,
            'searchQuery' => $hash ?: $documentNumber,
            'appName' => config('app.name', 'Mutamark ERP'),
            'companyName' => $verification ? $verification->issuer_name : (company_setting('company_name') ?: 'Mutamark Enterprises Ltd'),
        ]);
    }

    /**
     * Public Verification Page by Hash
     */
    public function verify(Request $request, ?string $hash = null): Response
    {
        $resolvedHash = $hash ?: (string) $request->input('hash', '');
        return $this->publicVerifyPortal($request->merge(['hash' => $resolvedHash]));
    }

    /**
     * Public Verification REST API Endpoint
     */
    public function apiVerify(Request $request, string $hash): JsonResponse
    {
        $verification = DocumentVerificationService::findByHash($hash);

        if (!$verification) {
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'Document verification record not found or invalid cryptographic hash.',
                'hash' => $hash,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'verified' => true,
            'integrity_verified' => (int) $verification->signature_version === 2,
            'verification_mode' => (int) $verification->signature_version === 2 ? 'content_bound' : 'legacy_registry_record',
            'issuer' => $verification->issuer_name,
            'document' => [
                'type' => $verification->document_type,
                'number' => $verification->document_number,
                'status' => $verification->status,
                'issue_date' => $verification->issue_date?->format('Y-m-d'),
                'due_date' => $verification->due_date?->format('Y-m-d'),
                'total_amount' => (float) $verification->total_amount,
                'tax_amount' => (float) $verification->tax_amount,
                'currency' => $verification->currency,
                'party_name' => $verification->party_name,
                'lipa_namba' => $verification->lipa_namba,
                'bank_details' => $verification->bank_details,
                'verified_at' => $verification->verified_at?->toIso8601String(),
            ],
            'security' => [
                'verification_hash' => $verification->verification_hash,
                'engine' => 'Wolinet Cryptographic Document Verification Engine',
                'signed_by' => $verification->signatory_name,
            ],
        ]);
    }
}
