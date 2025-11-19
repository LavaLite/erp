<?php

namespace Lavalite\Core\Helpers;

use Illuminate\Support\Facades\Cache;

class CurrencyHelper
{
    /**
     * Supported currencies with their symbols and formatting.
     */
    protected static array $currencies = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'decimal_separator' => ',', 'thousands_separator' => '.', 'decimals' => 2],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 0],
        'CNY' => ['symbol' => '¥', 'name' => 'Chinese Yuan', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'CHF' => ['symbol' => 'CHF', 'name' => 'Swiss Franc', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'SEK' => ['symbol' => 'kr', 'name' => 'Swedish Krona', 'decimal_separator' => ',', 'thousands_separator' => ' ', 'decimals' => 2],
        'NZD' => ['symbol' => 'NZ$', 'name' => 'New Zealand Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'SGD' => ['symbol' => 'S$', 'name' => 'Singapore Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'HKD' => ['symbol' => 'HK$', 'name' => 'Hong Kong Dollar', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'NOK' => ['symbol' => 'kr', 'name' => 'Norwegian Krone', 'decimal_separator' => ',', 'thousands_separator' => ' ', 'decimals' => 2],
        'KRW' => ['symbol' => '₩', 'name' => 'South Korean Won', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 0],
        'TRY' => ['symbol' => '₺', 'name' => 'Turkish Lira', 'decimal_separator' => ',', 'thousands_separator' => '.', 'decimals' => 2],
        'RUB' => ['symbol' => '₽', 'name' => 'Russian Ruble', 'decimal_separator' => ',', 'thousands_separator' => ' ', 'decimals' => 2],
        'BRL' => ['symbol' => 'R$', 'name' => 'Brazilian Real', 'decimal_separator' => ',', 'thousands_separator' => '.', 'decimals' => 2],
        'ZAR' => ['symbol' => 'R', 'name' => 'South African Rand', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
        'MXN' => ['symbol' => 'Mex$', 'name' => 'Mexican Peso', 'decimal_separator' => '.', 'thousands_separator' => ',', 'decimals' => 2],
    ];

    /**
     * Format amount with currency symbol.
     */
    public static function format(float|int $amount, ?string $currency = null, bool $showSymbol = true): string
    {
        $currency = $currency ?? static::getOrganizationCurrency();
        $config = static::$currencies[$currency] ?? static::$currencies['USD'];

        $formatted = number_format(
            $amount,
            $config['decimals'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        if ($showSymbol) {
            return $config['symbol'].$formatted;
        }

        return $formatted;
    }

    /**
     * Get currency symbol.
     */
    public static function symbol(?string $currency = null): string
    {
        $currency = $currency ?? static::getOrganizationCurrency();

        return static::$currencies[$currency]['symbol'] ?? '$';
    }

    /**
     * Get currency name.
     */
    public static function name(?string $currency = null): string
    {
        $currency = $currency ?? static::getOrganizationCurrency();

        return static::$currencies[$currency]['name'] ?? 'US Dollar';
    }

    /**
     * Get all supported currencies.
     */
    public static function all(): array
    {
        return collect(static::$currencies)
            ->map(fn ($config, $code) => [
                'code' => $code,
                'symbol' => $config['symbol'],
                'name' => $config['name'],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Check if currency is supported.
     */
    public static function isSupported(string $currency): bool
    {
        return isset(static::$currencies[$currency]);
    }

    /**
     * Parse formatted currency string to float.
     */
    public static function parse(string $value, ?string $currency = null): float
    {
        $currency = $currency ?? static::getOrganizationCurrency();
        $config = static::$currencies[$currency] ?? static::$currencies['USD'];

        // Remove currency symbol
        $value = str_replace($config['symbol'], '', $value);

        // Remove thousands separator
        $value = str_replace($config['thousands_separator'], '', $value);

        // Replace decimal separator with .
        $value = str_replace($config['decimal_separator'], '.', $value);

        return (float) $value;
    }

    /**
     * Convert amount between currencies (simplified - use real exchange rates in production).
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        // In production, fetch real exchange rates from API
        // This is a placeholder - you should integrate with a real currency API
        $rate = static::getExchangeRate($from, $to);

        return round($amount * $rate, 2);
    }

    /**
     * Get exchange rate (placeholder - integrate with real API).
     */
    protected static function getExchangeRate(string $from, string $to): float
    {
        // Cache exchange rates
        $cacheKey = "exchange_rate:{$from}:{$to}";

        return Cache::remember($cacheKey, 3600, function () {
            // TODO: Integrate with real exchange rate API
            // Example: https://api.exchangerate-api.com/v4/latest/{$from}
            // For now, return 1.0 as placeholder
            return 1.0;
        });
    }

    /**
     * Get currency from current organization context.
     */
    protected static function getOrganizationCurrency(): string
    {
        $organization = app('organization');

        return $organization?->currency ?? config('lavalite-core.default_currency', 'USD');
    }
}
