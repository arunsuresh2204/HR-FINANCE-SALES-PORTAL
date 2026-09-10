<?php

namespace App\Support;

class NumberToWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    /**
     * Render an amount in words using the Indian numbering system (Lakh/Crore),
     * e.g. 1950000.50 => "Indian Rupee Nineteen Lakh Fifty Thousand and Fifty Paise Only".
     */
    public static function indianRupees(float $amount): string
    {
        $amount = round($amount, 2);
        $whole = (int) floor($amount);
        $paise = (int) round(($amount - $whole) * 100);

        $words = 'Indian Rupee '.($whole > 0 ? self::convertIndian($whole) : 'Zero');

        if ($paise > 0) {
            $words .= ' and '.self::convertBelowThousand($paise).' Paise';
        }

        return $words.' Only';
    }

    private static function convertIndian(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $crore = intdiv($number, 10000000);
        $number %= 10000000;
        $lakh = intdiv($number, 100000);
        $number %= 100000;
        $thousand = intdiv($number, 1000);
        $number %= 1000;

        $parts = [];

        if ($crore > 0) {
            $parts[] = self::convertBelowThousand($crore).' Crore';
        }

        if ($lakh > 0) {
            $parts[] = self::convertBelowThousand($lakh).' Lakh';
        }

        if ($thousand > 0) {
            $parts[] = self::convertBelowThousand($thousand).' Thousand';
        }

        if ($number > 0) {
            $parts[] = self::convertBelowThousand($number);
        }

        return implode(' ', $parts);
    }

    private static function convertBelowThousand(int $number): string
    {
        if ($number < 20) {
            return self::ONES[$number];
        }

        if ($number < 100) {
            $tens = self::TENS[intdiv($number, 10)];
            $ones = $number % 10;

            return $ones > 0 ? "{$tens} ".self::ONES[$ones] : $tens;
        }

        $hundreds = intdiv($number, 100);
        $remainder = $number % 100;

        $words = self::ONES[$hundreds].' Hundred';

        if ($remainder > 0) {
            $words .= ' '.self::convertBelowThousand($remainder);
        }

        return $words;
    }
}
