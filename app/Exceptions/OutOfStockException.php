<?php

namespace App\Exceptions;

use App\Models\Drink;
use RuntimeException;

class OutOfStockException extends RuntimeException
{
    public function __construct(public readonly Drink $drink)
    {
        parent::__construct("{$drink->name} is out of stock.");
    }
}
