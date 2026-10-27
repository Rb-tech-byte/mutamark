<?php

namespace App\Services;

use App\Models\DocumentVerification;
use Illuminate\Support\Str;

class DocumentVerificationService
{
    /**
     * Generate or retrieve a verified record for any Mutamark ERP official document.
     */
    public static function getOrCreateVerification($document, string $documentType): DocumentVerification
    {
        $supported = in_array(get_class($document), [\App\Models\SalesInvoice::class,\App\Models\PurchaseInvoice::class,\App\Models\SalesProposal::class,\App\Models\AssetDisposal::class,\Workdo\Account\Models\Expense::class,\App\Models\Document::class],true);
        if ($supported) {
            $digest = hash('sha256',self::canonical($document));
            $existing = DocumentVerification::where('document_type',$documentType)->where('document_id',$document->id)->where('content_hash',$digest)->whereNull('revoked_at')->first();
            if ($existing && self::findByHash($existing->verification_hash)) return $existing;
        }
        $docId = $document->id ?? null;
        $docNumber = self::resolveDocumentNumber($document, $documentType);
        $totalAmount = (float) ($document->total_amount ?? $document->amount ?? $document->total ?? 0);
        $taxAmount = (float) ($document->tax_amount ?? $document->tax ?? 0);
        $currency = company_setting('defaultCurrency', $document->created_by ?? null) ?: 'TZS';
        $issueDate = $document->invoice_date ?? $document->proposal_date ?? $document->date ?? $document->created_at ?? now();
        $dueDate = $document->due_date ?? null;
        $status = strtolower($document->status ?? 'verified');
        $partyName = self::resolvePartyName($document);
        $createdBy = $document->created_by ?? creatorId();

        // Deterministic cryptographic hash
        $rawHashString = sprintf(
            'MUTAMARK:%s:%s:%.2f:%s:%s',
            $documentType,
            $docNumber,
            $totalAmount,
            substr((string) $issueDate, 0, 10),
            config('app.key')
        );
        $verificationHash = hash('sha256', $rawHashString);
        $verificationUrl = url('/verify/document/' . $verificationHash);

        $lipaNamba = company_setting('lipa_namba_till_number', $createdBy) ?: (company_setting('lipa_namba', $createdBy) ?: '');
        $tinNumber = company_setting('tin_number', $createdBy) ?: '100-200-300';
        $vrnNumber = company_setting('vrn_number', $createdBy) ?: '40-001234-Z';
        
        $bankName = company_setting('bank_name', $createdBy) ?: 'CRDB Bank / NMB Bank';
        $accountName = company_setting('bank_account_name', $createdBy) ?: 'MUTAMARK ENTERPRISES';
        $accountNumber = company_setting('bank_account_number', $createdBy) ?: '0150284758200';
        $bankDetails = "Bank: {$bankName} | Account Name: {$accountName} | Account No: {$accountNumber}";

        $verification = DocumentVerification::updateOrCreate(
            ['verification_hash' => $verificationHash],
            [
                'document_type' => $documentType,
                'document_id' => $docId,
                'document_number' => $docNumber,
                'qr_payload' => $verificationUrl,
                'issuer_name' => company_setting('company_name', $createdBy) ?: 'Mutamark Enterprises',
                'party_name' => $partyName,
                'party_email' => $document->customer->email ?? $document->vendor->email ?? null,
                'party_phone' => $document->customer->contact ?? $document->vendor->contact ?? null,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'currency' => $currency,
                'issue_date' => $issueDate ? date('Y-m-d', strtotime((string)$issueDate)) : null,
                'due_date' => $dueDate ? date('Y-m-d', strtotime((string)$dueDate)) : null,
                'status' => $status,
                'signatory_name' => 'Managing Director / Head of Finance',
                'signatory_title' => 'Mutamark Enterprises Authorized Signatory',
                'lipa_namba' => $lipaNamba,
                'tin_number' => $tinNumber,
                'vrn_number' => $vrnNumber,
                'bank_details' => $bankDetails,
                'verified_at' => now(),
                'created_by' => $createdBy,
            ]
        );

        if ($supported) {
            $verification->update(['signatory_name'=>auth()->user()?->name ?? 'Mutamark ERP','signatory_title'=>'System document seal']);
            return self::bindSignature($verification,$document,auth()->id());
        }
        return $verification;
    }

