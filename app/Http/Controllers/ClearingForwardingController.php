<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ClearingForwarding\CFJob;
use App\Models\ClearingForwarding\CFShipment;
use App\Models\ClearingForwarding\CFCargoLine;
use App\Models\ClearingForwarding\CFContainer;
use App\Models\ClearingForwarding\CFDocumentChecklist;
use App\Models\ClearingForwarding\CFPermit;
use App\Models\ClearingForwarding\CFClassification;
use App\Models\ClearingForwarding\CFValuation;
use App\Models\ClearingForwarding\CFDeclaration;
use App\Models\ClearingForwarding\CFCustomsEvent;
use App\Models\ClearingForwarding\CFChargeEstimate;
use App\Models\ClearingForwarding\CFDisbursement;
use App\Models\ClearingForwarding\CFTransportOrder;
use App\Models\ClearingForwarding\CFTrackingEvent;
use App\Models\ClearingForwarding\CFDelivery;
use App\Models\ClearingForwarding\CFProofOfDelivery;
use App\Models\ClearingForwarding\CFJobCosting;

class ClearingForwardingController extends Controller
{
    /**
     * Dashboard Overview
     */
    public function dashboard(Request $request)
    {
        $totalJobs = CFJob::count();
        $activeJobs = CFJob::whereNotIn('status', ['completed', 'cancelled'])->count();
        $inTransitJobs = CFJob::where('status', 'in_transit')->count();
        $releasedJobs = CFJob::where('status', 'cargo_released')->count();
        $pendingDeclarations = CFDeclaration::whereIn('status', ['draft', 'submitted', 'queried'])->count();

        // Demurrage Risk: Containers with free days <= 5 days remaining
        $containersAtRisk = CFContainer::whereNull('empty_returned_at')
            ->whereNotNull('empty_return_deadline')
            ->whereDate('empty_return_deadline', '<=', now()->addDays(5))
            ->with('job.customer')
            ->get();

        // Financials summary
        $totalAdvances = CFJobCosting::sum('customer_advance_received');
        $totalDisbursed = CFDisbursement::sum('amount_tzs');
        $totalServiceRevenue = CFJobCosting::sum('total_service_revenue');
        $totalProfit = CFJobCosting::sum('gross_profit');

        // Recent Jobs
        $recentJobs = CFJob::with(['customer', 'shipment', 'containers', 'jobCosting'])
            ->latest('id')
            ->take(8)
            ->get();

        return Inertia::render('ClearingForwarding/Dashboard', [
            'metrics' => [
                'total_jobs' => $totalJobs,
                'active_jobs' => $activeJobs,
                'in_transit_jobs' => $inTransitJobs,
                'released_jobs' => $releasedJobs,
                'pending_declarations' => $pendingDeclarations,
                'containers_at_risk_count' => $containersAtRisk->count(),
                'total_advances' => (float) $totalAdvances,
                'total_disbursed' => (float) $totalDisbursed,
                'total_service_revenue' => (float) $totalServiceRevenue,
                'total_profit' => (float) $totalProfit,
            ],
            'containersAtRisk' => $containersAtRisk,
            'recentJobs' => $recentJobs,
        ]);
    }

