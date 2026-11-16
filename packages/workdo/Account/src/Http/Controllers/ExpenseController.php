<?php

namespace Workdo\Account\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Account\Events\ApproveExpense;
use Workdo\Account\Events\CreateExpense;
use Workdo\Account\Events\DestroyExpense;
use Workdo\Account\Events\PostExpense;
use Workdo\Account\Events\UpdateExpense;
use Workdo\Account\Models\Expense;
use Workdo\Account\Models\ExpenseCategories;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\ChartOfAccount;
use Workdo\Account\Http\Requests\StoreExpenseRequest;
use Workdo\Account\Http\Requests\UpdateExpenseRequest;
use Workdo\Account\Services\BankTransactionsService;
use Workdo\Account\Services\JournalService;

class ExpenseController extends Controller
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function index(Request $request)
    {
        if(Auth::user()->can('manage-expenses')){
            $query = Expense::with(['category:id,category_name', 'warehouse:id,name', 'bankAccount:id,account_name', 'chartOfAccount:id,account_code,account_name', 'approvedBy:id,name'])
                ->select('id', 'expense_number', 'expense_date', 'category_id', 'warehouse_id', 'bank_account_id', 'payment_method', 'chart_of_account_id', 'amount', 'description', 'reference_number', 'attachment', 'status', 'rejection_reason', 'approved_by', 'approved_at', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-expenses')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-expenses')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            if ($request->search) {
                $query->where('expense_number', 'like', '%' . $request->search . '%');
            }
            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->payment_method) {
                $query->where('payment_method', $request->payment_method);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->date_from && $request->date_to) {
                $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }
            if ($request->bank_account_id) {
                $query->where('bank_account_id', $request->bank_account_id);
            }

            if ($request->sort) {
                $query->orderBy($request->sort, $request->direction ?? 'asc');
            } else {
                $query->latest();
            }

            $expenses = $query->paginate($request->per_page ?? 10)->withQueryString();

            $categories = ExpenseCategories::where('created_by', creatorId())
                ->where('is_active', true)
                ->select('id', 'category_name')
                ->get();

            $bankAccounts = BankAccount::where('created_by', creatorId())
                ->where('is_active', true)
                ->select('id', 'account_name')
                ->get();

            $warehouses = \App\Models\Warehouse::where('created_by', creatorId())
                ->where('is_active', true)
                ->select('id', 'name')
                ->get();

            $chartOfAccounts = ChartOfAccount::where('created_by', creatorId())
                ->where('is_active', true)
                ->whereBetween('account_code', ['5000', '6999'])
                ->select('id', 'account_code', 'account_name')
                ->orderBy('account_code')
                ->get();

            $allWorkspaceExpenses = Expense::with(['category:id,category_name', 'warehouse:id,name'])
                ->where('created_by', creatorId())
                ->get();

            $analytics = [
                'total_amount' => (float) $allWorkspaceExpenses->sum('amount'),
                'posted_amount' => (float) $allWorkspaceExpenses->where('status', 'posted')->sum('amount'),
                'pending_count' => $allWorkspaceExpenses->where('status', 'draft')->count(),
                'approved_count' => $allWorkspaceExpenses->where('status', 'approved')->count(),
                'rejected_count' => $allWorkspaceExpenses->where('status', 'rejected')->count(),
                'by_category' => $allWorkspaceExpenses->groupBy('category_id')->map(fn ($items) => [
                    'name' => $items->first()->category?->category_name ?? 'Uncategorized',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ])->values(),
                'by_location' => $allWorkspaceExpenses->groupBy('warehouse_id')->map(fn ($items) => [
                    'name' => $items->first()->warehouse?->name ?? 'Head Office / Unassigned',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ])->values(),
                'by_payment_method' => $allWorkspaceExpenses->groupBy('payment_method')->map(fn ($items, $method) => [
                    'method' => $method ?: 'bank_transfer',
                    'total' => (float) $items->sum('amount'),
                    'count' => $items->count(),
                ])->values(),
            ];

            return Inertia::render('Account/Expenses/Index', [
                'expenses' => $expenses,
                'categories' => $categories,
                'bankAccounts' => $bankAccounts,
                'warehouses' => $warehouses,
                'chartOfAccounts' => $chartOfAccounts,
                'analytics' => $analytics,
                'filters' => $request->only(['search', 'category_id', 'warehouse_id', 'payment_method', 'status', 'date_from', 'date_to', 'bank_account_id'])
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreExpenseRequest $request)
    {
        if(Auth::user()->can('create-expenses')){
            $validated = $request->validated();

            $expense = new Expense();
            $expense->expense_date = $validated['expense_date'];
            $expense->category_id = $validated['category_id'];
            $expense->warehouse_id = $validated['warehouse_id'] ?? null;
            $expense->bank_account_id = $validated['bank_account_id'];
            $expense->payment_method = $validated['payment_method'] ?? 'bank_transfer';
            $expense->chart_of_account_id = $validated['chart_of_account_id'];
            $expense->amount = $validated['amount'];
            $expense->description = $validated['description'] ?? null;
            $expense->reference_number = $validated['reference_number'] ?? null;
            if ($request->hasFile('attachment')) {
                $expense->attachment = $request->file('attachment')->store('expense-attachments', 'public');
            }
            $expense->status = 'draft';
            $expense->creator_id = Auth::id();
            $expense->created_by = creatorId();
            $expense->save();

            CreateExpense::dispatch($request, $expense);

            return redirect()->route('account.expenses.index')->with('success', __('The expense has been created successfully.'));
        }
        else{
            return redirect()->route('account.expenses.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        if(Auth::user()->can('edit-expenses') && $expense->created_by == creatorId()){
            if ($expense->status != 'draft') {
                return redirect()->route('account.expenses.index')->with('error', __('Cannot update posted expense.'));
            }

            $validated = $request->validated();

            $expense->expense_date = $validated['expense_date'];
            $expense->category_id = $validated['category_id'];
            $expense->warehouse_id = $validated['warehouse_id'] ?? null;
            $expense->bank_account_id = $validated['bank_account_id'];
            $expense->payment_method = $validated['payment_method'] ?? 'bank_transfer';
            $expense->chart_of_account_id = $validated['chart_of_account_id'];
            $expense->amount = $validated['amount'];
            $expense->description = $validated['description'] ?? null;
            $expense->reference_number = $validated['reference_number'] ?? null;
            if ($request->hasFile('attachment')) {
                $expense->attachment = $request->file('attachment')->store('expense-attachments', 'public');
            }
            $expense->save();

            UpdateExpense::dispatch($request, $expense);

            return back()->with('success', __('The expense details are updated successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(Expense $expense)
    {
        if(Auth::user()->can('delete-expenses') && $expense->created_by == creatorId()){

            if ($expense->status != 'draft') {
                return back()->with('error', __('Cannot delete posted expense.'));
            }

            DestroyExpense::dispatch($expense);

            $expense->delete();

            return back()->with('success', __('Expense deleted successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function approve(Expense $expense)
    {
        if(Auth::user()->can('approve-expenses') && $expense->created_by == creatorId()){

            ApproveExpense::dispatch($expense);

            $expense->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            return back()->with('success', __('Expense approved successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function reject(Request $request, Expense $expense)
    {
        if(Auth::user()->can('approve-expenses') && $expense->created_by == creatorId()){
            if ($expense->status === 'posted') {
                return back()->with('error', __('Cannot reject a posted expense.'));
            }

            $request->validate([
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            $expense->update([
                'status' => 'rejected',
                'rejection_reason' => $request->input('rejection_reason', __('Rejected by approver')),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return back()->with('success', __('Expense rejected.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function post(Expense $expense)
    {
        if(Auth::user()->can('post-expenses') && $expense->created_by == creatorId()){
            if (\App\Models\AccountingPeriod::isDateClosed($expense->expense_date->toDateString(), $expense->created_by)) {
                return back()->with('error', __('Cannot post expense into a closed accounting period.'));
            }
            try {
                $this->journalService->createExpenseEntryJournal($expense);
                $this->bankTransactionsService->createExpensePayment($expense);

                PostExpense::dispatch($expense);

                $expense->update([
                    'status' => 'posted',
                ]);

                return back()->with('success', __('Expense posted successfully.'));
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }
}
