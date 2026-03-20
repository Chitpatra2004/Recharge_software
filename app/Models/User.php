<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'mobile', 'password',
        'role', 'status', 'api_key', 'ip_whitelist',
        'commission_rate', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'api_key'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'commission_rate'   => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function rechargeTransactions(): HasMany
    {
        return $this->hasMany(RechargeTransaction::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function buyers(): HasMany
    {
        return $this->hasMany(Buyer::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sellerIntegrationRequests(): HasMany
    {
        return $this->hasMany(SellerIntegrationRequest::class);
    }

    public function latestIntegration(): HasOne
    {
        return $this->hasOne(SellerIntegrationRequest::class)->latestOfMany();
    }

    public function sellerPaymentRequests(): HasMany
    {
        return $this->hasMany(SellerPaymentRequest::class);
    }

    public function sellerGstInvoices(): HasMany
    {
        return $this->hasMany(SellerGstInvoice::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function allowedIps(): array
    {
        if (empty($this->ip_whitelist)) {
            return [];
        }
        return array_map('trim', explode(',', $this->ip_whitelist));
    }
}
