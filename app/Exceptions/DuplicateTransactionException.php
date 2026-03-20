<?php

namespace App\Exceptions;

use App\Models\RechargeTransaction;
use Exception;

class DuplicateTransactionException extends Exception
{
    public function __construct(public readonly RechargeTransaction $existing)
    {
        parent::__construct('Duplicate transaction detected.', 409);
    }
}
