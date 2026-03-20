<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'category', 'logo_url',
        'prepaid_enabled', 'postpaid_enabled', 'is_active',
        'min_amount', 'max_amount', 'commission_rate',
        'country_code', 'circles',
    ];

    protected function casts(): array
    {
        return [
            'prepaid_enabled'  => 'boolean',
            'postpaid_enabled' => 'boolean',
            'is_active'        => 'boolean',
            'min_amount'       => 'decimal:2',
            'max_amount'       => 'decimal:2',
            'commission_rate'  => 'decimal:2',
            'circles'          => 'array',
        ];
    }

    public function routes(): HasMany
    {
        return $this->hasMany(OperatorRoute::class);
    }

    public function rechargeTransactions(): HasMany
    {
        return $this->hasMany(RechargeTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
