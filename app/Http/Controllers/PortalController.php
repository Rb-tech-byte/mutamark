<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\PaymentTransaction;
use App\Models\PurchaseInvoice;
use App\Models\Rfq;
use App\Models\SalesInvoice;
use App\Models\SalesProposal;
use App\Models\ServiceTicket;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\RfqService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Services\CompanyAccess;
use Workdo\Account\Models\Customer;
use Workdo\Account\Models\Vendor;

class PortalController extends Controller
{
    private function customer(Request $request): User
    {
        $user = $request->user();
        if (in_array($user->type, ['client', 'customer'])) {
            abort_if($request->filled('customer_id') && $request->integer('customer_id') !== $user->id, 403);
            return $user;
        }
        CompanyAccess::requirePermission('manage-customers');
        $query = User::where('created_by', creatorId())->whereIn('type', ['client', 'customer']);
        return $request->filled('customer_id') ? $query->findOrFail($request->integer('customer_id')) : $query->firstOrFail();
    }

    private function customerDocument($document): void
    {
        CompanyAccess::check($document);
        if (in_array(Auth::user()->type, ['client', 'customer'])) {
            abort_unless((int) $document->customer_id === Auth::id(), 403);
        } else {
            CompanyAccess::requirePermission('manage-customers');
        }
    }

