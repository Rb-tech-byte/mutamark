<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use App\Models\DocumentVerification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentGatewayController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        return [new \Illuminate\Routing\Controllers\Middleware('can:manage-account', except: ['publicCheckout', 'processPublicPayment', 'checkoutSuccess', 'webhook'])];
    }
    /**
     * Helper to safely retrieve creator / company ID
     */
    protected function getCreatorId()
    {
        if (Auth::check()) {
            if (function_exists('creatorId')) {
                try {
                    $cid = creatorId();
                    if ($cid) {
                        return $cid;
                    }
                } catch (\Throwable $e) {}
            }
            return Auth::id();
        }
        return 1;
    }

    /**
     * Display Unified Payment Gateway Manager Dashboard (SRS Module 33)
     */
    public function index(Request $request)
    {
        // 1. KPI Aggregations in TZS
        $completedTxns = PaymentTransaction::where('creator_id', $this->getCreatorId())->where('status', 'completed');
        $totalCollected = $completedTxns->sum('amount');
        $completedCount = $completedTxns->count();
        $totalTxnsCount = PaymentTransaction::where('creator_id', $this->getCreatorId())->count();
        $activeGatewaysCount = PaymentGateway::where('creator_id', $this->getCreatorId())->where('is_active', true)->count();
        $successRate = $totalTxnsCount > 0 ? round(($completedCount / $totalTxnsCount) * 100, 1) : 100.0;

        // 2. Gateways with masked credentials
        $gateways = PaymentGateway::where('creator_id', $this->getCreatorId())->orderBy('is_default', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($gw) {
                $data = $gw->toArray();
                $data['masked_credentials'] = $gw->getMaskedCredentials();
                $data['credentials'] = $data['masked_credentials'];
                $data['completed_volume'] = PaymentTransaction::where('gateway_key', $gw->gateway_key)
                    ->where('status', 'completed')
                    ->sum('amount');
                $data['total_count'] = PaymentTransaction::where('gateway_key', $gw->gateway_key)->count();
                return $data;
            });

        // 3. Transactions Ledger
        $transactions = PaymentTransaction::where('creator_id', $this->getCreatorId())->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        // 4. Open Sales Invoices for Payment Link creation
        $openInvoices = SalesInvoice::select('id', 'invoice_number', 'total_amount', 'paid_amount', 'balance_amount', 'status', 'created_at')
            ->where('created_by', $this->getCreatorId())
            ->where('status', '!=', 'paid')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        // 5. Unified Tanzania Mobile Money / Lipa Namba Config (Loaded dynamically from Company Settings)
        $companySettings = getCompanyAllSetting();
        $tillNumber = $companySettings['lipa_namba_till_number'] ?? '';
        $merchantName = $companySettings['lipa_namba_merchant_name'] ?? ($companySettings['company_name'] ?? 'Company Till');

        $lipaNambaInfo = [
            'till_number' => $tillNumber,
            'merchant_name' => $merchantName,
            'is_configured' => !empty($tillNumber),
            'networks' => [
                [
                    'name' => 'Vodacom M-Pesa',
                    'network_code' => 'mpesa',
                    'ussd_root' => '*150*00#',
                    'ussd_dial_template' => !empty($tillNumber) ? "*150*00*1*{$tillNumber}*{amount}#" : '*150*00#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*00# > 4 (Lipa kwa M-Pesa) > 1 (Weka Namba ya Kampuni) > Lipa Namba: {$tillNumber}"
                        : "Piga *150*00# > 4 (Lipa kwa M-Pesa) > Weka Lipa Namba ya Kampuni",
                    'brand_color' => '#E60000',
                ],
                [
                    'name' => 'Tigo Pesa',
                    'network_code' => 'tigopesa',
                    'ussd_root' => '*150*01#',
                    'ussd_dial_template' => !empty($tillNumber) ? "*150*01*1*{$tillNumber}*{amount}#" : '*150*01#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*01# > 5 (Lipa kwa Simu) > 1 (Mitandao Yote / Lipa Namba) > Lipa Namba: {$tillNumber}"
                        : "Piga *150*01# > 5 (Lipa kwa Simu) > Weka Lipa Namba ya Kampuni",
                    'brand_color' => '#0033A0',
                ],
                [
                    'name' => 'Airtel Money',
                    'network_code' => 'airtelmoney',
                    'ussd_root' => '*150*60#',
                    'ussd_dial_template' => !empty($tillNumber) ? "*150*60*1*{$tillNumber}*{amount}#" : '*150*60#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*60# > 5 (Lipa Bili) > Lipa Namba > Lipa Namba: {$tillNumber}"
                        : "Piga *150*60# > 5 (Lipa Bili) > Weka Lipa Namba ya Kampuni",
                    'brand_color' => '#ED1C24',
                ],
                [
                    'name' => 'Halopesa',
                    'network_code' => 'halopesa',
                    'ussd_root' => '*150*88#',
                    'ussd_dial_template' => !empty($tillNumber) ? "*150*88*1*{$tillNumber}*{amount}#" : '*150*88#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*88# > 5 (Lipa kwa Halopesa) > Namba ya Biashara: {$tillNumber}"
                        : "Piga *150*88# > 5 (Lipa kwa Halopesa) > Weka Namba ya Biashara",
                    'brand_color' => '#FF6600',
                ],
                [
                    'name' => 'CRDB SimBanking',
                    'network_code' => 'crdb',
                    'ussd_root' => '*150*03#',
                    'ussd_dial_template' => '*150*03#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*03# / Fungua SimBanking App > Malipo > Lipa Namba > Weka {$tillNumber}"
                        : "Piga *150*03# / Fungua SimBanking App > Malipo > Lipa Namba",
                    'brand_color' => '#008752',
                ],
                [
                    'name' => 'NMB Mkononi',
                    'network_code' => 'nmb',
                    'ussd_root' => '*150*66#',
                    'ussd_dial_template' => '*150*66#',
                    'instructions' => !empty($tillNumber)
                        ? "Piga *150*66# / Fungua NMB Mkononi App > Lipa Mkononi > Weka {$tillNumber}"
                        : "Piga *150*66# / Fungua NMB Mkononi App > Lipa Mkononi",
                    'brand_color' => '#002B49',
                ],
            ],
        ];

        return Inertia::render('PaymentGateways/Index', [
            'metrics' => [
                'total_collected' => (float) $totalCollected,
                'completed_count' => $completedCount,
                'total_txns_count' => $totalTxnsCount,
                'active_gateways_count' => $activeGatewaysCount,
                'success_rate' => $successRate,
            ],
            'gateways' => $gateways,
            'transactions' => $transactions,
            'open_invoices' => $openInvoices,
            'lipa_namba_info' => $lipaNambaInfo,
        ]);
    }

    /**
     * Create a new custom Payment Gateway or Till Channel
     */
    public function storeGateway(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'gateway_key' => 'required|string|max:50|unique:payment_gateways,gateway_key',
            'description' => 'nullable|string',
            'mode' => 'required|in:sandbox,live',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'transaction_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'transaction_fee_flat' => 'nullable|numeric|min:0',
            'webhook_secret' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'credentials' => 'nullable|array',
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            PaymentGateway::where('creator_id', $this->getCreatorId())->update(['is_default' => false]);
        }

        $validated['creator_id'] = $this->getCreatorId();
        $validated['created_by'] = Auth::id() ?: 1;

        $gw = PaymentGateway::create($validated);

        return redirect()->back()->with('success', "Payment Gateway [{$gw->name}] created successfully.");
    }

    /**
     * Update gateway configuration and credentials (no hardcoding)
     */
    public function updateGateway(Request $request, $id)
    {
        $gateway = PaymentGateway::where('creator_id', $this->getCreatorId())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'mode' => 'required|in:sandbox,live',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'transaction_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'transaction_fee_flat' => 'nullable|numeric|min:0',
            'webhook_secret' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'credentials' => 'nullable|array',
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            // Unset other defaults
            PaymentGateway::where('creator_id', $this->getCreatorId())->where('id', '!=', $gateway->id)->update(['is_default' => false]);
        }

        // Merge credentials so omitted masked keys aren't overwritten with asterisks
        $existingCreds = $gateway->credentials ?? [];
        if (empty($validated['webhook_secret'])) unset($validated['webhook_secret']);
        $newCreds = $validated['credentials'] ?? [];
        foreach ($newCreds as $k => $v) {
            if (str_contains((string)$v, '••••••••')) {
                // Keep existing secret
                $newCreds[$k] = $existingCreds[$k] ?? '';
            }
        }
        $validated['credentials'] = $newCreds;

        $gateway->update($validated);

        return redirect()->back()->with('success', "Payment Gateway [{$gateway->name}] updated successfully.");
    }

    /**
     * Delete custom payment gateway
     */
    public function destroyGateway($id)
    {
        $gateway = PaymentGateway::where('creator_id', $this->getCreatorId())->findOrFail($id);

        $hasCompletedTxns = PaymentTransaction::where('gateway_key', $gateway->gateway_key)
            ->where('status', 'completed')
            ->exists();

        if ($hasCompletedTxns) {
            return redirect()->back()->with('error', "Cannot delete Gateway [{$gateway->name}] because it has completed transactions in the financial ledger. Please deactivate it instead.");
        }

        $gateway->delete();

        return redirect()->back()->with('success', "Payment Gateway [{$gateway->name}] deleted successfully.");
    }

    /**
     * Toggle gateway active status
     */
    public function toggleGateway($id)
    {
        $gateway = PaymentGateway::where('creator_id', $this->getCreatorId())->findOrFail($id);
        $gateway->is_active = !$gateway->is_active;
        $gateway->save();

        return redirect()->back()->with('success', "Gateway status toggled.");
    }

    /**
     * Record an offline/manual Lipa Namba payment and instantly issue Receipt Voucher
     */
    public function manualSettlement(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|integer',
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:150',
            'amount' => 'required|numeric|min:100',
            'currency' => 'nullable|string|max:10',
            'gateway_key' => 'required|string|exists:payment_gateways,gateway_key',
            'payment_channel' => 'required|string|max:50',
            'reference_number' => 'required|string|max:100',
            'settlement_notes' => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCreatorId();
        $invoiceNumber = null;
        if (!empty($validated['invoice_id'])) {
            $inv = SalesInvoice::where('created_by', $this->getCreatorId())->findOrFail($validated['invoice_id']);
            if ($inv) {
                $invoiceNumber = $inv->invoice_number;
            }
        }

        $gw = PaymentGateway::where('creator_id', $this->getCreatorId())->where('gateway_key', $validated['gateway_key'])->firstOrFail();
        $fee = $gw ? $gw->calculateFee((float) $validated['amount']) : 0.00;
        $net = (float) $validated['amount'] - $fee;

        $txn = PaymentTransaction::create([
            'gateway_key' => $validated['gateway_key'],
            'invoice_type' => 'sales_invoice',
            'invoice_id' => $validated['invoice_id'] ?? null,
            'invoice_number' => $invoiceNumber ?: "INV-OFFLINE-" . strtoupper(Str::random(5)),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'amount' => (float) $validated['amount'],
            'currency' => $validated['currency'] ?? 'TZS',
            'fee_amount' => $fee,
            'net_amount' => $net,
            'status' => 'pending',
            'creator_id' => $companyId,
            'created_by' => Auth::id() ?? 1,
        ]);

        // Complete settlement and issue Master Receipt Voucher (RV-YYYY-#####)
        $txn->markAsCompleted($validated['reference_number'], $validated['payment_channel'], [
            'method' => 'manual_offline_settlement',
            'reference_number' => $validated['reference_number'],
            'settlement_notes' => $validated['settlement_notes'] ?? 'Manual Lipa Namba cashier recording',
            'recorded_by' => Auth::check() ? Auth::user()->name : 'System Auditor',
            'recorded_at' => Carbon::now()->toIso8601String(),
        ]);

        return redirect()->back()->with('success', "Payment settled successfully! Receipt Voucher [{$txn->receipt_voucher_number}] issued.");
    }

    /**
     * Reconcile or update transaction reference / audit notes
     */
    public function reconcileTransaction(Request $request, $id)
    {
        $txn = PaymentTransaction::where('creator_id', $this->getCreatorId())->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|in:pending,completed,failed,cancelled,refunded',
            'gateway_transaction_id' => 'nullable|string|max:100',
            'reconciliation_notes' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['gateway_transaction_id'])) {
            $txn->gateway_transaction_id = $validated['gateway_transaction_id'];
        }

        if (!empty($validated['status']) && $validated['status'] === 'completed' && $txn->status !== 'completed') {
            $ref = $validated['gateway_transaction_id'] ?? ('REC-' . time());
            $txn->markAsCompleted($ref, $txn->payment_method_channel ?: 'manual_reconciliation', [
                'reconciled_by' => Auth::check() ? Auth::user()->name : 'Auditor',
                'reconciled_at' => Carbon::now()->toIso8601String(),
                'notes' => $validated['reconciliation_notes'] ?? null,
            ]);
        } else {
            abort_if($txn->status === 'completed' && !empty($validated['status']) && $validated['status'] !== 'completed', 409, 'Use an authorized refund or reversal instead of changing a settled payment status.');
            if (!empty($validated['status'])) {
                $txn->status = $validated['status'];
            }
            $txn->save();
        }

        return redirect()->back()->with('success', "Transaction [{$txn->transaction_reference}] reconciled successfully.");
    }

    /**
     * Void / cancel a pending transaction
     */
    public function voidTransaction(Request $request, $id)
    {
        $txn = PaymentTransaction::where('creator_id', $this->getCreatorId())->findOrFail($id);

        if ($txn->status === 'completed') {
            return redirect()->back()->with('error', "Cannot void a completed payment that has already settled into the double-entry ledger.");
        }

        $txn->status = 'cancelled';
        $txn->save();

        return redirect()->back()->with('success', "Transaction [{$txn->transaction_reference}] has been voided.");
    }

    /**
     * Generate a new shareable payment link for an invoice or direct collection
     */
    public function generatePaymentLink(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|integer',
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:100',
            'currency' => 'nullable|string|max:10',
            'gateway_key' => 'required|string|exists:payment_gateways,gateway_key',
        ]);

        $companyId = $this->getCreatorId();
        $invoiceNumber = null;
        if (!empty($validated['invoice_id'])) {
            $inv = SalesInvoice::where('created_by', $this->getCreatorId())->findOrFail($validated['invoice_id']);
            if ($inv) {
                $invoiceNumber = $inv->invoice_number;
            }
        }

        $gw = PaymentGateway::where('creator_id', $this->getCreatorId())->where('gateway_key', $validated['gateway_key'])->firstOrFail();
        $fee = $gw ? $gw->calculateFee((float) $validated['amount']) : 0.00;
        $net = (float) $validated['amount'] - $fee;

        $txn = PaymentTransaction::create([
            'gateway_key' => $validated['gateway_key'],
            'invoice_type' => 'sales_invoice',
            'invoice_id' => $validated['invoice_id'] ?? null,
            'invoice_number' => $invoiceNumber ?: "INV-DIRECT-" . strtoupper(Str::random(5)),
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'amount' => (float) $validated['amount'],
            'currency' => $validated['currency'] ?? 'TZS',
            'fee_amount' => $fee,
            'net_amount' => $net,
            'status' => 'pending',
            'creator_id' => $companyId,
            'created_by' => Auth::id() ?? 1,
        ]);

        $checkoutUrl = url("/pay/checkout/{$txn->checkout_token}");

        return redirect()->back()->with('success', "Payment Link generated: {$checkoutUrl}");
    }

    /**
     * Public Checkout View for Customer Payment
     */
    public function publicCheckout($token)
    {
        $transaction = PaymentTransaction::where('checkout_token', $token)->firstOrFail();
        $gateways = PaymentGateway::where('creator_id', $transaction->creator_id)->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get()
            ->map(function ($gw) {
                return [
                    'gateway_key' => $gw->gateway_key,
                    'name' => $gw->name,
                    'description' => $gw->description,
                    'mode' => $gw->mode,
                    'instructions' => $gw->instructions,
                    'is_default' => $gw->is_default,
                    'credentials' => $gw->gateway_key === 'manual_mobile_money' || $gw->gateway_key === 'bank_transfer' 
                        ? array_intersect_key($gw->credentials ?? [], array_flip(['till_number', 'merchant_name', 'account_number', 'account_name', 'bank_name', 'paybill']))
                        : null,
                ];
            });

        return Inertia::render('PaymentGateways/Checkout', [
            'transaction' => $transaction->makeHidden(['gateway_response', 'creator_id', 'created_by']),
            'gateways' => $gateways,
        ]);
    }

    /**
     * Process Public Customer Payment Submission
     */
    public function processPublicPayment(Request $request, $token)
    {
        $validated = $request->validate([
            'gateway_key' => 'required|string|exists:payment_gateways,gateway_key',
            'payment_channel' => 'required|string', // mpesa, tigopesa, airtelmoney, card, bank
            'phone_number' => 'nullable|string',
            'reference_number' => 'required|string|max:100',
        ]);

        \App\Services\TransactionSafety::requireTransactionalTables(['payment_transactions']);
        DB::transaction(function () use ($token, $validated) {
        $transaction = PaymentTransaction::where('checkout_token', $token)->lockForUpdate()->firstOrFail();
        abort_unless(in_array($transaction->status, ['pending', 'pending_verification']), 409);
        $gateway = PaymentGateway::where('gateway_key', $validated['gateway_key'])
            ->where('creator_id', $transaction->creator_id)->where('is_active', true)->firstOrFail();
        // Customer-supplied references are claims, never proof that funds settled.
        $transaction->update([
            'status' => 'pending_verification', 'gateway_key' => $gateway->gateway_key,
            'customer_phone' => $validated['phone_number'] ?? $transaction->customer_phone,
            'payment_method_channel' => $validated['payment_channel'],
            'gateway_response' => ['submitted_ref' => $validated['reference_number'], 'submitted_at' => now()->toIso8601String()],
        ]);
        }, 3);
        return redirect()->route('payment.checkout', $token)->with('success', __('Payment reference submitted for verification.'));
    }

    /**
     * Checkout Success Screen with Receipt Voucher Download
     */
    public function checkoutSuccess($token)
    {
        $transaction = PaymentTransaction::where('checkout_token', $token)->firstOrFail();
        abort_unless($transaction->status === 'completed', 409, 'Payment has not been verified.');
        $verification = null;
        if ($transaction->receipt_voucher_number) {
            $verification = DocumentVerification::where('document_number', $transaction->receipt_voucher_number)->first();
        }

        return Inertia::render('PaymentGateways/Success', [
            'transaction' => $transaction->makeHidden(['gateway_response', 'creator_id', 'created_by']),
            'verification' => $verification,
        ]);
    }

    /**
     * Unified Webhook & IPN Handler for external gateways
     */
    public function webhook(Request $request, string $gatewayKey)
    {
        $gateway = PaymentGateway::where('gateway_key', $gatewayKey)->where('is_active', true)->firstOrFail();
        $signature = (string) $request->header('X-Payment-Signature', '');
        abort_unless($gateway->webhook_secret && hash_equals(hash_hmac('sha256', $request->getContent(), $gateway->webhook_secret), $signature), 401);
        $request->validate([
            'status' => 'required|in:completed', 'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10', 'gateway_transaction_id' => 'required|string|max:100',
        ]);
        $payload = $request->all();
        $reference = $payload['transaction_reference'] ?? $payload['reference'] ?? null;

        if (!$reference) {
            return response()->json(['status' => 'error', 'message' => 'Missing transaction reference'], 400);
        }

        $transaction = PaymentTransaction::where('gateway_key', $gatewayKey)->where('creator_id', $gateway->creator_id)
            ->where(fn ($q) => $q->where('transaction_reference', $reference)->orWhere('checkout_token', $reference))
            ->first();

        if (!$transaction) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
        }
        abort_unless(round((float) $payload['amount'], 2) === round((float) $transaction->amount, 2) && strtoupper($payload['currency']) === strtoupper($transaction->currency), 422, 'Payment amount or currency mismatch.');

        $gatewayTxnId = $payload['gateway_transaction_id'] ?? $payload['id'] ?? 'EXT-' . time();
        $channel = $payload['payment_channel'] ?? 'online_gateway';

        $transaction->markAsCompleted($gatewayTxnId, $channel, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment reconciled and Receipt Voucher generated.',
            'receipt_voucher' => $transaction->receipt_voucher_number,
        ]);
    }
}
