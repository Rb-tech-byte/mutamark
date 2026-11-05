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

