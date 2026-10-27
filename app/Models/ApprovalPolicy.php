<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_type',
        'tier_level',
        'tier_name',
        'min_amount',
        'max_amount',
        'required_role',
        'approver_id',
        'requires_digital_signature',
        'enforce_segregation_of_duties',
        'is_active',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'tier_level' => 'integer',
        'requires_digital_signature' => 'boolean',
        'enforce_segregation_of_duties' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDocument($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Find matching policy tier for given document type and amount
     */
    public static function matchTier(string $documentType, float $amount)
    {
        return static::active()
            ->where('document_type', $documentType)
            ->where('min_amount', '<=', $amount)
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')
                  ->orWhere('max_amount', '>=', $amount);
            })
            ->orderBy('tier_level', 'asc')
            ->first();
    }
}
