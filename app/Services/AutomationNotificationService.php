<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseRequisition;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Workdo\Account\Models\Expense;
use Workdo\Pos\Models\Pos;
use Workdo\ProductService\Models\WarehouseStock;

class AutomationNotificationService
{
    public static function scanWorkspace(int $creatorId, bool $sendEmail = false): int
    {
        $created = 0;
        $today = now()->toDateString();

        // 1. Low Stock Alerts
        $lowStocks = WarehouseStock::with(['product:id,name,sku', 'warehouse:id,name'])
            ->whereHas('product', fn ($q) => $q->where('created_by', $creatorId))
            ->where('quantity', '<=', 5)
            ->limit(25)
            ->get();

        foreach ($lowStocks as $stock) {
            $created += self::dispatchOnce($creatorId, [
                'type' => 'low_stock',
                'title' => 'Low Stock Alert: ' . ($stock->product?->name ?? 'Item'),
                'message' => sprintf(
                    '%s (%s) at %s has only %s units remaining.',
                    $stock->product?->name ?? 'Item',
                    $stock->product?->sku ?? '-',
                    $stock->warehouse?->name ?? 'Warehouse',
                    $stock->quantity
                ),
                'severity' => ((float) $stock->quantity <= 0) ? 'critical' : 'warning',
                'link' => '/product-service/stock',
                'dedupe_key' => "low_stock:{$stock->id}:{$today}",
            ], $sendEmail);
        }

        // 2. Purchase Due Dates & Supplier Payment Deadlines
        $duePurchases = PurchaseInvoice::with('vendor:id,name')
            ->where('created_by', $creatorId)
            ->whereIn('status', ['posted', 'partial', 'overdue'])
            ->where('balance_amount', '>', 0)
            ->whereDate('due_date', '<=', now()->addDays(3)->toDateString())
            ->limit(20)
            ->get();

        foreach ($duePurchases as $bill) {
            $isOverdue = $bill->due_date && $bill->due_date->isPast();
            $created += self::dispatchOnce($creatorId, [
                'type' => $isOverdue ? 'supplier_payment' : 'purchase_due',
                'title' => ($isOverdue ? 'Overdue Supplier Payment: ' : 'Upcoming Purchase Due: ') . $bill->invoice_number,
                'message' => sprintf(
                    'Bill %s (%s) balance %s is due on %s.',
                    $bill->invoice_number,
                    $bill->vendor?->name ?? 'Supplier',
                    number_format((float) $bill->balance_amount, 2),
                    optional($bill->due_date)->format('Y-m-d') ?? '-'
                ),
                'severity' => $isOverdue ? 'critical' : 'warning',
                'link' => '/purchases/' . $bill->id,
                'dedupe_key' => "purchase_due:{$bill->id}:{$today}",
            ], $sendEmail);
        }

        // 3. Customer Payment Deadlines
        $dueSales = SalesInvoice::with('customer:id,name')
            ->where('created_by', $creatorId)
            ->whereIn('status', ['posted', 'partial', 'sent', 'overdue'])
            ->where('balance_amount', '>', 0)
            ->whereDate('due_date', '<=', now()->addDays(3)->toDateString())
            ->limit(20)
            ->get();

        foreach ($dueSales as $inv) {
            $isOverdue = $inv->due_date && $inv->due_date->isPast();
            $created += self::dispatchOnce($creatorId, [
                'type' => 'customer_payment',
                'title' => ($isOverdue ? 'Overdue Customer Payment: ' : 'Customer Payment Deadline: ') . $inv->invoice_number,
                'message' => sprintf(
                    'Invoice %s (%s) balance %s is due on %s.',
                    $inv->invoice_number,
                    $inv->customer?->name ?? 'Customer',
                    number_format((float) $inv->balance_amount, 2),
                    optional($inv->due_date)->format('Y-m-d') ?? '-'
                ),
                'severity' => $isOverdue ? 'critical' : 'warning',
                'link' => '/sales-invoices/' . $inv->id,
                'dedupe_key' => "customer_due:{$inv->id}:{$today}",
            ], $sendEmail);
        }

        // 4. Pending Approvals (Requisitions & Draft Expenses)
        $pendingReqs = PurchaseRequisition::where('created_by', $creatorId)->where('status', 'submitted')->count();
        if ($pendingReqs > 0) {
            $created += self::dispatchOnce($creatorId, [
                'type' => 'pending_approval',
                'title' => 'Pending Requisition Approvals',
                'message' => "{$pendingReqs} purchase requisition(s) awaiting approval.",
                'severity' => 'info',
                'link' => '/procurement/purchase-requisitions',
                'dedupe_key' => "pending_reqs:{$creatorId}:{$today}",
            ], $sendEmail);
        }

        $pendingExpenses = Expense::where('created_by', $creatorId)->where('status', 'draft')->count();
        if ($pendingExpenses > 0) {
            $created += self::dispatchOnce($creatorId, [
                'type' => 'pending_approval',
                'title' => 'Pending Expense Approvals',
                'message' => "{$pendingExpenses} expense voucher(s) awaiting approval.",
                'severity' => 'info',
                'link' => '/account/expenses',
                'dedupe_key' => "pending_exps:{$creatorId}:{$today}",
            ], $sendEmail);
        }

        // 5. Stock Transfer Requests & Receipts
        if (Schema::hasTable('warehouse_transfers')) {
            $pendingTransfers = \Illuminate\Support\Facades\DB::table('warehouse_transfers')
                ->where('created_by', $creatorId)
                ->where('status', 'pending')
                ->count();
            if ($pendingTransfers > 0) {
                $created += self::dispatchOnce($creatorId, [
                    'type' => 'stock_transfer_request',
                    'title' => 'Pending Stock Transfer Requests',
                    'message' => "{$pendingTransfers} stock transfer request(s) awaiting receipt/confirmation.",
                    'severity' => 'info',
                    'link' => '/warehouses',
                    'dedupe_key' => "transfer_req:{$creatorId}:{$today}",
                ], $sendEmail);
            }
        }

        // 6. Cash Register Closing Summary
        $todayPosCount = Pos::where('created_by', $creatorId)->whereDate('created_at', $today)->count();
        if ($todayPosCount > 0) {
            $created += self::dispatchOnce($creatorId, [
                'type' => 'cash_register_closing',
                'title' => 'Daily Cash Register Activity',
                'message' => "{$todayPosCount} POS transaction(s) recorded today ready for end-of-day cash register closing.",
                'severity' => 'info',
                'link' => '/pos/orders',
                'dedupe_key' => "pos_closing:{$creatorId}:{$today}",
            ], $sendEmail);
        }

        return $created;
    }

    public static function recordActivity(int $creatorId, string $title, string $message, string $severity = 'info', ?string $link = null): void
    {
        AppNotification::create([
            'type' => 'system_activity',
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'link' => $link,
            'created_by' => $creatorId,
        ]);
    }

    private static function dispatchOnce(int $creatorId, array $payload, bool $sendEmail): int
    {
        $exists = AppNotification::where('created_by', $creatorId)
            ->where('dedupe_key', $payload['dedupe_key'])
            ->exists();

        if ($exists) {
            return 0;
        }

        AppNotification::create(array_merge($payload, [
            'created_by' => $creatorId,
        ]));

        if ($sendEmail) {
            try {
                $adminEmail = User::where('id', $creatorId)->value('email');
                if ($adminEmail && config('mail.default') && config('mail.default') !== 'log') {
                    Mail::raw($payload['message'], function ($m) use ($adminEmail, $payload) {
                        $m->to($adminEmail)->subject('[MutaDash Alert] ' . $payload['title']);
                    });
                }
            } catch (\Throwable $e) {
                // Keep in-app notification even if SMTP is unconfigured
            }
        }

        return 1;
    }
}

