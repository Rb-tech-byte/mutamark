<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'buy_rate',
        'sell_rate',
        'bot_indicative_rate',
        'is_base',
        'is_active',
        'auto_sync',
        'last_synced_at',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'buy_rate' => 'decimal:6',
            'sell_rate' => 'decimal:6',
            'bot_indicative_rate' => 'decimal:6',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'auto_sync' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CurrencyExchangeRateHistory::class, 'currency_code', 'code');
    }
}
