<?php

namespace App\Enums;

enum TransactionType: string
{
    case Purchase = 'purchase';
    case Deposit = 'deposit';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Deposit => 'Deposit',
            self::Adjustment => 'Adjustment',
            self::Transfer => 'Transfer',
        };
    }
}
