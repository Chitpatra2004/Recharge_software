<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RechargeTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'buyer_id', 'idempotency_key',
        'mobile', 'operator_code', 'circle', 'recharge_type',
        'amount', 'commission', 'net_amount', 'status',
        'operator_ref', 'api_ref', 'operator_response',
        'failure_reason', 'retry_count', 'processed_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'amount'            => 'decimal:2',
            'commission'        => 'decimal:2',
            'net_amount'        => 'decimal:2',
            'operator_response' => 'array',
            'processed_at'      => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(RechargeAttempt::class);
    }

    public function walletTransactions(): MorphMany
    {
        return $this->morphMany(WalletTransaction::class, 'reference');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isTerminal(): bool
    {
        return in_array($this->status, ['success', 'failed', 'refunded']);
    }

    public function canRetry(): bool
    {
        return ! $this->isTerminal()
            && $this->retry_count < config('recharge.max_retries', 3);
    }
}
