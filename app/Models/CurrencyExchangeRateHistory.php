<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyExchangeRateHistory extends Model
{
    use HasFactory;

    protected $table = 'currency_exchange_rate_histories';

    protected $fillable = [
        'currency_code',
        'rate_date',
        'exchange_rate',
        'buy_rate',
        'sell_rate',
        'bot_indicative_rate',
        'source',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rate_date' => 'date',
            'exchange_rate' => 'decimal:6',
            'buy_rate' => 'decimal:6',
            'sell_rate' => 'decimal:6',
            'bot_indicative_rate' => 'decimal:6',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }
}
