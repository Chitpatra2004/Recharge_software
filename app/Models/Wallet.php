<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'credit_limit',
        'reserved_balance', 'status',
    ];

    protected function casts(): array
    {
        return [
            'balance'          => 'decimal:2',
            'credit_limit'     => 'decimal:2',
            'reserved_balance' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Available balance = balance - reserved_balance + credit_limit.
     *
     * FIX M2: credit_limit was missing here but included in the API response
     * (BuyerRechargeController::balance()).  The two were inconsistent: the API
     * showed a higher available balance than what the service would actually
     * allow for a recharge, causing confusing 422 errors for users with credit.
     *
     * Example: balance=50, reserved=0, credit_limit=500 → available=550.
     * The floor check in WalletRepository::debit() ensures balance never drops
     * below -credit_limit even with this wider availability window.
     */
    public function availableBalance(): float
    {
        return (float) $this->balance
             - (float) $this->reserved_balance
             + (float) ($this->credit_limit ?? 0);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->availableBalance() >= $amount;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
