<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceReturn extends Model
{
    protected $fillable = [
        'return_number',
        'return_date',
        'customer_id',
        'warehouse_id',
        'original_invoice_id',
        'reason',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceReturnItem::class, 'return_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customerDetails(): BelongsTo
    {
        return $this->belongsTo(\Workdo\Account\Models\Customer::class, 'customer_id', 'user_id');
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'original_invoice_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = static::generateReturnNumber();
            }
        });
    }

    public static function generateReturnNumber(): string
    {
        $year = date('Y');
        $lastReturn = static::where(function ($q) use ($year) {
                $q->where('return_number', 'like', "CN-{$year}-%")
                  ->orWhere('return_number', 'like', "SR-{$year}-%");
            })
            ->where('created_by', creatorId())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReturn && preg_match('/-(\d+)$/', $lastReturn->return_number, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return "CN-{$year}-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}