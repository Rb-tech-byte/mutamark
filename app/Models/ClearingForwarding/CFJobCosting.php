<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFJobCosting extends Model
{
    use HasFactory;

    protected $table = 'cf_job_costings';

    protected $fillable = [
        'cf_job_id',
        'currency',
        'agency_fee_revenue',
        'documentation_fee_revenue',
        'transport_markup_revenue',
        'total_service_revenue',
        'statutory_duties_disbursed',
        'port_shipping_disbursed',
        'transport_haulage_cost',
        'other_third_party_costs',
        'total_disbursements_and_direct_costs',
        'gross_profit',
        'net_margin_percentage',
        'customer_advance_received',
        'customer_invoiced_total',
        'balance_due_from_customer',
        'is_financially_reconciled',
        'reconciled_by',
        'reconciled_at',
    ];

    protected $casts = [
        'agency_fee_revenue' => 'decimal:2',
        'documentation_fee_revenue' => 'decimal:2',
        'transport_markup_revenue' => 'decimal:2',
        'total_service_revenue' => 'decimal:2',
        'statutory_duties_disbursed' => 'decimal:2',
        'port_shipping_disbursed' => 'decimal:2',
        'transport_haulage_cost' => 'decimal:2',
        'other_third_party_costs' => 'decimal:2',
        'total_disbursements_and_direct_costs' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'net_margin_percentage' => 'decimal:2',
        'customer_advance_received' => 'decimal:2',
        'customer_invoiced_total' => 'decimal:2',
        'balance_due_from_customer' => 'decimal:2',
        'is_financially_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(CFJob::class, 'cf_job_id');
    }

    public function reconciler()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Recompute the totals from actual disbursements and estimates
     */
    public function recalculate(): void
    {
        $job = $this->job()->with(['disbursements', 'chargeEstimates', 'transportOrders'])->first();
        if (!$job) {
            return;
        }

        // 1. Total Service Revenue (Agency fees, documentation, markups)
        $agencyFees = $job->chargeEstimates()
            ->where('is_disbursement', false)
            ->where('charge_category', 'agency_fee')
            ->sum('amount_tzs') ?: 0;

        $docFees = $job->chargeEstimates()
            ->where('is_disbursement', false)
            ->where('charge_category', 'documentation_fee')
            ->sum('amount_tzs') ?: 0;

        $transportRevenue = $job->chargeEstimates()
            ->where('is_disbursement', false)
            ->where('charge_category', 'transport_haulage')
            ->sum('amount_tzs') ?: 0;

        $otherServiceRevenue = $job->chargeEstimates()
            ->where('is_disbursement', false)
            ->whereNotIn('charge_category', ['agency_fee', 'documentation_fee', 'transport_haulage'])
            ->sum('amount_tzs') ?: 0;

        $totalRevenue = $agencyFees + $docFees + $transportRevenue + $otherServiceRevenue;

        // 2. Disbursements & Direct Costs
        $statutory = $job->disbursements()
            ->where('charge_category', 'statutory_duty')
            ->sum('amount_tzs') ?: 0;

        $portShipping = $job->disbursements()
            ->whereIn('charge_category', ['port_charges', 'shipping_line', 'terminal_handling'])
            ->sum('amount_tzs') ?: 0;

        $transportCost = $job->transportOrders()
            ->sum('agreed_freight_cost') ?: 0;

        $otherDirect = $job->disbursements()
            ->whereNotIn('charge_category', ['statutory_duty', 'port_charges', 'shipping_line', 'terminal_handling'])
            ->sum('amount_tzs') ?: 0;

        $totalDisbursementsAndDirect = $statutory + $portShipping + $transportCost + $otherDirect;

        // 3. Gross Profit (Revenue - direct costs, noting pure disbursements are pass-through)
        $grossProfit = $totalRevenue - ($transportCost + $otherDirect);
        $margin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        $totalInvoiced = $totalRevenue + $statutory + $portShipping;
        $balance = $totalInvoiced - $this->customer_advance_received;

        $this->update([
            'agency_fee_revenue' => $agencyFees,
            'documentation_fee_revenue' => $docFees,
            'transport_markup_revenue' => $transportRevenue,
            'total_service_revenue' => $totalRevenue,
            'statutory_duties_disbursed' => $statutory,
            'port_shipping_disbursed' => $portShipping,
            'transport_haulage_cost' => $transportCost,
            'other_third_party_costs' => $otherDirect,
            'total_disbursements_and_direct_costs' => $totalDisbursementsAndDirect,
            'gross_profit' => $grossProfit,
            'net_margin_percentage' => round($margin, 2),
            'customer_invoiced_total' => $totalInvoiced,
            'balance_due_from_customer' => $balance,
        ]);
    }
}

