<?php

namespace Database\Seeders;

use App\Models\SalesInvoice;
use App\Models\TraFiscalReceipt;
use App\Models\TraZReport;
use App\Services\TraFiscalService;
use Illuminate\Database\Seeder;

class MutamarkTraFiscalSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = 2; // Default admin/company

        // 1. Seed Yesterday's Transmitted Z-Report
        $yesterdayZ = TraZReport::updateOrCreate(
            ['z_report_number' => 'ZREP-2026-00001', 'created_by' => $creatorId],
            [
                'report_date' => now()->subDay()->toDateString(),
                'opening_receipt_number' => 'TRA-RCT-2026-00001',
                'closing_receipt_number' => 'TRA-RCT-2026-00003',
                'total_receipts_count' => 3,
                'gross_turnover' => 14850000.00,
                'net_taxable_turnover' => 12584745.76,
                'total_vat_18' => 2265254.24,
                'total_vat_zero_rated' => 0,
                'total_vat_exempt' => 0,
                'cumulative_turnover_to_date' => 14850000.00,
                'status' => 'transmitted',
                'tra_ack_token' => 'TRA-ACK-8829104-VFD',
                'closed_at' => now()->subDay()->setTime(23, 59, 0),
                'closed_by' => $creatorId,
                'creator_id' => $creatorId,
            ]
        );

        // 2. Fiscalize existing sales invoices
        $invoices = SalesInvoice::where('created_by', $creatorId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->get();

        if ($invoices->count() === 0) {
            $invoices = SalesInvoice::take(3)->get();
        }

        foreach ($invoices as $inv) {
            TraFiscalService::fiscalizeSalesInvoice($inv);
        }
    }
}
