<?php

namespace Workdo\Account\Http\Controllers;

use Workdo\Account\Models\CustomerPayment;
use Workdo\Account\Models\CustomerPaymentAllocation;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\CreditNote;
use Workdo\Account\Models\CreditNoteApplication;
use Workdo\Account\Http\Requests\StoreCustomerPaymentRequest;
use Workdo\Account\Services\JournalService;
use Workdo\Account\Services\BankTransactionsService;
use App\Models\User;
use App\Models\SalesInvoice;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Workdo\Account\Events\CreateCustomerPayment;
use Workdo\Account\Events\UpdateCustomerPaymentStatus;
use Workdo\Account\Events\DestroyCustomerPayment;
use App\Models\EmailTemplate;

class CustomerPaymentController extends Controller
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
        if(Auth::user()->can('manage-customer-payments')){
            $query = CustomerPayment::with(['customer', 'bankAccount', 'allocations.invoice', 'creditNoteApplications.creditNote'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-customer-payments')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-customer-payments')) {
                        $q->where('creator_id', Auth::id())->orWhere('customer_id',Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            // Apply filters
            if ($request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->search) {
                $query->where('payment_number', 'like', '%' . $request->search . '%');
            }
            if ($request->date_from) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }
            if ($request->bank_account_id) {
                $query->where('bank_account_id', $request->bank_account_id);
            }

            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $payments = $query->paginate($request->get('per_page', 10));
            $customers = User::where('type', 'client')->where('created_by', creatorId())->get();

            $bankAccounts = BankAccount::where('is_active', true)->where('created_by', creatorId())->get();

            return Inertia::render('Account/CustomerPayments/Index', [
                'payments' => $payments,
                'customers' => $customers,
                'bankAccounts' => $bankAccounts,
                'filters' => $request->only(['customer_id', 'status', 'search', 'bank_account_id'])
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        if(Auth::user()->can('create-customer-payments')){
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Validate that at least one invoice allocation exists
            if (!$request->allocations || count($request->allocations) === 0) {
                return back()->with('error', __('At least one invoice allocation is required to create a payment.'));
            }

            // Validate credit note amount doesn't exceed invoice allocation amount
            if ($request->credit_notes) {
                $totalInvoiceAmount = collect($request->allocations)->sum('amount');
                $totalCreditNoteAmount = collect($request->credit_notes)->sum('amount');

                if ($totalCreditNoteAmount > $totalInvoiceAmount) {
                    return back()->with('error', __('Credit note amount cannot exceed the total invoice allocation amount.'));
                }
            }

            // Create payment
            $payment = new CustomerPayment();
            $payment->payment_date = $request->payment_date;
            $payment->customer_id = $request->customer_id;
            $payment->bank_account_id = $request->bank_account_id;
            $payment->reference_number = $request->reference_number;
            $payment->payment_amount = $request->payment_amount;
            $payment->notes = $request->notes;
            $payment->creator_id = Auth::id();
            $payment->created_by = creatorId();
            $payment->save();

            // Create allocations if provided
            if ($request->allocations) {
                foreach ($request->allocations as $allocation) {
                    $paymentAllocation = new CustomerPaymentAllocation();
                    $paymentAllocation->payment_id = $payment->id;
                    $paymentAllocation->invoice_id = $allocation['invoice_id'];
                    $paymentAllocation->allocated_amount = $allocation['amount'];
                    $paymentAllocation->save();
                }
            }

            // Handle credit notes if provided
            if ($request->credit_notes) {
                foreach ($request->credit_notes as $creditNote) {
                    $creditNoteModel = CreditNote::find($creditNote['credit_note_id']);
                    if (!$creditNoteModel) continue;

                    // Create credit note application entry
                    CreditNoteApplication::create([
                        'credit_note_id' => $creditNote['credit_note_id'],
                        'payment_id' => $payment->id,
                        'applied_amount' => $creditNote['amount'],
                        'application_date' => $request->payment_date,
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId()
                    ]);
                }
            }

            // Dispatch event
            CreateCustomerPayment::dispatch($request, $payment);

            return $request->expectsJson() ? response()->json($payment->load('allocations'), 201) : redirect()->route('account.customer-payments.index')->with('success', __('The customer payment has been created successfully.'));
            }, 3);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }



    public function getOutstandingInvoices($customerId)
    {
        $invoices = SalesInvoice::where('customer_id', $customerId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['posted', 'partial'])
            ->where('created_by', creatorId())
            ->get();

        $creditNotes = CreditNote::where('customer_id', $customerId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['approved', 'partial'])
            ->where('created_by', creatorId())
            ->get(['id', 'credit_note_number', 'balance_amount', 'total_amount', 'status']);

        return response()->json([
            'invoices' => $invoices,
            'creditNotes' => $creditNotes
        ]);
    }

    public function updateStatus(Request $request, CustomerPayment $customerPayment)
    {
        $data = $request->validate(['status' => 'required|in:cleared,cancelled']);
        \App\Services\FinancialControlService::clearAccountPayment($customerPayment, $data['status']);
        return $request->expectsJson() ? response()->json($customerPayment->fresh()) : back()->with('success', __('The payment status has been updated.'));
    }

    public function destroy(CustomerPayment $customerPayment)
    {
        if(Auth::user()->can('delete-customer-payments') && $customerPayment->created_by == creatorId() && $customerPayment->status === 'pending'){

            \Illuminate\Support\Facades\DB::transaction(function () use ($customerPayment) {
                $locked = CustomerPayment::where('created_by', creatorId())->lockForUpdate()->findOrFail($customerPayment->id);
                abort_unless($locked->status === 'pending', 409, 'Finalized payments require a reversal.');
                DestroyCustomerPayment::dispatch($locked);
                $locked->allocations()->delete();
                $locked->creditNoteApplications()->delete();
                $locked->delete();
            }, 3);
            return back()->with('success', __('The customer payment has been deleted.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }
}
