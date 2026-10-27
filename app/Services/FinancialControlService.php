<?php

namespace App\Services;

use App\Models\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Workdo\ProductService\Models\WarehouseStock;

class FinancialControlService
{
    public static function assertPeriodOpen($date, ?int $companyId = null): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) return;
        $lockedUntil = DB::table('settings')->where('created_by',$companyId ?? creatorId())->where('key','accounting_locked_until')->value('value');
        if ($lockedUntil && \Carbon\Carbon::parse($date)->startOfDay()->lte(\Carbon\Carbon::parse($lockedUntil)->endOfDay())) {
            throw \Illuminate\Validation\ValidationException::withMessages(['journal_date'=>'This accounting period is locked.']);
        }
    }

    public static function reconcileInvoiceBalances(\App\Models\SalesInvoice $invoice, string $reason): \App\Models\SalesInvoice
    {
        CompanyAccess::requirePermission('manage-account'); CompanyAccess::check($invoice);
        return DB::transaction(function () use ($invoice,$reason) {
            $invoice = \App\Models\SalesInvoice::where('created_by',creatorId())->lockForUpdate()->findOrFail($invoice->id);
            abort_if($invoice->status === 'draft',409,'Draft invoices have no settled balance to reconcile.');
            $paid = (float) DB::table('customer_payment_allocations as a')->join('customer_payments as p','p.id','=','a.payment_id')->where('a.invoice_id',$invoice->id)->where('p.created_by',creatorId())->where('p.customer_id',$invoice->customer_id)->where('p.status','cleared')->sum('a.allocated_amount');
            abort_if($paid > (float) $invoice->total_amount + 0.01,422,'Allocated overpayment requires a reviewed advance or credit-note correction.');
            $before = $invoice->only(['paid_amount','balance_amount','status']);
            $balance = round((float) $invoice->total_amount - $paid,2);
            $invoice->update(['paid_amount'=>$paid,'balance_amount'=>$balance,'status'=>$balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'posted')]);
            FinancialAuditLog::record(['event_type'=>'INVOICE_RECONCILED','module'=>'Sales','reference_type'=>'SalesInvoice','reference_id'=>$invoice->id,'reference_number'=>$invoice->invoice_number,'action'=>'Reconciled derived invoice balances to cleared allocations','details'=>$reason,'old_values'=>$before,'new_values'=>$invoice->only(['paid_amount','balance_amount','status'])]);
            return $invoice;
        },3);
    }

    public static function postInvoice(\Illuminate\Database\Eloquent\Model $invoice): void
    {
        CompanyAccess::check($invoice);
        $sales = $invoice instanceof \App\Models\SalesInvoice;
        CompanyAccess::requirePermission($sales ? 'post-sales-invoices' : 'post-purchase-invoices');
        DB::transaction(function () use ($invoice, $sales) {
            $locked = $invoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($invoice->id);
            if ($locked->status !== 'draft') return;
            abort_unless($locked->items()->exists(), 422, 'An invoice must have line items before posting.');
            if ($sales) {
                $customer = \Workdo\Account\Models\Customer::where('created_by', creatorId())->where('user_id', $locked->customer_id)->lockForUpdate()->first();
                if ($customer && $customer->credit_limit !== null) {
                    $outstanding = \App\Models\SalesInvoice::where('created_by', creatorId())->where('customer_id', $locked->customer_id)->where('id', '!=', $locked->id)->whereIn('status', ['posted', 'partial', 'overdue'])->sum('balance_amount');
                    if (round((float) $outstanding + (float) $locked->balance_amount, 2) > (float) $customer->credit_limit) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['credit_limit' => 'This invoice exceeds the customer credit limit. Review the limit or settle outstanding invoices first.']);
                    }
                }
            }
            if ($sales) \App\Events\PostSalesInvoice::dispatch($locked);
            else \App\Events\PostPurchaseInvoice::dispatch($locked);
            $locked->update(['status' => 'posted']);
            if ($sales && $locked->sales_order_id) \App\Models\SalesOrder::where('created_by', creatorId())->where('id', $locked->sales_order_id)->update(['stock_reserved' => false]);
        }, 3);
    }

    /** All manual receipt/payment entry points share the existing Account posting services. */
    public static function clearAccountPayment(\Illuminate\Database\Eloquent\Model $payment, string $status): void
    {
        $customer = $payment instanceof \Workdo\Account\Models\CustomerPayment;
        $partyKey = $customer ? 'customer_id' : 'vendor_id';
        $invoiceClass = $customer ? \App\Models\SalesInvoice::class : \App\Models\PurchaseInvoice::class;
        $noteClass = $customer ? \Workdo\Account\Models\CreditNote::class : \Workdo\Account\Models\DebitNote::class;
        $applicationClass = $customer ? \Workdo\Account\Models\CreditNoteApplication::class : \Workdo\Account\Models\DebitNoteApplication::class;
        $noteKey = $customer ? 'credit_note_id' : 'debit_note_id';
        CompanyAccess::requirePermission($customer ? 'cleared-customer-payments' : 'cleared-vendor-payments');
        CompanyAccess::check($payment);
        abort_unless(in_array($status, ['cleared', 'cancelled'], true), 422, 'Choose cleared or cancelled.');
        DB::transaction(function () use ($payment, $status, $customer, $partyKey, $invoiceClass, $noteClass, $applicationClass, $noteKey) {
            $locked = $payment::where('created_by', creatorId())->lockForUpdate()->findOrFail($payment->id);
            if ($locked->status === $status) return;
            abort_unless($locked->status === 'pending', 409, 'Finalized payments require a reversal.');
            if ($status === 'cancelled') { $locked->update(['status' => $status]); return; }
            $bank = \Workdo\Account\Models\BankAccount::where('created_by', creatorId())->where('is_active', true)->lockForUpdate()->findOrFail($locked->bank_account_id);
            abort_unless($bank->gl_account_id, 422, 'Select a bank account with a GL account.');
            $allocations = $locked->allocations()->orderBy('invoice_id')->get();
            $applications = $applicationClass::where('payment_id', $locked->id)->orderBy($noteKey)->get();
            $allocationTotal = (float) $allocations->sum('allocated_amount');
            $noteTotal = (float) $applications->sum('applied_amount');
            $withholding = $customer ? 0 : round((float) $locked->wht_base_amount * (float) $locked->wht_rate / 100,2);
            if ($allocations->isEmpty() || abs($allocationTotal - $noteTotal - $withholding - (float) $locked->payment_amount) > 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages(['allocations' => 'Invoice allocations must equal cash payment plus applied notes.']);
            }
            foreach ($allocations->groupBy('invoice_id') as $invoiceId => $group) {
                $invoice = $invoiceClass::where('created_by', creatorId())->where($partyKey, $locked->$partyKey)->lockForUpdate()->findOrFail($invoiceId);
                $amount = round((float) $group->sum('allocated_amount'), 2);
                if (!in_array($invoice->status, ['posted', 'partial', 'overdue'], true) || $amount <= 0 || $amount > round((float) $invoice->balance_amount, 2)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['allocations' => 'Allocate only the outstanding balance of a posted invoice belonging to this party.']);
                }
                if (!$customer) ThreeWayMatchService::assertInvoicePayable($invoice);
                $invoice->paid_amount = round((float) $invoice->paid_amount + $amount, 2);
                $invoice->balance_amount = round((float) $invoice->total_amount - (float) $invoice->paid_amount, 2);
                $invoice->status = $invoice->balance_amount <= 0 ? 'paid' : 'partial';
                $invoice->save();
            }
            foreach ($applications->groupBy($noteKey) as $noteId => $group) {
                $note = $noteClass::where('created_by', creatorId())->where($partyKey, $locked->$partyKey)->lockForUpdate()->findOrFail($noteId);
                $amount = round((float) $group->sum('applied_amount'), 2);
                if (!in_array($note->status, ['approved', 'partial'], true) || $amount <= 0 || $amount > round((float) $note->balance_amount, 2)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['notes' => 'A selected note is unavailable or exceeds its outstanding balance.']);
                }
                $note->applied_amount = round((float) $note->applied_amount + $amount, 2);
                $note->balance_amount = round((float) $note->total_amount - (float) $note->applied_amount, 2);
                $note->status = $note->balance_amount <= 0 ? 'applied' : 'partial';
                $note->save();
            }
            if ((float) $locked->payment_amount > 0) {
                $journal = app(\Workdo\Account\Services\JournalService::class);
                $bankService = app(\Workdo\Account\Services\BankTransactionsService::class);
                $entry = $customer ? $journal->createCustomerPaymentJournal($locked) : $journal->createVendorPaymentJournal($locked);
                if (!$entry instanceof \Workdo\Account\Models\JournalEntry) throw new \RuntimeException('Journal posting failed. Payment was rolled back.');
                if ($customer) $bankService->createCustomerPayment($locked);
                else $bankService->createVendorPayment($locked);
            }
            if ($withholding > 0) {
                $liability = company_setting('withholding_payable_account_id');
                abort_unless($liability,422,'Configure the withholding payable account before clearing this payment.');
                $payable = \Workdo\Account\Models\ChartOfAccount::where('created_by',creatorId())->where('account_code','2000')->firstOrFail();
                app(\Workdo\Account\Services\JournalService::class)->postControlledJournal('vendor_withholding',$locked->id,(string) $locked->payment_date,[['account_id'=>$payable->id,'debit_amount'=>$withholding,'credit_amount'=>0],['account_id'=>$liability,'debit_amount'=>0,'credit_amount'=>$withholding]]);
                DB::table('withholding_taxes')->insert(['wht_number'=>DocumentNumberService::next('WHT'),'reference_type'=>'vendor_payment','reference_id'=>$locked->id,'vendor_id'=>$locked->vendor_id,'gross_amount'=>$locked->wht_base_amount,'wht_rate'=>$locked->wht_rate,'wht_amount'=>$withholding,'net_payable'=>$locked->payment_amount,'status'=>'pending','payment_date'=>$locked->payment_date,'gl_account_id'=>$liability,'creator_id'=>auth()->id(),'created_by'=>creatorId(),'created_at'=>now(),'updated_at'=>now()]);
            }
            $locked->update(['status' => 'cleared']);
        }, 3);
    }

    /**
     * Enforce Appendix B: Invoice & Bill Immutability.
     * Prevents editing or deleting invoices that are already posted, sent, or paid.
     *
     * @param object $invoice Invoice or Bill model instance
     * @param string $attemptedAction Action attempted ('delete', 'update', 'void')
     * @throws \RuntimeException
     */
    public static function assertInvoiceMutable(object $invoice, string $attemptedAction = 'update'): void
    {
        $status = strtolower($invoice->status ?? '');
        $lockedStatuses = ['sent', 'paid', 'partially_paid', 'posted', 'completed'];

        if (in_array($status, $lockedStatuses)) {
            $refNumber = $invoice->invoice_number ?? $invoice->bill_number ?? "INV-{$invoice->id}";

            FinancialAuditLog::record([
                'event_type' => 'IMMUTABILITY_BLOCKED',
                'module' => 'Sales',
                'reference_type' => get_class($invoice),
                'reference_id' => $invoice->id,
                'reference_number' => $refNumber,
                'action' => "Attempted {$attemptedAction} on immutable {$status} invoice blocked",
                'details' => "Financial Control Policy (Appendix B): Documents with status '{$status}' cannot be modified or deleted. An authorized Credit Note must be issued instead.",
                'severity' => 'critical',
                'old_values' => ['status' => $status],
                'new_values' => ['attempted_action' => $attemptedAction],
            ]);

            throw new \RuntimeException("Financial Immutability Violation: Document {$refNumber} has status '{$status}' and cannot be {$attemptedAction}d. Issue a Credit Note or Debit Note to make corrections.");
        }
    }

    /**
     * Enforce Appendix B: Double-Entry Ledger Immutability.
     * Prevents deleting or direct alteration of posted double-entry journals.
     *
     * @param object $journal JournalEntry model instance
     * @throws \RuntimeException
     */
    public static function assertJournalMutable(object $journal): void
    {
        $status = strtolower($journal->status ?? '');

        if ($status === 'posted' || $status === 'reversed') {
            $refNumber = $journal->journal_number ?? "JV-{$journal->id}";

            FinancialAuditLog::record([
                'event_type' => 'IMMUTABILITY_BLOCKED',
                'module' => 'Accounting',
                'reference_type' => get_class($journal),
                'reference_id' => $journal->id,
                'reference_number' => $refNumber,
                'action' => "Attempted deletion of posted journal {$refNumber} blocked",
                'details' => "Financial Control Policy (Appendix B): Posted double-entry journal vouchers cannot be deleted. A Reversal Journal Voucher (RJV) must be executed.",
                'severity' => 'critical',
                'old_values' => ['status' => $status],
            ]);

            throw new \RuntimeException("Ledger Immutability Violation: Posted Journal Voucher {$refNumber} cannot be deleted. Execute a Reversal Journal Voucher (RJV) instead.");
        }
    }

    /**
     * Create a Reversal Journal Voucher (RJV) to properly void a posted journal entry.
     *
     * @param int $journalEntryId ID of the posted journal entry
     * @param string $reason Audit justification for the reversal
     * @return array Resulting reversal voucher info
     */
    public static function createReversalJournal(int $journalEntryId, string $reason): array
    {
        CompanyAccess::requirePermission('manage-account');
        self::assertPeriodOpen(now());
        return DB::transaction(function () use ($journalEntryId, $reason) {
            $original = DB::table('journal_entries')->where('created_by', creatorId())->where('id', $journalEntryId)->lockForUpdate()->first();
            if (!$original) {
                throw new \InvalidArgumentException("Journal entry #{$journalEntryId} not found.");
            }

            if ($original->status !== 'posted') {
                throw new \RuntimeException("Journal entry {$original->journal_number} has already been reversed.");
            }

            $items = DB::table('journal_entry_items')->where('journal_entry_id', $original->id)->get();

            $rjvNumber = DocumentNumberService::next('RJV');

            $creatorId = Auth::check() ? (creatorId() ?: 2) : ($original->created_by ?? 2);

            // Insert reversal header
            $rjvId = DB::table('journal_entries')->insertGetId([
                'journal_number' => $rjvNumber,
                'journal_date' => now()->toDateString(),
                'entry_type' => 'manual',
                'reference_type' => 'JournalReversal',
                'reference_id' => $original->id,
                'description' => "REVERSAL of {$original->journal_number}: {$reason}",
                'total_debit' => $original->total_credit,
                'total_credit' => $original->total_debit,
                'status' => 'posted',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Invert debit and credit for every line
            foreach ($items as $item) {
                DB::table('journal_entry_items')->insert([
                    'journal_entry_id' => $rjvId,
                    'account_id' => $item->account_id,
                    'description' => "Reversal: " . $item->description,
                    'debit_amount' => $item->credit_amount,
                    'credit_amount' => $item->debit_amount,
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $account = \Workdo\Account\Models\ChartOfAccount::where('created_by',creatorId())->lockForUpdate()->findOrFail($item->account_id);
                $delta = (float) $item->credit_amount - (float) $item->debit_amount;
                $account->increment('current_balance', $account->normal_balance === 'debit' ? $delta : -$delta);
            }

            // Mark original as reversed
            DB::table('journal_entries')->where('id', $original->id)->update([
                'status' => 'reversed',
                'updated_at' => now(),
            ]);

            // Log in Financial Audit Ledger
            FinancialAuditLog::record([
                'event_type' => 'JOURNAL_REVERSED',
                'module' => 'Accounting',
                'reference_type' => 'JournalEntry',
                'reference_id' => $rjvId,
                'reference_number' => $rjvNumber,
                'action' => "Executed Reversal Journal Voucher {$rjvNumber} for {$original->journal_number}",
                'details' => "Reason: {$reason}. Inverted {$items->count()} ledger accounts totaling TZS " . number_format($original->total_debit, 2),
                'severity' => 'info',
                'old_values' => ['original_status' => $original->status, 'journal_number' => $original->journal_number],
                'new_values' => ['rjv_number' => $rjvNumber, 'status' => 'posted', 'reversed_amount' => $original->total_debit],
            ]);

            return [
                'reversal_id' => $rjvId,
                'reversal_number' => $rjvNumber,
                'original_number' => $original->journal_number,
                'reversed_amount' => (float) $original->total_debit,
                'status' => 'posted',
            ];
        });
    }

    /**
     * Enforce Appendix B: Negative Stock Prevention Guard.
     * Prevents issuing deliveries or transfers that exceed available physical stock.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $requestedQuantity
     * @param string $referenceNumber
     * @throws \RuntimeException
     */
    public static function assertSufficientStock(int $productId, int $warehouseId, float $requestedQuantity, string $referenceNumber = 'N/A'): void
    {
        $stock = WarehouseStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $availableQty = (float) ($stock?->quantity ?? 0);

        if ($availableQty < $requestedQuantity) {
            FinancialAuditLog::record([
                'event_type' => 'NEGATIVE_STOCK_BLOCKED',
                'module' => 'Inventory',
                'reference_number' => $referenceNumber,
                'action' => "Blocked stock dispatch exceeding available inventory balance",
                'details' => "Inventory Protection Guard: Attempted to dispatch {$requestedQuantity} units, but only {$availableQty} units exist in Warehouse #{$warehouseId}.",
                'severity' => 'warning',
                'old_values' => ['available_qty' => $availableQty],
                'new_values' => ['requested_qty' => $requestedQuantity, 'deficit' => ($requestedQuantity - $availableQty)],
            ]);

            throw new \RuntimeException("Inventory Control Guard: Insufficient stock in warehouse. Available: {$availableQty}, Requested: {$requestedQuantity}. Negative stock operations are strictly prohibited.");
        }
    }

    /**
     * Verify SHA-256 cryptographic chaining integrity across all audit logs.
     *
     * @return array Cryptographic integrity validation report
     */
    public static function verifyAuditChainIntegrity(): array
    {
        $logs = FinancialAuditLog::orderBy('id', 'asc')->get();

        $tampered = [];
        $expectedPrevHash = 'GENESIS_HASH_MUTAMARK_ERP_2026';
        $verifiedCount = 0;

        foreach ($logs as $log) {
            // 1. Verify previous hash link
            if ($log->previous_hash !== $expectedPrevHash) {
                $tampered[] = [
                    'audit_number' => $log->audit_number,
                    'reason' => 'Hash chain break: previous_hash mismatch',
                    'expected' => $expectedPrevHash,
                    'found' => $log->previous_hash,
                ];
            }

            // 2. Re-compute current hash
            $computed = FinancialAuditLog::computeHash(
                $log->audit_number,
                $log->event_type,
                $log->reference_number,
                $log->actor_name,
                $log->created_at->toDateTimeString(),
                $log->previous_hash,
                $log->payload_hash ? FinancialAuditLog::payloadHash($log->toArray()) : null
            );

            if ($computed !== $log->current_hash) {
                $tampered[] = [
                    'audit_number' => $log->audit_number,
                    'reason' => 'Signature mismatch: data tampering detected',
                    'expected' => $computed,
                    'found' => $log->current_hash,
                ];
            } else {
                $verifiedCount++;
            }

            $expectedPrevHash = $log->current_hash;
        }

        return [
            'is_valid' => count($tampered) === 0,
            'total_logs' => $logs->count(),
            'verified_count' => $verifiedCount,
            'tampered_records' => $tampered,
            'latest_block_hash' => $expectedPrevHash,
            'verification_timestamp' => now()->toIso8601String(),
        ];
    }
}
