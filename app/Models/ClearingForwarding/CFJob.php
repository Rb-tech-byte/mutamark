<?php

namespace App\Models\ClearingForwarding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CFJob extends Model
{
    use HasFactory;

    protected $table = 'cf_jobs';

    protected $fillable = [
        'job_number',
        'customer_id',
        'consignee_name',
        'consignor_name',
        'service_type',
        'transport_mode',
        'customs_regime',
        'status',
        'priority',
        'case_owner_id',
        'declarant_id',
        'branch_id',
        'workspace_id',
        'created_by',
        'opened_at',
        'closed_at',
        'customer_reference',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function caseOwner()
    {
        return $this->belongsTo(User::class, 'case_owner_id');
    }

    public function declarant()
    {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    public function shipment()
    {
        return $this->hasOne(CFShipment::class, 'cf_job_id');
    }

    public function cargoLines()
    {
        return $this->hasMany(CFCargoLine::class, 'cf_job_id');
    }

    public function containers()
    {
        return $this->hasMany(CFContainer::class, 'cf_job_id');
    }

    public function documents()
    {
        return $this->hasMany(CFDocumentChecklist::class, 'cf_job_id');
    }

    public function permits()
    {
        return $this->hasMany(CFPermit::class, 'cf_job_id');
    }

    public function classifications()
    {
        return $this->hasMany(CFClassification::class, 'cf_job_id');
    }

    public function valuation()
    {
        return $this->hasOne(CFValuation::class, 'cf_job_id');
    }

    public function declarations()
    {
        return $this->hasMany(CFDeclaration::class, 'cf_job_id');
    }

    public function chargeEstimates()
    {
        return $this->hasMany(CFChargeEstimate::class, 'cf_job_id');
    }

    public function disbursements()
    {
        return $this->hasMany(CFDisbursement::class, 'cf_job_id');
    }

    public function transportOrders()
    {
        return $this->hasMany(CFTransportOrder::class, 'cf_job_id');
    }

    public function trackingEvents()
    {
        return $this->hasMany(CFTrackingEvent::class, 'cf_job_id')->orderBy('event_time', 'asc');
    }

    public function deliveries()
    {
        return $this->hasMany(CFDelivery::class, 'cf_job_id');
    }

    public function jobCosting()
    {
        return $this->hasOne(CFJobCosting::class, 'cf_job_id');
    }

    public function borderMovements()
    {
        return $this->hasMany(CFBorderMovement::class, 'cf_job_id');
    }

    public function freightBookings()
    {
        return $this->hasMany(CFFreightBooking::class, 'cf_job_id');
    }

    /**
     * Compute live completion across the 16-step C&F ERP workflow.
     */
    public function getWorkflowSteps(): array
    {
        $hasUploadedDocs = $this->documents->whereNotNull('file_path')->count() > 0;
        $hasVerifiedDocs = $this->documents->where('verification_status', 'verified')->count() > 0;
        $hasQuotation = $this->chargeEstimates->count() > 0;
        $hasCustomsClear = $this->declarations->whereIn('status', ['assessed', 'customs_released', 'released'])->count() > 0;
        $hasTransportPlan = $this->transportOrders->count() > 0 || $this->freightBookings->count() > 0;
        $hasDispatch = $this->transportOrders->whereIn('status', ['dispatched', 'in_transit', 'delivered'])->count() > 0
            || in_array($this->status, ['in_transit', 'delivered', 'completed'], true);
        $needsBorder = in_array($this->service_type, ['cross_border', 'transit', 'export'], true)
            || !empty($this->shipment?->border_of_entry);
        $hasBorder = !$needsBorder || $this->borderMovements->count() > 0;
        $hasPod = $this->deliveries->filter(fn ($d) => $d->proofOfDelivery !== null || $d->status === 'delivered')->count() > 0;
        $hasBilling = ($this->jobCosting?->total_service_revenue ?? 0) > 0 || ($this->jobCosting?->customer_advance_received ?? 0) > 0;

        return [
            ['step' => 1, 'label' => 'Customer Registration', 'done' => $this->customer_id !== null],
            ['step' => 2, 'label' => 'Job Request', 'done' => true],
            ['step' => 3, 'label' => 'Document Submission', 'done' => $hasUploadedDocs],
            ['step' => 4, 'label' => 'Document Verification', 'done' => $hasVerifiedDocs],
            ['step' => 5, 'label' => 'Job Creation', 'done' => !empty($this->job_number)],
            ['step' => 6, 'label' => 'Quotation', 'done' => $hasQuotation],
            ['step' => 7, 'label' => 'Customer Approval', 'done' => !in_array($this->status, ['draft', 'documents_pending'], true)],
            ['step' => 8, 'label' => 'Customs Clearing', 'done' => $hasCustomsClear],
            ['step' => 9, 'label' => 'Transport Planning', 'done' => $hasTransportPlan],
            ['step' => 10, 'label' => 'Loading & Dispatch', 'done' => $hasDispatch],
            ['step' => 11, 'label' => 'Border Management', 'done' => $hasBorder],
            ['step' => 12, 'label' => 'Cargo Transportation', 'done' => $this->trackingEvents->count() > 0 || $hasDispatch],
            ['step' => 13, 'label' => 'Delivery & POD', 'done' => $hasPod],
            ['step' => 14, 'label' => 'Invoicing & Payment', 'done' => $hasBilling],
            ['step' => 15, 'label' => 'Job Closure', 'done' => $this->status === 'completed'],
            ['step' => 16, 'label' => 'Reporting', 'done' => $this->status === 'completed' || $this->jobCosting?->reconciled_at !== null],
        ];
    }

    /**
     * Validate missing mandatory documents & duplicate filenames.
     */
    public function getDocumentValidationSummary(): array
    {
        $missingMandatory = $this->documents
            ->filter(fn ($d) => ($d->is_mandatory ?? false) && empty($d->file_path))
            ->pluck('document_type')
            ->values()
            ->all();

        $uploadedNames = $this->documents
            ->filter(fn ($d) => !empty($d->file_path))
            ->map(fn ($d) => strtolower(basename((string) $d->file_path)))
            ->filter();
        $duplicates = $uploadedNames->countBy()->filter(fn ($c) => $c > 1)->keys()->values()->all();

        return [
            'missing_mandatory' => $missingMandatory,
            'duplicate_files' => $duplicates,
            'verified_count' => $this->documents->where('verification_status', 'verified')->count(),
            'total_count' => $this->documents->count(),
        ];
    }

    public static function generateJobNumber(): string
    {
        $year = date('Y');
        $prefix = "CF-{$year}-";
        $latest = static::where('job_number', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->job_number, strlen($prefix));
            $nextSeq = str_pad($lastSeq + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '00001';
        }

        return $prefix . $nextSeq;
    }
}

