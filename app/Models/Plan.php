<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Classes\Module;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'number_of_users',
        'custom_plan',
        'status',
        'free_plan',
        'modules',
        'package_price_yearly',
        'package_price_monthly',
        'price_per_user_monthly',
        'price_per_user_yearly',
        'storage_limit',
        'price_per_storage_monthly',
        'price_per_storage_yearly',
        'trial',
        'trial_days',
        'created_by',
    ];

    protected $casts = [
        'custom_plan' => 'boolean',
        'status' => 'boolean',
        'free_plan' => 'boolean',
        'trial' => 'boolean',
        'modules' => 'array',
        'package_price_yearly' => 'decimal:2',
        'package_price_monthly' => 'decimal:2',
        'price_per_user_monthly' => 'decimal:2',
        'price_per_user_yearly' => 'decimal:2',
        'storage_limit' => 'integer',
        'price_per_storage_monthly' => 'decimal:2',
        'price_per_storage_yearly' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('custom_plan', false);
    }

    public function scopeCustom($query)
    {
        return $query->where('custom_plan', true);
    }

    public function getAvailableModulesForUser($userId)
    {
        return self::getUserSubscriptionModules($userId);
    }

    public static function getUserSubscriptionModules($userId = null)
    {
        $user = $userId ? User::find($userId) : auth()->user();

        if (!$user) {
            return [];
        }

        return (new Module())->allEnabled();
    }
}