<?php

namespace App\Support;

/**
 * The application stores money as decimal amounts of the main currency unit,
 * while the Space-Market API speaks integer cents.
 */
final class Money
{
    public static function toCents(float|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function toAmount(int $cents): float
    {
        return $cents / 100;
    }
}
