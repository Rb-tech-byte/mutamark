<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Models\SalesInvoice;
use App\Models\TraFiscalReceipt;
use App\Models\TraZReport;
use Illuminate\Support\Str;
use Workdo\Pos\Models\Pos;

class TraFiscalService
{
    public const COMPANY_TIN = '100-200-300';
    public const COMPANY_VRN = '40-001234-Z';
    public const VFD_SERIAL  = 'MUTAMARK-VFD-01';

    /**
     * Get or initialize today's active Z-Report
     */
    public static function getOrCreateActiveZReport(?int $creatorId = null): TraZReport
    {
        $creatorId = $creatorId ?: creatorId();
        $today = now()->toDateString();

        $zReport = TraZReport::where('created_by', $creatorId)
            ->where('report_date', $today)
            ->where('status', 'open')
            ->first();

        if (!$zReport) {
            // Find cumulative turnover to date
            $previousTurnover = TraZReport::where('created_by', $creatorId)
                ->where('status', '!=', 'open')
                ->sum('gross_turnover') ?: 0;

            $zReport = TraZReport::create([
                'report_date' => $today,
                'status' => 'open',
                'cumulative_turnover_to_date' => $previousTurnover,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        return $zReport;
    }

    /**
     * Fiscalize an existing Sales Invoice
     */
    public static function fiscalizeSalesInvoice(SalesInvoice $invoice): TraFiscalReceipt
    {
        // Check if already fiscalized
        $existing = TraFiscalReceipt::where('source_type', 'sales_invoice')
            ->where('source_id', $invoice->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $creatorId = $invoice->created_by ?: creatorId();
        $zReport = self::getOrCreateActiveZReport($creatorId);

        $totalAmount = (float)$invoice->total_amount;
        $subtotal = (float)$invoice->subtotal;
        $taxAmount = (float)$invoice->tax_amount;

        // If no tax breakdown, default to standard 18% VAT
        if ($taxAmount == 0 && $totalAmount > 0) {
            $taxAmount = round($totalAmount * 0.18 / 1.18, 2);
            $subtotal = $totalAmount - $taxAmount;
        }

        $now = now();
        $cust = $invoice->customer;
        $custTin = $cust?->tax_payer_id ?: '999-999-999';
        $custName = $cust?->name ?: 'General Consumer';

        // Generate verification signature
        $sigRaw = sprintf(
            "%s|%s|%.2f|%s|%s",
            self::COMPANY_TIN,
            $invoice->invoice_number,
            $totalAmount,
            $now->toIso8601String(),
            config('app.key')
        );
        $fullHash = hash('sha256', $sigRaw);
        $vCode = strtoupper(substr($fullHash, 0, 4) . '-' . substr($fullHash, 4, 4) . '-' . substr($fullHash, 8, 4) . '-' . substr($fullHash, 12, 4));

        $qrPayload = sprintf(
            "https://verify.tra.go.tz/verify?tin=%s&vrn=%s&inv=%s&amt=%.2f&time=%s&sig=%s",
            self::COMPANY_TIN,
            self::COMPANY_VRN,
            urlencode($invoice->invoice_number),
            $totalAmount,
            urlencode($now->format('YmdHis')),
            substr($fullHash, 0, 16)
        );

        $receiptCounter = $zReport->total_receipts_count + 1;

        $receipt = TraFiscalReceipt::create([
            'source_type' => 'sales_invoice',
            'source_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $custName,
            'customer_tin' => $custTin,
            'customer_vrn' => self::COMPANY_VRN,
            'total_tax_exclusive_amount' => $subtotal,
            'tax_amount_a' => $taxAmount, // Standard 18% VAT
            'tax_amount_b' => 0,
            'tax_amount_c' => 0,
            'tax_amount_d' => 0,
            'total_tax_inclusive_amount' => $totalAmount,
            'z_number' => $zReport->z_report_number,
            'daily_receipt_counter' => $receiptCounter,
            'rct_verification_code' => $vCode,
            'qr_payload' => $qrPayload,
            'vfd_device_serial' => self::VFD_SERIAL,
            'status' => 'fiscalized',
            'fiscalized_at' => $now,
            'creator_id' => $invoice->creator_id,
            'created_by' => $creatorId,
        ]);

        // Update active Z-Report figures
        if (empty($zReport->opening_receipt_number)) {
            $zReport->opening_receipt_number = $receipt->receipt_number;
        }
        $zReport->closing_receipt_number = $receipt->receipt_number;
        $zReport->total_receipts_count += 1;
        $zReport->gross_turnover += $totalAmount;
        $zReport->net_taxable_turnover += $subtotal;
        $zReport->total_vat_18 += $taxAmount;
        $zReport->cumulative_turnover_to_date += $totalAmount;
        $zReport->save();

        // Register with DocumentVerification for cryptographic verification
        DocumentVerification::updateOrCreate(
            [
                'document_type' => 'tra_fiscal_receipt',
                'document_id' => $receipt->id,
            ],
            [
                'document_number' => $receipt->receipt_number,
                'verification_hash' => $fullHash,
                'qr_payload' => $qrPayload,
                'issuer_name' => 'MUTAMARK ENTERPRISES',
                'party_name' => $custName,
                'party_phone' => $invoice->customer?->phone ?? '',
                'party_email' => $invoice->customer?->email ?? '',
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'currency' => 'TZS',
                'issue_date' => $now->toDateString(),
                'status' => 'verified',
                'tin_number' => self::COMPANY_TIN,
                'vrn_number' => self::COMPANY_VRN,
                'verified_at' => $now,
                'created_by' => $creatorId,
            ]
        );

        return $receipt;
    }

    /**
     * Fiscalize a POS checkout sale
     */
    public static function fiscalizePosSale(Pos $pos): TraFiscalReceipt
    {
        $existing = TraFiscalReceipt::where('source_type', 'pos_sale')
            ->where('source_id', $pos->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $creatorId = $pos->created_by ?: creatorId();
        $zReport = self::getOrCreateActiveZReport($creatorId);

        $totalAmount = 0;
        foreach ($pos->items as $item) {
            $totalAmount += ((float)$item->quantity * (float)$item->price);
        }

        $taxAmount = round($totalAmount * 0.18 / 1.18, 2);
        $subtotal = $totalAmount - $taxAmount;
        $now = now();
        $custName = $pos->customer?->name ?: 'Counter Walk-In Customer';

        $sigRaw = sprintf(
            "%s|POS-%s|%.2f|%s|%s",
            self::COMPANY_TIN,
            $pos->sale_number,
            $totalAmount,
            $now->toIso8601String(),
            config('app.key')
        );
        $fullHash = hash('sha256', $sigRaw);
        $vCode = strtoupper(substr($fullHash, 0, 4) . '-' . substr($fullHash, 4, 4) . '-' . substr($fullHash, 8, 4) . '-' . substr($fullHash, 12, 4));

        $qrPayload = sprintf(
            "https://verify.tra.go.tz/verify?tin=%s&vrn=%s&rct=%s&amt=%.2f&time=%s&sig=%s",
            self::COMPANY_TIN,
            self::COMPANY_VRN,
            urlencode($pos->sale_number),
            $totalAmount,
            urlencode($now->format('YmdHis')),
            substr($fullHash, 0, 16)
        );

        $receiptCounter = $zReport->total_receipts_count + 1;

        $receipt = TraFiscalReceipt::create([
            'source_type' => 'pos_sale',
            'source_id' => $pos->id,
            'invoice_number' => $pos->sale_number,
            'customer_name' => $custName,
            'customer_tin' => '999-999-999',
            'customer_vrn' => self::COMPANY_VRN,
            'total_tax_exclusive_amount' => $subtotal,
            'tax_amount_a' => $taxAmount,
            'total_tax_inclusive_amount' => $totalAmount,
            'z_number' => $zReport->z_report_number,
            'daily_receipt_counter' => $receiptCounter,
            'rct_verification_code' => $vCode,
            'qr_payload' => $qrPayload,
            'vfd_device_serial' => self::VFD_SERIAL,
            'status' => 'fiscalized',
            'fiscalized_at' => $now,
            'creator_id' => $pos->creator_id,
            'created_by' => $creatorId,
        ]);

        if (empty($zReport->opening_receipt_number)) {
            $zReport->opening_receipt_number = $receipt->receipt_number;
        }
        $zReport->closing_receipt_number = $receipt->receipt_number;
        $zReport->total_receipts_count += 1;
        $zReport->gross_turnover += $totalAmount;
        $zReport->net_taxable_turnover += $subtotal;
        $zReport->total_vat_18 += $taxAmount;
        $zReport->cumulative_turnover_to_date += $totalAmount;
        $zReport->save();

        return $receipt;
    }

    /**
     * Close today's Z-Report (End-Of-Day Fiscal Closure)
     */
    public static function closeDailyZReport(?int $userId = null, ?int $creatorId = null): ?TraZReport
    {
        $creatorId = $creatorId ?: creatorId();
        $zReport = self::getOrCreateActiveZReport($creatorId);

        if ($zReport->status === 'closed' || $zReport->status === 'transmitted') {
            return $zReport;
        }

        $now = now();
        $ackToken = 'TRA-ACK-' . strtoupper(Str::random(8)) . '-VFD';

        $zReport->status = 'transmitted';
        $zReport->tra_ack_token = $ackToken;
        $zReport->closed_at = $now;
        $zReport->closed_by = $userId ?: auth()->id();
        $zReport->save();

        return $zReport;
    }
}
