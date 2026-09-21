<?php

namespace App\Exceptions;

use App\Models\Drinker;
use RuntimeException;

class InsufficientFundsException extends RuntimeException
{
    public function __construct(public readonly Drinker $drinker, public readonly float $amount)
    {
        parent::__construct("{$drinker->name} does not have sufficient funds for this transfer.");
    }
}
