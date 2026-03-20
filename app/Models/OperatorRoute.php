<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatorRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'operator_code', 'recharge_type', 'api_provider',
        'api_endpoint', 'api_config', 'priority', 'success_rate',
        'timeout_seconds', 'max_retries', 'is_active',
        'min_amount', 'max_amount',
    ];

    protected $hidden = ['api_config'];

    protected function casts(): array
    {
        return [
            'api_config'  => 'encrypted:array',
            'is_active'   => 'boolean',
            'min_amount'  => 'decimal:2',
            'max_amount'  => 'decimal:2',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(RechargeAttempt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOperator($query, string $code, string $type = 'prepaid')
    {
        return $query->where('operator_code', $code)
                     ->where('recharge_type', $type)
                     ->orderBy('priority');
    }
}