    private function supplier(Request $request): Vendor
    {
        if ($request->user()->type === 'vendor') {
            $vendor = Vendor::where('created_by', creatorId())->where('user_id', Auth::id())->firstOrFail();
            abort_if($request->filled('vendor_id') && $request->integer('vendor_id') !== $vendor->id, 403);
            return $vendor;
        }
        CompanyAccess::requirePermission('manage-vendors');
        $query = Vendor::where('created_by', creatorId());
        return $request->filled('vendor_id') ? $query->findOrFail($request->integer('vendor_id')) : $query->firstOrFail();
    }
    /**
     * Customer Self-Service Portal (§3.27.1)
     */
    public function customerPortal(Request $request)
    {
        $customerUser = $this->customer($request);
        $allCustomers = Auth::user()->can('manage-customers') ? User::where('created_by', creatorId())->whereIn('type', ['client', 'customer'])->get(['id', 'name', 'email']) : collect([$customerUser->only(['id', 'name', 'email'])]);

        // Customer's proposals
        $proposals = SalesProposal::where('customer_id', $customerUser->id)
            ->where('created_by', creatorId())->where('status', '!=', 'draft')
            ->with('items')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        // Customer's invoices
        $invoices = SalesInvoice::where('customer_id', $customerUser->id)
            ->where('created_by', creatorId())->where('status', '!=', 'draft')
            ->with(['items', 'paymentAllocations.payment'])
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        // Customer's delivery notes
        $deliveryNotes = DeliveryNote::where('customer_id', $customerUser->id)
            ->where('created_by', creatorId())
            ->with('items')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        // Customer's service & repair tickets
        $tickets = ServiceTicket::where('created_by', creatorId())->where('customer_id', $customerUser->id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        // Account Statement Summary
        $statementQuery = SalesInvoice::where('created_by', creatorId())->where('customer_id', $customerUser->id)->where('status', '!=', 'draft');
        $totalInvoiced = (clone $statementQuery)->sum('total_amount');
        $totalPaid = (clone $statementQuery)->sum('paid_amount');
        $outstandingBalance = max(0, $totalInvoiced - $totalPaid);

        return Inertia::render('Portal/CustomerPortal', [
            'customer' => [
                'id' => $customerUser->id,
                'name' => $customerUser->name,
                'email' => $customerUser->email,
                'phone' => $customerUser->mobile_no ?? '',
            ],
            'statement' => [
                'total_invoiced' => (float) $totalInvoiced,
                'total_paid' => (float) $totalPaid,
                'outstanding_balance' => (float) $outstandingBalance,
            ],
            'proposals' => $proposals,
            'invoices' => $invoices,
            'delivery_notes' => $deliveryNotes,
            'tickets' => $tickets,
            'available_customers' => $allCustomers,
        ]);
    }

    /**
     * Customer Approves a Quotation / Proposal
     */
    public function customerAcceptProposal($id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
        $proposal = SalesProposal::lockForUpdate()->findOrFail($id);
        $this->customerDocument($proposal);
        abort_unless($proposal->status === 'sent', 409);
        $proposal->update(['status' => 'accepted']);

        return redirect()->back()->with('success', "Quotation {$proposal->proposal_number} has been approved. Our sales team is preparing order dispatch.");
        }, 3);
    }

    /**
     * Customer Requests Revisions / Rejects a Proposal
     */
    public function customerRejectProposal(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
        $proposal = SalesProposal::lockForUpdate()->findOrFail($id);
        $this->customerDocument($proposal);
        abort_unless($proposal->status === 'sent', 409);
        $proposal->update([
            'status' => 'draft',
            'notes' => trim(($proposal->notes ?? '') . "\nCustomer Feedback: " . $request->input('reason', 'Revision requested.')),
        ]);

        return redirect()->back()->with('info', "Feedback recorded for Quotation {$proposal->proposal_number}. An updated proposal will be issued.");
        }, 3);
    }

    /**
     * Customer Submits Payment Reference (Lipa Namba / Bank Ref)
     */
    public function customerSubmitPayment(Request $request, $invoiceId)
    {
        $invoice = SalesInvoice::findOrFail($invoiceId);
        $this->customerDocument($invoice);
        abort_unless(in_array($invoice->status, ['posted', 'partial', 'overdue']), 409);
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_reference' => 'required|string|max:100',
            'amount_paid' => 'required|numeric|min:1|max:'.$invoice->balance_amount,
            'notes' => 'nullable|string',
        ]);

        $gateway = \App\Models\PaymentGateway::where('creator_id', creatorId())->where('is_active', true)
            ->whereIn('gateway_key', ['manual_mobile_money', 'bank_transfer'])->orderByDesc('is_default')->first();
        if (!$gateway) throw \Illuminate\Validation\ValidationException::withMessages(['payment_method' => __('Finance must configure a payment channel first.')]);
        PaymentTransaction::create([
            'gateway_key' => $gateway->gateway_key,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_type' => 'sales_invoice',
            'payment_method_channel' => $validated['payment_method'],
            'amount' => $validated['amount_paid'],
            'currency' => 'TZS',
            'status' => 'pending_verification',
            'customer_name' => $invoice->customer->name,
            'customer_email' => $invoice->customer->email,
            'gateway_response' => ['submitted_ref' => $validated['transaction_reference'], 'notes' => $validated['notes'] ?? null],
            'creator_id' => creatorId(), 'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Payment reference {$validated['transaction_reference']} submitted! Our finance desk will verify and update your invoice.");
    }

    /**
     * Customer Submits a Support or Repair Ticket
     */
    public function customerSubmitTicket(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'issue_reported' => 'required|string',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        $user = $this->customer($request);

        $ticket = ServiceTicket::create([
            'ticket_number' => ServiceTicket::generateTicketNumber(),
            'customer_name' => $user ? $user->name : 'Portal Client',
            'customer_id' => $user->id,
            'contact_phone' => $validated['contact_phone'] ?? ($user ? $user->mobile_no : null),
            'product_name' => $validated['product_name'],
            'serial_number' => $validated['serial_number'] ?? null,
            'service_type' => 'warranty_claim',
            'issue_reported' => $validated['issue_reported'],
            'status' => 'received',
            'creator_id' => $user ? $user->id : 1,
            'created_by' => creatorId(),
        ]);

        return redirect()->back()->with('success', "Support & Service ticket {$ticket->ticket_number} created successfully.");
    }

    /**
     * Supplier Self-Service Portal (§3.28.1)
     */
    public function supplierPortal(Request $request)
    {
        $vendor = $this->supplier($request);
        $allVendors = Auth::user()->can('manage-vendors') ? Vendor::where('created_by', creatorId())->get(['id', 'company_name']) : collect([$vendor->only(['id', 'company_name'])]);

        // Published RFQs available for bidding
        $openRfqs = Rfq::with(['items', 'quotations' => fn ($q) => $q->where('vendor_id', $vendor->id)])
            ->where('created_by', creatorId())
            ->whereIn('status', ['published', 'evaluating'])
            ->orderBy('id', 'desc')
            ->get();
        foreach ($openRfqs as $rfq) $rfq->setAppends(['status_label', 'total_target_amount']);

        // Vendor's submitted bids
        $submittedBids = [];
        if ($vendor) {
            $submittedBids = SupplierQuotation::where('vendor_id', $vendor->id)
                ->where('created_by', creatorId())
                ->with(['rfq', 'items'])
                ->orderBy('id', 'desc')
                ->get();
        }

        // Vendor's Purchase Bills / Orders
        $purchaseOrders = [];
        if ($vendor) {
            $purchaseOrders = \App\Models\PurchaseOrder::where('created_by', creatorId())->where('vendor_id', $vendor->id)
                ->with('items')
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get();
        }

        return Inertia::render('Portal/SupplierPortal', [
            'vendor' => $vendor ? [
                'id' => $vendor->id,
                'name' => $vendor->company_name,
                'contact' => $vendor->contact_person_name,
                'email' => $vendor->primary_email ?? $vendor->contact_person_email ?? '',
            ] : null,
            'open_rfqs' => $openRfqs,
            'submitted_bids' => $submittedBids,
            'purchase_orders' => $purchaseOrders,
            'available_vendors' => $allVendors,
        ]);
    }

    public function supplierAcceptOrder(Request $request, \App\Models\PurchaseOrder $purchaseOrder)
    {
        $vendor=$this->supplier($request);
        abort_unless((int)$purchaseOrder->created_by===(int)creatorId() && (int)$purchaseOrder->vendor_id===(int)$vendor->id && in_array($purchaseOrder->status,['issued','partially_received','received']),404);
        $purchaseOrder->update(['supplier_accepted_at'=>$purchaseOrder->supplier_accepted_at ?? now()]);
        return $request->expectsJson() ? response()->json($purchaseOrder) : back()->with('success',__('Purchase order accepted.'));
    }

    public function supplierUpload(Request $request, \App\Models\PurchaseOrder $purchaseOrder)
    {
        $vendor=$this->supplier($request);
        abort_unless((int)$purchaseOrder->created_by===(int)creatorId() && (int)$purchaseOrder->vendor_id===(int)$vendor->id,404);
        $request->merge(['title'=>$purchaseOrder->po_number.' supplier attachment','access_level'=>'private','entity_type'=>'purchase_order','entity_id'=>$purchaseOrder->id]);
        return app(DocumentController::class)->store($request,true);
    }

    public function supplierUpdateProfile(Request $request)
    {
        $vendor=$this->supplier($request);
        $data=$request->validate(['company_name'=>'required|string|max:191','contact_person_name'=>'nullable|string|max:191','contact_person_email'=>'nullable|email|max:191','contact_person_mobile'=>'nullable|string|max:32']);
        $vendor->update($data);
        return $request->expectsJson() ? response()->json($vendor->only(array_keys($data))) : back()->with('success',__('Supplier contact details updated.'));
    }

    /**
     * Supplier Submits Quotation Bid directly online
     */
    public function supplierSubmitBid(Request $request, $rfqId)
    {
        $rfq = Rfq::findOrFail($rfqId);
        CompanyAccess::check($rfq);
        $vendor = $this->supplier($request);
        abort_unless(in_array($rfq->status, ['published', 'evaluating']) && (!$rfq->closing_date || !$rfq->closing_date->endOfDay()->isPast()), 409);

        $validated = $request->validate([
            'vendor_id' => 'required|integer',
            'quotation_reference' => 'required|string|max:100',
            'delivery_lead_time_days' => 'required|integer|min:1',
            'warranty_months' => 'nullable|integer|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.rfq_item_id' => ['required', 'distinct', Rule::exists('rfq_items', 'id')->where('rfq_id', $rfq->id)],
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $validated['submission_date'] = now()->toDateString();
        $validated['vendor_id'] = $vendor->id;

        $bid = RfqService::addVendorBid($rfq, $validated, Auth::id() ?: 1, $rfq->created_by);

        return redirect()->back()->with('success', "Tender bid {$bid->quotation_reference} successfully submitted for RFQ {$rfq->rfq_number}.");
    }
}
