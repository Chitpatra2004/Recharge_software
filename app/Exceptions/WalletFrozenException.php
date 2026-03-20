<?php

namespace App\Exceptions;

use Exception;

class WalletFrozenException extends Exception
{
    public function __construct()
    {
        parent::__construct('Your wallet is currently frozen. Please contact support.', 403);
    }
}
