<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as ModelsPermission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
            'name',
            'email',
            'mobile_no',
            'email_verified_at',
            'password',
            'type',
            'avatar',
            'lang',
            'layout_direction',
            'active_plan',
            'plan_expire_date',
            'is_trial_done',
            'total_user',
            'commission_amount',
            'storage_limit',
            'is_disable',
            'is_enable_login',
            'creator_id',
            'created_by',
            'active_status',
            'last_seen_at',
            'slug',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_last_used_at',
        'active_plan', 'plan_expire_date', 'is_trial_done', 'total_user', 'storage_limit',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'is_trial_done' => 'integer',
        ];
    }

    public static function newTwoFactorSecret(): string
    {
        $bits = '';
        foreach (str_split(random_bytes(20)) as $byte) $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $secret = '';
        foreach (str_split($bits, 5) as $chunk) $secret .= $alphabet[bindec($chunk)];
        return $secret;
    }

    public static function totp(string $secret, int $counter): string
    {
        $bits = ''; $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        foreach (str_split($secret) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) throw new \InvalidArgumentException('Invalid authenticator secret.');
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }
        $key = '';
        foreach (str_split($bits, 8) as $chunk) if (strlen($chunk) === 8) $key .= chr(bindec($chunk));
        $hash = hash_hmac('sha1', pack('N2', 0, $counter), $key, true);
        $offset = ord($hash[19]) & 15;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;
        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function verifySecondFactor(string $code, bool $allowRecovery = true): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($code, $allowRecovery) {
            $user = static::lockForUpdate()->findOrFail($this->id);
            if (!$user->two_factor_secret) return false;
            if (preg_match('/^\d{6}$/', $code)) {
                $step = intdiv(time(), 30);
                foreach ([$step - 1, $step, $step + 1] as $counter) {
                    if ($counter > (int) $user->two_factor_last_used_at && hash_equals(self::totp($user->two_factor_secret, $counter), $code)) {
                        $user->forceFill(['two_factor_last_used_at' => $counter])->save();
                        return true;
                    }
                }
            } elseif ($allowRecovery) {
                $codes = $user->two_factor_recovery_codes ?? [];
                foreach ($codes as $index => $hash) if (\Illuminate\Support\Facades\Hash::check($code, $hash)) {
                    unset($codes[$index]); $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save(); return true;
                }
            }
            return false;
        }, 3);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = static::generateUniqueSlug($user->name);
            }
        });
    }

    public static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public static $superadmin_activated_module = [
        'ProductService',
        'LandingPage',
    ];

    public  $not_emp_type = [
        'superadmin',
        'company',
        'client',
        'vendor',
        'doctor',
        'student',
        'parent',
        'tenant',
        'salesagent',
        'driver',
        'farmer',
        'gym member',
        'gym trainer',
        'resident',
        'hr',
        'construction-partner',
        'driving student',
        'advocate',
        'case initiator'
    ];

    public function scopeEmp($query, $additionalTypes = [], $includeTypes = [])
    {
        $excludeTypes = array_diff(array_merge($this->not_emp_type, $additionalTypes), $includeTypes);
        return $query->whereNotIn('type', $excludeTypes);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function CompanySetting($user_id)
    {
        $company_settings = [
            // Brand Settings
            'logo_light' => admin_setting('logo_light'),
            'logo_dark' => admin_setting('logo_dark'),
            'favicon' => admin_setting('favicon'),
            'titleText' => admin_setting('titleText'),
            'footerText' => admin_setting('footerText'),
            'sidebarVariant' => admin_setting('sidebarVariant'),
            'sidebarStyle' => admin_setting('sidebarStyle'), 
            'themeMode' => admin_setting('themeMode'),
            'themeColor' => admin_setting('themeColor'),
            'customColor' => admin_setting('customColor'),

            // System Settings
            'defaultLanguage' => admin_setting('defaultLanguage'),
            'dateFormat' => admin_setting('dateFormat'),
            'timeFormat' => admin_setting('timeFormat'),
            'calendarStartDay' => admin_setting('calendarStartDay'),

            // Currency Settings
            'defaultCurrency' => admin_setting('defaultCurrency'),
            'currencySymbol' => admin_setting('currencySymbol'),
            'currency_format' => admin_setting('currency_format'),
            'decimalFormat' => admin_setting('decimalFormat'),
            'decimalSeparator' => admin_setting('decimalSeparator'),
            'thousandsSeparator' => admin_setting('thousandsSeparator'),
            'floatNumber' => admin_setting('floatNumber'),
            'currencySymbolSpace' => admin_setting('currencySymbolSpace'),
            'currencySymbolPosition' => admin_setting('currencySymbolPosition'),
        ];
        foreach($company_settings as $key => $value){
            if (!company_setting($key,$user_id)) {
                setSetting($key, $value, $user_id);
            }
        }
    }

    public static function MakeRole($userId)
    {
        // Create staff role
        $staffRole = Role::where('name','staff')->where('created_by',$userId)->where('guard_name','web')->first();
        if(empty($staffRole))
        {
            $staffRole                   = new Role();
            $staffRole->name             = 'staff';
            $staffRole->guard_name       = 'web';
            $staffRole->label            = 'Staff';
            $staffRole->editable         = false;
            $staffRole->created_by       = $userId;
            $staffRole->save();

            $permissions = ModelsPermission::whereIn('name', [
                'manage-dashboard',
                'manage-media',
                'manage-own-media',
                'create-media',
                'download-media',
                'delete-media',
                'manage-media-directories',
                'manage-own-media-directories',
                'create-media-directories',
                'edit-media-directories',
                'delete-media-directories',
                'manage-profile',
                'edit-profile',
                'change-password-profile',
                'manage-messenger',
                'send-messages',
                'view-messages',
                'toggle-favorite-messages',
                'toggle-pinned-messages'
            ])->get();

            $staffRole->givePermissionTo($permissions);
        }

        // Create client role
        $clientRole = Role::where('name','client')->where('created_by',$userId)->where('guard_name','web')->first();
        if(empty($clientRole))
        {
            $clientRole                   = new Role();
            $clientRole->name             = 'client';
            $clientRole->guard_name       = 'web';
            $clientRole->label            = 'Client';
            $clientRole->editable         = false;
            $clientRole->created_by       = $userId;
            $clientRole->save();

            $permissions = ModelsPermission::whereIn('name', [
                'manage-dashboard',
                'manage-media',
                'manage-own-media',
                'create-media',
                'download-media',
                'delete-media',
                'manage-media-directories',
                'manage-own-media-directories',
                'create-media-directories',
                'edit-media-directories',
                'delete-media-directories',
                'manage-profile',
                'edit-profile',
                'change-password-profile',
                'manage-messenger',
                'send-messages',
                'view-messages',
                'toggle-favorite-messages',
                'toggle-pinned-messages',
                'manage-sales-invoices',
                'manage-own-sales-invoices',
                'view-sales-invoices',
                'print-sales-invoices',
                'manage-sales-return-invoices',
                'manage-own-sales-return-invoices',
                'view-sales-return-invoices',
                'manage-sales-proposals',
                'manage-own-sales-proposals',
                'view-sales-proposals',
                'print-sales-proposals',
                'accept-sales-proposals',
                'reject-sales-proposals',
            ])->get();

            $clientRole->givePermissionTo($permissions);
        }

        // Create vendor role
        $vendorRole = Role::where('name','vendor')->where('created_by',$userId)->where('guard_name','web')->first();
        if(empty($vendorRole))
        {
            $vendorRole                   = new Role();
            $vendorRole->name             = 'vendor';
            $vendorRole->guard_name       = 'web';
            $vendorRole->label            = 'Vendor';
            $vendorRole->editable         = false;
            $vendorRole->created_by       = $userId;
            $vendorRole->save();

            $permissions = ModelsPermission::whereIn('name', [
                'manage-dashboard',
                'manage-media',
                'manage-own-media',
                'create-media',
                'download-media',
                'delete-media',
                'manage-media-directories',
                'manage-own-media-directories',
                'create-media-directories',
                'edit-media-directories',
                'delete-media-directories',
                'manage-profile',
                'edit-profile',
                'change-password-profile',
                'manage-messenger',
                'send-messages',
                'view-messages',
                'toggle-favorite-messages',
                'toggle-pinned-messages',
                'manage-purchase-invoices',
                'manage-own-purchase-invoices',
                'view-purchase-invoices',
                'print-purchase-invoices',
                'manage-purchase-return-invoices',
                'manage-own-purchase-return-invoices',
                'view-purchase-return-invoices',
            ])->get();

            $vendorRole->givePermissionTo($permissions);
        }
    }
}
