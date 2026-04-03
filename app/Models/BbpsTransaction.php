<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BbpsTransaction extends Model
{
    protected $fillable = [
        'user_id','idempotency_key','biller_category','biller_id','biller_name',
        'consumer_number','amount','balance_before','balance_after',
        'status','txn_id','biller_ref_id','failure_reason','bill_details',
        'processed_at','next_retry_at',
    ];

    protected $casts = [
        'amount'         => 'float',
        'balance_before' => 'float',
        'balance_after'  => 'float',
        'bill_details'   => 'array',
        'processed_at'   => 'datetime',
        'next_retry_at'  => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
