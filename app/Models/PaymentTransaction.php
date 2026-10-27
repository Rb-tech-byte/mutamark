<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_reference',
        'gateway_key',
        'invoice_type',
        'invoice_id',
        'invoice_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'amount',
        'currency',
        'fee_amount',
        'net_amount',
        'status',
        'gateway_transaction_id',
        'payment_method_channel',
        'receipt_voucher_number',
        'customer_payment_id',
        'gateway_response',
        'checkout_token',
        'paid_at',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            // Master Document Numbering: TXN-YYYY-#####
            if (empty($model->transaction_reference)) {
                $model->transaction_reference = \App\Services\DocumentNumberService::next('TXN');
            }
            // Secure checkout token
            if (empty($model->checkout_token)) {
                $model->checkout_token = Str::random(40) . time();
            }

            // Default Net Amount
            if (empty($model->net_amount) && !empty($model->amount)) {
                $model->net_amount = (float) $model->amount - (float) ($model->fee_amount ?? 0);
            }
        });
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_key', 'gateway_key');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    /**
     * Complete payment, reconcile ledger, update invoice, and issue Receipt Voucher (RV-YYYY-#####)
     */
    public function markAsCompleted(string $gatewayTxnId, string $channel = 'mobile_money', array $response = []): bool
    {
        \App\Services\TransactionSafety::requireTransactionalTables(['payment_transactions', 'sales_invoices', 'customer_payments', 'customer_payment_allocations', 'bank_accounts', 'bank_transactions', 'journal_entries', 'journal_entry_items', 'chart_of_accounts', 'document_verifications']);
        return DB::transaction(function () use ($gatewayTxnId, $channel, $response) {
        $locked = static::lockForUpdate()->findOrFail($this->id);
        $this->setRawAttributes($locked->getAttributes(), true);
        $this->unsetRelations();
        if ($this->status === 'completed') return true;
        if (!in_array($this->status, ['pending', 'pending_verification', 'processing'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['status' => 'Only pending payments can be settled.']);
        }
        $invoice = SalesInvoice::where('created_by', $this->creator_id)->lockForUpdate()->find($this->invoice_id);
        if (!$invoice || !in_array($invoice->status, ['posted', 'partial', 'overdue']) || (float) $this->amount <= 0 || (float) $this->amount > (float) $invoice->balance_amount || $this->currency !== 'TZS') {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Settlement requires a posted invoice in TZS with sufficient outstanding balance.']);
        }
        $gateway = PaymentGateway::where('gateway_key', $this->gateway_key)->where('created_by', $this->creator_id)->where('is_active', true)->lockForUpdate()->firstOrFail();
        $bankId = (int) ($gateway->credentials['bank_account_id'] ?? 0);
        $bank = \Workdo\Account\Models\BankAccount::where('created_by', $this->creator_id)->where('is_active', true)->lockForUpdate()->find($bankId);
        if (!$bank || !$bank->gl_account_id || (float) $this->fee_amount !== 0.0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['gateway_key' => 'Configure a company bank/GL account for this gateway. Fee-bearing settlement requires a fee posting configuration.']);
        }
        if (static::where('gateway_key', $this->gateway_key)->where('gateway_transaction_id', $gatewayTxnId)->where('status', 'completed')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['gateway_transaction_id' => 'This provider reference has already settled another payment.']);
        }
        $this->status = 'completed';
        $this->gateway_transaction_id = $gatewayTxnId;
        $this->payment_method_channel = $channel;
        $this->gateway_response = $response;
        $this->paid_at = Carbon::now();

        // 1. Generate Master Receipt Voucher Number: RV-YYYY-#####
        $rvNumber = \App\Services\DocumentNumberService::next('RV');
        $this->receipt_voucher_number = $rvNumber;

        // 2. Insert into customer_payments ledger
        $paymentId = DB::table('customer_payments')->insertGetId([
            'payment_number' => $rvNumber,
            'payment_date' => Carbon::now()->toDateString(),
            'customer_id' => $invoice->customer_id,
            'bank_account_id' => $bank->id,
            'reference_number' => $gatewayTxnId,
            'payment_amount' => $this->amount,
            'status' => 'cleared',
            'notes' => "Online settlement via {$this->gateway_key} ({$channel}). Transaction Ref: {$this->transaction_reference}",
            'creator_id' => $this->created_by,
            'created_by' => $this->creator_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $this->customer_payment_id = $paymentId;
        $this->save();

        // 3. Update Sales Invoice balance and status if linked
        if ($this->invoice_type === 'sales_invoice' && $this->invoice_id) {
            $invoice = SalesInvoice::find($this->invoice_id);
            if ($invoice) {
                $newPaid = (float) $invoice->paid_amount + (float) $this->amount;
                $newBalance = max(0, (float) $invoice->total_amount - $newPaid);
                $invoice->paid_amount = $newPaid;
                $invoice->balance_amount = $newBalance;
                if ($newBalance <= 0) {
                    $invoice->status = 'paid';
                } else {
                    $invoice->status = 'partial';
                }
                $invoice->save();
            }
        }

        // 4. Create Cryptographic SHA-256 Seal in DocumentVerification
        \Workdo\Account\Models\CustomerPaymentAllocation::create([
            'payment_id' => $paymentId, 'invoice_id' => $invoice->id, 'allocated_amount' => $this->amount,
            'creator_id' => $this->created_by, 'created_by' => $this->creator_id,
        ]);
        // Reuse the Account module's bank and double-entry services under the invoice company context.
        $guard = \Illuminate\Support\Facades\Auth::guard('web');
        $previousUser = $guard->user();
        try {
            $guard->setUser(User::findOrFail($this->creator_id));
            $payment = \Workdo\Account\Models\CustomerPayment::findOrFail($paymentId);
            $journal = app(\Workdo\Account\Services\JournalService::class)->createCustomerPaymentJournal($payment);
            if (!$journal instanceof \Workdo\Account\Models\JournalEntry) throw new \RuntimeException('Customer payment journal was not posted.');
            app(\Workdo\Account\Services\BankTransactionsService::class)->createCustomerPayment($payment);
        } finally {
            if ($previousUser) $guard->setUser($previousUser); else $guard->forgetUser();
        }
        if (class_exists(DocumentVerification::class)) {
            $hash = hash_hmac('sha256', "{$rvNumber}|{$this->amount}|{$this->currency}|{$gatewayTxnId}", config('app.key', 'MutamarkSecret2026'));
            DocumentVerification::updateOrCreate([
                'verification_hash' => $hash,
            ], [
                'document_type' => 'receipt_voucher',
                'document_id' => $paymentId,
                'document_number' => $rvNumber,
                'issuer_name' => 'Mutamark Enterprises Ltd',
                'party_name' => $this->customer_name ?: 'Customer',
                'party_phone' => $this->customer_phone,
                'party_email' => $this->customer_email,
                'total_amount' => $this->amount,
                'tax_amount' => 0.00,
                'currency' => $this->currency,
                'issue_date' => Carbon::now()->toDateString(),
                'status' => 'verified',
                'signatory_name' => 'Automated Payment Settlement Gateway',
                'signatory_title' => 'Unified Payment Switch',
                'qr_payload' => url("/verify/document/{$hash}"),
                'verified_at' => Carbon::now(),
                'created_by' => $this->creator_id,
            ]);
        }

        return true;
        }, 3);
    }
}
