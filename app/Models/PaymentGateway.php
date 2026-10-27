<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;
    protected $hidden = ['credentials', 'webhook_secret'];

    protected $fillable = [
        'gateway_key',
        'name',
        'description',
        'is_active',
        'is_default',
        'mode',
        'supported_currencies',
        'credentials',
        'transaction_fee_percentage',
        'transaction_fee_flat',
        'webhook_secret',
        'instructions',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'supported_currencies' => 'array',
        'credentials' => 'encrypted:array',
        'webhook_secret' => 'encrypted',
        'transaction_fee_percentage' => 'decimal:2',
        'transaction_fee_flat' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway_key', 'gateway_key');
    }

    /**
     * Compute fee for transaction amount
     */
    public function calculateFee(float $amount): float
    {
        $pctFee = ($amount * ((float) $this->transaction_fee_percentage)) / 100.0;
        return round($pctFee + (float) $this->transaction_fee_flat, 2);
    }

    /**
     * Return credentials with sensitive keys masked for UI display
     */
    public function getMaskedCredentials(): array
    {
        $creds = ($this->credentials ?? []) + ['bank_account_id' => ''];
        $masked = [];
        $sensitiveKeys = ['api_secret', 'secret_key', 'consumer_secret', 'webhook_secret', 'password'];

        foreach ($creds as $k => $v) {
            if ((in_array(strtolower($k), $sensitiveKeys) || preg_match('/secret|password|token|api_key|consumer_key/i', $k)) && !empty($v)) {
                $masked[$k] = '••••••••';
            } else {
                $masked[$k] = $v;
            }
        }
        return $masked;
    }
}
