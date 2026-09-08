<?php

namespace App\Support;

class Currency
{
    public const OPTIONS = [
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
    ];

    public static function symbol(string $code): string
    {
        return self::OPTIONS[$code] ?? $code;
    }

    /**
     * Format an amount for display, using the numbering convention
     * of the currency's region: Indian digit grouping for INR,
     * the European comma-decimal/period-thousands style for EUR,
     * and standard grouping for USD.
     */
    public static function format(float|string $amount, string $code = 'INR'): string
    {
        $amount = (float) $amount;

        return match ($code) {
            'INR' => '₹'.self::formatIndian($amount),
            'EUR' => '€ '.number_format($amount, 2, ',', '.'),
            default => (self::OPTIONS[$code] ?? $code.' ').number_format($amount, 2),
        };
    }

    public static function formatIndian(float $amount): string
    {
        $negative = $amount < 0;
        $amount = abs($amount);

        [$whole, $decimal] = explode('.', number_format($amount, 2, '.', ''));

        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);

        $formatted = $rest !== ''
            ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).','.$lastThree
            : $lastThree;

        return ($negative ? '-' : '').$formatted.'.'.$decimal;
    }
}