    /**
     * Job Register (Searchable List)
     */
    public function index(Request $request)
    {
        $query = CFJob::with(['customer', 'shipment', 'containers', 'jobCosting', 'caseOwner']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('job_number', 'like', "%{$s}%")
                    ->orWhere('consignee_name', 'like', "%{$s}%")
                    ->orWhere('consignor_name', 'like', "%{$s}%")
                    ->orWhere('customer_reference', 'like', "%{$s}%")
                    ->orWhereHas('shipment', function ($sq) use ($s) {
                        $sq->where('bl_awb_number', 'like', "%{$s}%")
                           ->orWhere('vessel_flight_truck', 'like', "%{$s}%");
                    })
                    ->orWhereHas('containers', function ($cq) use ($s) {
                        $cq->where('container_number', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('customs_regime')) {
            $query->where('customs_regime', $request->customs_regime);
        }

        if ($request->filled('transport_mode')) {
            $query->where('transport_mode', $request->transport_mode);
        }

        $jobs = $query->latest('id')->paginate(15)->withQueryString();

        return Inertia::render('ClearingForwarding/Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'status', 'service_type', 'customs_regime', 'transport_mode']),
        ]);
    }

    /**
     * Create Job Screen
     */
    public function create()
    {
        $customers = User::where('type', 'client')
            ->orWhere('type', 'customer')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $staff = User::whereIn('type', ['company', 'staff'])
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('ClearingForwarding/Jobs/Create', [
            'customers' => $customers,
            'staff' => $staff,
            'suggestedJobNumber' => CFJob::generateJobNumber(),
        ]);
    }

    /**
     * Store new Job
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'consignee_name' => 'nullable|string|max:255',
            'consignor_name' => 'nullable|string|max:255',
            'service_type' => 'required|string|in:import,export,transit,domestic',
            'transport_mode' => 'required|string|in:sea,air,road,rail',
            'customs_regime' => 'required|string',
            'priority' => 'required|string|in:normal,urgent,critical',
            'customer_reference' => 'nullable|string|max:255',
            'case_owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',

            // Shipment particulars
            'bl_awb_number' => 'nullable|string|max:255',
            'vessel_flight_truck' => 'nullable|string|max:255',
            'voyage_number' => 'nullable|string|max:255',
            'shipping_line_airline' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'port_of_loading' => 'nullable|string|max:255',
            'port_of_discharge' => 'nullable|string|max:255',
            'terminal_icd' => 'nullable|string|max:255',
            'final_destination' => 'nullable|string|max:255',
            'eta' => 'nullable|date',

            // Cargo item line
            'commodity_description' => 'required|string',
            'package_type' => 'nullable|string',
            'package_count' => 'nullable|numeric|min:1',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'cbm' => 'nullable|numeric|min:0',
            'hs_code' => 'nullable|string',
            'customs_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',

            // Container if sea/containerized
            'container_number' => 'nullable|string|max:50',
            'size_type' => 'nullable|string|max:20',
            'seal_number' => 'nullable|string|max:50',
            'free_days' => 'nullable|integer|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $job = CFJob::create([
                'job_number' => CFJob::generateJobNumber(),
                'customer_id' => $validated['customer_id'],
                'consignee_name' => $validated['consignee_name'] ?? null,
                'consignor_name' => $validated['consignor_name'] ?? null,
                'service_type' => $validated['service_type'],
                'transport_mode' => $validated['transport_mode'],
                'customs_regime' => $validated['customs_regime'],
                'status' => 'documents_pending',
                'priority' => $validated['priority'],
                'case_owner_id' => $validated['case_owner_id'] ?? Auth::id(),
                'opened_at' => now(),
                'customer_reference' => $validated['customer_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create Shipment details
            CFShipment::create([
                'cf_job_id' => $job->id,
                'bl_awb_number' => $validated['bl_awb_number'] ?? null,
                'vessel_flight_truck' => $validated['vessel_flight_truck'] ?? null,
                'voyage_number' => $validated['voyage_number'] ?? null,
                'shipping_line_airline' => $validated['shipping_line_airline'] ?? null,
                'origin_country' => $validated['origin_country'] ?? null,
                'port_of_loading' => $validated['port_of_loading'] ?? null,
                'port_of_discharge' => $validated['port_of_discharge'] ?? null,
                'terminal_icd' => $validated['terminal_icd'] ?? null,
                'final_destination' => $validated['final_destination'] ?? null,
                'eta' => $validated['eta'] ?? null,
            ]);

            // Create Cargo line
            $cargoLine = CFCargoLine::create([
                'cf_job_id' => $job->id,
                'commodity_description' => $validated['commodity_description'],
                'package_type' => $validated['package_type'] ?? 'packages',
                'package_count' => $validated['package_count'] ?? 1,
                'gross_weight_kg' => $validated['gross_weight_kg'] ?? 0,
                'cbm' => $validated['cbm'] ?? null,
                'hs_code' => $validated['hs_code'] ?? null,
                'customs_value' => $validated['customs_value'] ?? 0,
                'currency' => $validated['currency'] ?? 'USD',
            ]);

            // Create Container if provided
            if (!empty($validated['container_number'])) {
                CFContainer::create([
                    'cf_job_id' => $job->id,
                    'container_number' => strtoupper(trim($validated['container_number'])),
                    'size_type' => $validated['size_type'] ?? '40HC',
                    'seal_number' => $validated['seal_number'] ?? null,
                    'shipping_line' => $validated['shipping_line_airline'] ?? null,
                    'free_days' => $validated['free_days'] ?? 14,
                    'status' => 'at_terminal',
                ]);
            }

            // Populate mandatory document checklist
            $standardDocs = [
                ['type' => 'bill_of_lading', 'name' => 'Bill of Lading / Air Waybill', 'mandatory' => true],
                ['type' => 'commercial_invoice', 'name' => 'Commercial Invoice (attested)', 'mandatory' => true],
                ['type' => 'packing_list', 'name' => 'Detailed Packing List', 'mandatory' => true],
                ['type' => 'certificate_of_origin', 'name' => 'Certificate of Origin', 'mandatory' => true],
                ['type' => 'tbs_certificate', 'name' => 'TBS / PVoC Certificate', 'mandatory' => false],
            ];

            foreach ($standardDocs as $sd) {
                CFDocumentChecklist::create([
                    'cf_job_id' => $job->id,
                    'document_type' => $sd['type'],
                    'document_name' => $sd['name'],
                    'is_mandatory' => $sd['mandatory'],
                    'verification_status' => 'pending',
                ]);
            }

            // Initial Job Costing
            CFJobCosting::create([
                'cf_job_id' => $job->id,
                'currency' => 'TZS',
            ]);

            return redirect()->route('clearing-forwarding.jobs.show', $job->id)
                ->with('success', "Job {$job->job_number} created successfully.");
        });
    }

    /**
     * Centralized Electronic Job File (Show Hub)
     */
    public function show(CFJob $cfJob)
    {
        $cfJob->load([
            'customer',
            'caseOwner',
            'declarant',
            'shipment',
            'cargoLines.classification',
            'containers',
            'documents.verifier',
            'permits',
            'valuation.reviewer',
            'declarations.events.recorder',
            'chargeEstimates',
            'disbursements.authorizer',
            'transportOrders.trackingEvents',
            'trackingEvents.recorder',
            'deliveries.proofOfDelivery.verifier',
            'jobCosting.reconciler',
        ]);

        $users = User::select('id', 'name', 'email', 'type')->orderBy('name')->get();

        return Inertia::render('ClearingForwarding/Jobs/Show', [
            'job' => $cfJob,
            'users' => $users,
        ]);
    }

    /**
     * Update Job Status
     */
    public function updateStatus(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cfJob->update(['status' => $validated['status']]);

        // Auto-log tracking event
        CFTrackingEvent::create([
            'cf_job_id' => $cfJob->id,
            'milestone_code' => $validated['status'],
            'location_name' => 'Operations Center',
            'event_time' => now(),
            'remarks' => "Job status updated to: " . strtoupper(str_replace('_', ' ', $validated['status'])) . ($validated['notes'] ? " - {$validated['notes']}" : ''),
            'is_customer_visible' => true,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Job status updated successfully.');
    }

    /**
     * Verify or Reject Document in Checklist
     */
    public function verifyDocument(Request $request, CFDocumentChecklist $document)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected,waived,pending',
            'document_reference' => 'nullable|string|max:255',
            'is_original_received' => 'nullable|boolean',
            'rejection_reason' => 'nullable|string',
        ]);

        $document->update([
            'verification_status' => $validated['verification_status'],
            'document_reference' => $validated['document_reference'] ?? $document->document_reference,
            'is_original_received' => $validated['is_original_received'] ?? $document->is_original_received,
            'rejection_reason' => $validated['verification_status'] === 'rejected' ? $validated['rejection_reason'] : null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Document verification updated.');
    }

    /**
     * Save/Update Customs Declaration
     */
    public function storeDeclaration(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'customs_office' => 'required|string',
            'declaration_type' => 'required|string',
            'declaration_number' => 'nullable|string',
            'tancis_reference' => 'nullable|string',
            'regime_code' => 'nullable|string',
            'assessment_number' => 'nullable|string',
            'assessed_amount_tzs' => 'nullable|numeric',
            'payment_control_number' => 'nullable|string',
            'release_order_number' => 'nullable|string',
            'status' => 'required|string',
            'customs_channel' => 'nullable|string',
        ]);

        $declaration = $cfJob->declarations()->first();
        if ($declaration) {
            $declaration->update($validated);
        } else {
            $validated['cf_job_id'] = $cfJob->id;
            $validated['declarant_user_id'] = Auth::id();
            $declaration = CFDeclaration::create($validated);
        }

        // Auto update job status if declared / assessed / released
        if ($validated['status'] === 'released') {
            $cfJob->update(['status' => 'cargo_released']);
        } elseif ($validated['status'] === 'assessed') {
            $cfJob->update(['status' => 'assessed']);
        } elseif ($validated['status'] === 'submitted') {
            $cfJob->update(['status' => 'declaration_submitted']);
        }

        return back()->with('success', 'Customs declaration updated successfully.');
    }

    /**
     * Store Disbursement & trigger costing recalculation
     */
    public function storeDisbursement(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'payee_name' => 'required|string|max:255',
            'charge_category' => 'required|string',
            'description' => 'required|string',
            'payment_method' => 'required|string',
            'disbursed_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'payment_reference' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
        ]);

