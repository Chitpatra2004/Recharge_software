<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(float $required, float $available)
    {
        parent::__construct(
            "Insufficient balance. Required: {$required}, Available: {$available}",
            422
        );
    }
}
