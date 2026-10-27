<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class FinancialAuditLog extends Model
{
    use HasFactory;

    protected $table = 'financial_audit_logs';

    protected $fillable = [
        'audit_number',
        'payload_hash',
        'event_type',
        'module',
        'reference_type',
        'reference_id',
        'reference_number',
        'actor_id',
        'actor_name',
        'actor_role',
        'ip_address',
        'user_agent',
        'action',
        'details',
        'old_values',
        'new_values',
        'severity',
        'previous_hash',
        'current_hash',
        'is_verified',
        'created_by',
        'creator_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Generate sequential audit reference AUD-YYYY-XXXXX
     */
    public static function generateAuditNumber(): string
    {
        $year = date('Y');
        $prefix = "AUD-{$year}-";
        $lastLog = self::where('audit_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLog) {
            $lastNum = (int) substr($lastLog->audit_number, -5);
            $nextNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '00001';
        }

        return "{$prefix}{$nextNum}";
    }

    /**
     * Compute cryptographic SHA-256 hash chaining to guarantee audit immutability.
     */
    public static function computeHash(string $auditNumber, string $eventType, string $refNumber, string $actor, string $timestamp, ?string $prevHash, ?string $payloadHash = null): string
    {
        $payload = implode('|', [
            $auditNumber,
            $eventType,
            $refNumber,
            $actor,
            $timestamp,
            $prevHash ?? 'GENESIS_HASH_MUTAMARK_ERP_2026',
        ]);

        return hash('sha256', $payload.($payloadHash ? '|'.$payloadHash : ''));
    }

    public static function payloadHash(array $data): string
    {
        $fields = ['module','reference_type','reference_id','actor_id','actor_role','ip_address','user_agent','action','details','old_values','new_values','severity'];
        $payload = [];
        foreach ($fields as $field) $payload[$field] = $data[$field] ?? null;
        foreach ($payload as $field => $value) if ($value !== null && !is_array($value)) $payload[$field] = (string) $value;
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Audit records are immutable. Append a correction instead.'));
        static::deleting(fn () => throw new \LogicException('Audit history must be retained.'));
    }

    /**
     * Record an immutable audit log entry.
     */
    public static function record(array $data): self
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
        $user = Auth::user();
        $actorId = $data['actor_id'] ?? $user?->id;
        $actorName = $data['actor_name'] ?? $user?->name ?? 'System Automated Engine';
        $actorRole = $data['actor_role'] ?? $user?->type ?? 'System';
        $ip = $data['ip_address'] ?? Request::ip() ?? '127.0.0.1';
        $ua = $data['user_agent'] ?? Request::userAgent() ?? 'Mutamark Engine Client';

        $auditNumber = \App\Services\DocumentNumberService::next('AUD');
        $lastLog = self::orderBy('id', 'desc')->first();
        $prevHash = $lastLog?->current_hash ?? 'GENESIS_HASH_MUTAMARK_ERP_2026';
        $now = now()->toDateTimeString();

        $data += ['actor_id' => $actorId, 'actor_role' => $actorRole, 'ip_address' => $ip, 'user_agent' => $ua, 'severity' => 'info'];
        $payloadHash = self::payloadHash($data);
        $currentHash = self::computeHash(
            $auditNumber,
            $data['event_type'],
            $data['reference_number'],
            $actorName,
            $now,
            $prevHash,
            $payloadHash
        );

        $creatorId = Auth::check() ? (creatorId() ?: 2) : ($user?->created_by ?: 2);

        return self::create([
            'audit_number' => $auditNumber,
            'payload_hash' => $payloadHash,
            'event_type' => $data['event_type'],
            'module' => $data['module'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reference_number' => $data['reference_number'],
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'actor_role' => $actorRole,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'action' => $data['action'],
            'details' => $data['details'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'severity' => $data['severity'] ?? 'info',
            'previous_hash' => $prevHash,
            'current_hash' => $currentHash,
            'is_verified' => true,
            'created_by' => $creatorId,
            'creator_id' => $creatorId,
        ]);
        }, 3);
    }
}
