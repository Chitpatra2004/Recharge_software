<?php

namespace App\Events;

use App\Models\RechargeTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RechargeInitiated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly RechargeTransaction $transaction) {}
}
