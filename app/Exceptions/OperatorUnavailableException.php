<?php

namespace App\Exceptions;

use Exception;

class OperatorUnavailableException extends Exception
{
    public function __construct(string $operatorCode)
    {
        parent::__construct("No active operator route available for: {$operatorCode}", 503);
    }
}