        $amountTzs = $validated['currency'] === 'USD' ? $validated['disbursed_amount'] * 2650 : $validated['disbursed_amount'];

        CFDisbursement::create([
            'cf_job_id' => $cfJob->id,
            'payee_name' => $validated['payee_name'],
            'charge_category' => $validated['charge_category'],
            'description' => $validated['description'],
            'payment_method' => $validated['payment_method'],
            'disbursed_amount' => $validated['disbursed_amount'],
            'currency' => $validated['currency'],
            'amount_tzs' => $amountTzs,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_date' => $validated['payment_date'],
            'is_reconciled' => true,
            'authorized_by' => Auth::id(),
        ]);

        // Recalculate job costing
        if ($cfJob->jobCosting) {
            $cfJob->jobCosting->recalculate();
        }

        return back()->with('success', 'Disbursement recorded and financials recalculated.');
    }

    /**
     * Store Transport Order
     */
    public function storeTransportOrder(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'transporter_type' => 'required|string',
            'carrier_name' => 'nullable|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_plate' => 'required|string|max:50',
            'pickup_location' => 'required|string|max:255',
            'delivery_destination' => 'required|string|max:255',
            'route_corridor' => 'nullable|string',
            'agreed_freight_cost' => 'nullable|numeric|min:0',
        ]);

        $order = CFTransportOrder::create([
            'cf_job_id' => $cfJob->id,
            'order_number' => CFTransportOrder::generateOrderNumber(),
            'transporter_type' => $validated['transporter_type'],
            'carrier_name' => $validated['carrier_name'] ?? 'Mutamark Fleet',
            'driver_name' => $validated['driver_name'],
            'driver_phone' => $validated['driver_phone'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'],
            'pickup_location' => $validated['pickup_location'],
            'delivery_destination' => $validated['delivery_destination'],
            'route_corridor' => $validated['route_corridor'] ?? null,
            'agreed_freight_cost' => $validated['agreed_freight_cost'] ?? 0,
            'currency' => 'TZS',
            'status' => 'dispatched',
            'dispatch_date' => now(),
        ]);

        $cfJob->update(['status' => 'in_transit']);

        // Log tracking event
        CFTrackingEvent::create([
            'cf_job_id' => $cfJob->id,
            'cf_transport_order_id' => $order->id,
            'milestone_code' => 'dispatched',
            'location_name' => $validated['pickup_location'],
            'event_time' => now(),
            'remarks' => "Cargo loaded onto {$validated['vehicle_plate']} driven by {$validated['driver_name']}. En route to {$validated['delivery_destination']}.",
            'is_customer_visible' => true,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', "Transport Order {$order->order_number} issued and vehicle dispatched.");
    }

    /**
     * Store In-Transit Tracking Event
     */
    public function storeTrackingEvent(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'milestone_code' => 'required|string',
            'location_name' => 'required|string',
            'remarks' => 'nullable|string',
            'is_customer_visible' => 'nullable|boolean',
        ]);

        CFTrackingEvent::create([
            'cf_job_id' => $cfJob->id,
            'milestone_code' => $validated['milestone_code'],
            'location_name' => $validated['location_name'],
            'event_time' => now(),
            'remarks' => $validated['remarks'] ?? null,
            'is_customer_visible' => $validated['is_customer_visible'] ?? true,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Milestone event logged.');
    }

    /**
     * Record Delivery and Electronic Proof of Delivery (POD)
     */
    public function storeDelivery(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'delivery_address' => 'required|string',
            'packages_delivered' => 'required|numeric|min:1',
            'cargo_condition' => 'required|string|in:sound_and_intact,shortage,damaged,seal_broken,disputed',
            'condition_notes' => 'nullable|string',
            'recipient_name' => 'required|string|max:255',
            'recipient_id_type' => 'nullable|string',
            'recipient_id_number' => 'nullable|string',
            'recipient_phone' => 'nullable|string',
            'signature_data' => 'nullable|string', // base64
        ]);

        return DB::transaction(function () use ($validated, $cfJob) {
            $delivery = CFDelivery::create([
                'cf_job_id' => $cfJob->id,
                'delivery_number' => CFDelivery::generateDeliveryNumber(),
                'delivery_date' => now()->toDateString(),
                'delivery_address' => $validated['delivery_address'],
                'packages_delivered' => $validated['packages_delivered'],
                'cargo_condition' => $validated['cargo_condition'],
                'condition_notes' => $validated['condition_notes'] ?? null,
                'status' => 'pod_confirmed',
            ]);

            $verificationHash = hash('sha256', $delivery->delivery_number . '|' . $validated['recipient_name'] . '|' . now()->toIso8601String());

            CFProofOfDelivery::create([
                'cf_delivery_id' => $delivery->id,
                'cf_job_id' => $cfJob->id,
                'recipient_name' => $validated['recipient_name'],
                'recipient_id_type' => $validated['recipient_id_type'] ?? 'national_id_nida',
                'recipient_id_number' => $validated['recipient_id_number'] ?? null,
                'recipient_phone' => $validated['recipient_phone'] ?? null,
                'signature_data' => $validated['signature_data'] ?? null,
                'signed_at' => now(),
                'official_stamp_received' => true,
                'verification_hash' => $verificationHash,
                'verified_by' => Auth::id(),
            ]);

            $cfJob->update(['status' => 'delivered']);

            // Milestone
            CFTrackingEvent::create([
                'cf_job_id' => $cfJob->id,
                'milestone_code' => 'delivered_pod',
                'location_name' => $validated['delivery_address'],
                'event_time' => now(),
                'remarks' => "Cargo handed over to {$validated['recipient_name']}. Condition: " . strtoupper($validated['cargo_condition']) . ". Signed POD archived.",
                'is_customer_visible' => true,
                'recorded_by' => Auth::id(),
            ]);

            return back()->with('success', "Delivery recorded and Proof of Delivery archived under {$delivery->delivery_number}.");
        });
    }

    /**
     * Financial Reconciliation & Customer Advance Allocation
     */
    public function reconcileCosting(Request $request, CFJob $cfJob)
    {
        $validated = $request->validate([
            'customer_advance_received' => 'required|numeric|min:0',
            'agency_fee_revenue' => 'nullable|numeric|min:0',
            'is_financially_reconciled' => 'nullable|boolean',
        ]);

        $costing = $cfJob->jobCosting ?: new CFJobCosting(['cf_job_id' => $cfJob->id]);
        $costing->customer_advance_received = $validated['customer_advance_received'];
        if (isset($validated['agency_fee_revenue'])) {
            $costing->agency_fee_revenue = $validated['agency_fee_revenue'];
        }
        $costing->is_financially_reconciled = $validated['is_financially_reconciled'] ?? false;
        if ($costing->is_financially_reconciled) {
            $costing->reconciled_by = Auth::id();
            $costing->reconciled_at = now();
            if ($cfJob->status === 'delivered') {
                $cfJob->update(['status' => 'completed', 'closed_at' => now()]);
            }
        }
        $costing->save();
        $costing->recalculate();

        return back()->with('success', 'Job costing reconciled successfully.');
    }

    /**
     * Containers Demurrage Monitor View
     */
    public function containers(Request $request)
    {
        $containers = CFContainer::with(['job.customer', 'job.shipment'])
            ->latest('id')
            ->paginate(20);

        return Inertia::render('ClearingForwarding/Containers/Index', [
            'containers' => $containers,
        ]);
    }

    /**
     * Customs Declarations Monitor View
     */
    public function declarations(Request $request)
    {
        $declarations = CFDeclaration::with(['job.customer', 'job.shipment', 'declarant'])
            ->latest('id')
            ->paginate(20);

        return Inertia::render('ClearingForwarding/Declarations/Index', [
            'declarations' => $declarations,
        ]);
    }

    /**
     * Transport & Haulage Monitor View
     */
    public function transport(Request $request)
    {
        $orders = CFTransportOrder::with(['job.customer', 'job.shipment', 'deliveries.proofOfDelivery'])
            ->latest('id')
            ->paginate(20);

        return Inertia::render('ClearingForwarding/Transport/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Print View for C&F Documents (Quotation, Transport Order, Delivery Note, POD, Final Account)
     */
    public function printDocument(CFJob $cfJob, string $type)
    {
        $cfJob->load([
            'customer',
            'shipment',
            'cargoLines.classification',
            'containers',
            'declarations',
            'disbursements',
            'transportOrders',
            'deliveries.proofOfDelivery',
            'jobCosting',
        ]);

        return Inertia::render('ClearingForwarding/Print', [
            'job' => $cfJob,
            'type' => $type,
        ]);
    }
}

