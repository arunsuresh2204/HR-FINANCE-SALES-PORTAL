<?php

namespace App\Support;

use App\Models\Currency as CurrencyModel;
use Illuminate\Support\Facades\Cache;

class Currency
{
    /**
     * All currencies (active and inactive), keyed by code. Cached indefinitely
     * and busted by App\Models\Currency whenever a row is saved or deleted.
     *
     * Inactive currencies are kept here, not dropped, so that historical
     * amounts recorded in a currency the admin later deactivates still
     * format with their real symbol/style instead of falling back to a
     * bare code.
     *
     * @return array<string, CurrencyModel>
     */
    protected static function all(): array
    {
        return Cache::rememberForever('currencies.all', function () {
            return CurrencyModel::all()->keyBy('code')->all();
        });
    }

    /**
     * Code => symbol map of ACTIVE currencies only, for populating dropdowns
     * where a user is picking a currency for a new record.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_map(
            fn (CurrencyModel $currency) => $currency->symbol,
            array_filter(self::all(), fn (CurrencyModel $currency) => $currency->is_active)
        );
    }

    /**
     * Active currency codes, for validation rules on new records.
     *
     * @return array<int, string>
     */
    public static function codes(): array
    {
        return array_keys(self::options());
    }

    /**
     * Every known currency code, active or not. For validating a value that
     * was already assigned to an existing record (e.g. copying a billing
     * request's currency onto its invoice) rather than a fresh user pick —
     * deactivating a currency must not block acting on records already in it.
     *
     * @return array<int, string>
     */
    public static function allCodes(): array
    {
        return array_keys(self::all());
    }

    public static function symbol(string $code): string
    {
        return self::all()[$code]->symbol ?? $code;
    }

    /**
     * Format an amount for display, using the numbering convention
     * configured for the currency: Indian digit grouping for INR,
     * the European comma-decimal/period-thousands style for EUR-like
     * currencies, and standard grouping otherwise.
     */
    public static function format(float|string $amount, string $code = 'INR'): string
    {
        $amount = (float) $amount;
        $currency = self::all()[$code] ?? null;

        if (! $currency) {
            return $code.' '.number_format($amount, 2);
        }

        $prefix = $currency->symbol.($currency->symbol_spaced ? ' ' : '');

        return $prefix.match ($currency->format_style) {
            'indian' => self::formatIndian($amount),
            'european' => number_format($amount, 2, ',', '.'),
            default => number_format($amount, 2),
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
