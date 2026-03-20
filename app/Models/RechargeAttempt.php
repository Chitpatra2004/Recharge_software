<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeAttempt extends Model
{
    protected $fillable = [
        'recharge_transaction_id', 'operator_route_id', 'attempt_number',
        'status', 'request_url', 'request_payload', 'response_payload',
        'response_code', 'duration_ms', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'request_payload'  => 'array',
            'response_payload' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(RechargeTransaction::class, 'recharge_transaction_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(OperatorRoute::class, 'operator_route_id');
    }
}
