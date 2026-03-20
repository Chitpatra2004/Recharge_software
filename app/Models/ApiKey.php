<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'key_prefix', 'key_hash',
        'scopes', 'ip_whitelist', 'last_used_at',
        'last_used_ip', 'request_count', 'expires_at', 'is_active',
    ];

    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'scopes'       => 'array',
            'ip_whitelist' => 'array',
            'last_used_at' => 'datetime',
            'expires_at'   => 'datetime',
            'is_active'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function hasScope(string $scope): bool
    {
        // FIX M1: deny-by-default — a key with no scopes has NO access.
        // Previously this returned true for empty scopes (all-access by accident).
        // Now scopes must be explicitly granted. Use ['*'] to grant all scopes.
        if (empty($this->scopes)) {
            return false;
        }

        // Wildcard '*' grants all scopes (for super-admin / internal keys)
        if (in_array('*', $this->scopes, true)) {
            return true;
        }

        return in_array($scope, $this->scopes, true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn ($q) => $q->whereNull('expires_at')
                                          ->orWhere('expires_at', '>', now()));
    }

    public static function findByRawKey(string $rawKey): ?self
    {
        return static::active()
            ->where('key_hash', hash('sha256', $rawKey))
            ->first();
    }
}
