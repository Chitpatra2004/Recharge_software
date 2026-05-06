<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerOperatorCommission extends Model
{
    protected $fillable = [
        'user_id',
        'operator_id',
        'operator_code',
        'commission',
        'commission_type',
        'api1',
        'limit_txn',
        'limit_amount',
        'blocked_amounts',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission' => 'decimal:3',
            'limit_txn' => 'integer',
            'limit_amount' => 'decimal:2',
            'is_active'  => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