    public static function findByHash(string $hash): ?DocumentVerification
    {
        $record = DocumentVerification::where('verification_hash', $hash)->whereNull('revoked_at')->first();
        if (!$record) return null;
        if ((int) $record->signature_version !== 2) return $record; // Legacy records are labelled as registry-only by the UI.
        $allowed = [\App\Models\SalesInvoice::class, \App\Models\PurchaseInvoice::class, \App\Models\SalesProposal::class, \App\Models\AssetDisposal::class, \Workdo\Account\Models\Expense::class, \App\Models\Document::class];
        if (!in_array($record->source_model, $allowed, true)) return null;
        $source = $record->source_model::find($record->source_id);
        if (!$source || !hash_equals($record->content_hash, hash('sha256', self::canonical($source)))) return null;
        $payload = $record->signed_payload;
        if (($payload['content_hash'] ?? null) !== $record->content_hash || (int) ($payload['document_id'] ?? 0) !== (int) $record->source_id || ($payload['document_model'] ?? null) !== $record->source_model || ($payload['signer_id'] ?? null) != $record->signer_id) return null;
        if (!$payload || !hash_equals($record->verification_hash, hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), config('app.key')))) return null;
        return $record;
    }

    public static function bindSignature(DocumentVerification $record, \Illuminate\Database\Eloquent\Model $document, ?int $signerId): DocumentVerification
    {
        $digest = hash('sha256', self::canonical($document));
        $payload = ['content_hash' => $digest, 'document_id' => $document->id, 'document_model' => get_class($document), 'signed_at' => now()->toIso8601String(), 'signer_id' => $signerId];
        $hash = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), config('app.key'));
        $record->update(['signature_version' => 2, 'content_hash' => $digest, 'signed_payload' => $payload, 'signer_id' => $signerId, 'source_model' => get_class($document), 'source_id' => $document->id, 'verification_hash' => $hash, 'qr_payload' => url('/verify/document/'.$hash)]);
        return $record->fresh();
    }

    private static function canonical(\Illuminate\Database\Eloquent\Model $document): string
    {
        $source = $document->fresh();
        $ignore = ['updated_at','status','paid_amount','balance_amount','verification_hash','verification_code','signed_at','signed_by','approved_by','approved_at','posted_at'];
        $normalize = function ($values) use ($ignore) {
            $values = array_diff_key($values, array_flip($ignore)); ksort($values);
            return array_map(fn ($value) => $value === null ? null : (string) $value, $values);
        };
        $payload = ['document' => $normalize($source->getRawOriginal())];
        if (method_exists($source, 'items')) $payload['items'] = $source->items()->orderBy('id')->get()->map(fn ($item) => $normalize($item->getRawOriginal()))->all();
        if ($source instanceof \App\Models\Document) {
            $path = \Illuminate\Support\Facades\Storage::disk('local')->path($source->file_path);
            $payload['file_sha256'] = is_file($path) ? hash_file('sha256', $path) : null;
            $payload['version'] = $source->versions()->max('version_number');
        }
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private static function resolveDocumentNumber($document, string $type): string
    {
        if (!empty($document->invoice_number)) return $document->invoice_number;
        if (!empty($document->proposal_number)) return $document->proposal_number;
        if (!empty($document->payment_number)) return $document->payment_number;
        if (!empty($document->credit_note_number)) return $document->credit_note_number;
        if (!empty($document->debit_note_number)) return $document->debit_note_number;
        if (!empty($document->journal_number)) return $document->journal_number;
        if (!empty($document->return_number)) return $document->return_number;
        return strtoupper($type) . '-' . ($document->id ?? '1');
    }

    private static function resolvePartyName($document): ?string
    {
        if (!empty($document->customer->name)) return $document->customer->name;
        if (!empty($document->vendor->name)) return $document->vendor->name;
        if (!empty($document->user->name)) return $document->user->name;
        if (!empty($document->party_name)) return $document->party_name;
        return null;
    }
}
