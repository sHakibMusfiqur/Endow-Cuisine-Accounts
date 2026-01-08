<?php

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

if (!function_exists('getActiveCurrency')) {
    /**
     * Get the active currency for the current session.
     * Priority: Session selected currency > Default currency
     *
     * @return Currency
     */
    function getActiveCurrency(): Currency
    {
        // Try to get from cache first for performance
        $cacheKey = 'active_currency_' . Session::getId();
        
        $currency = Cache::remember($cacheKey, 3600, function () {
            // Check if user has selected a currency in session
            $currencyId = Session::get('active_currency_id');
            
            if ($currencyId) {
                $currency = Currency::where('id', $currencyId)
                    ->where('is_active', true)
                    ->first();
                    
                if ($currency) {
                    return $currency;
                }
            }
            
            // Fallback to default currency
            $defaultCurrency = Currency::getDefault();
            
            // If no default exists, get the first active currency
            if (!$defaultCurrency) {
                $defaultCurrency = Currency::where('is_active', true)->first();
            }
            
            // Ultimate fallback: create a default KRW currency if none exists
            if (!$defaultCurrency) {
                $defaultCurrency = Currency::create([
                    'code' => 'KRW',
                    'name' => 'South Korean Won',
                    'symbol' => '₩',
                    'exchange_rate' => 1.0,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }
            
            return $defaultCurrency;
        });
        
        return $currency;
    }
}

if (!function_exists('setActiveCurrency')) {
    /**
     * Set the active currency for the current session.
     *
     * @param int $currencyId
     * @return bool
     */
    function setActiveCurrency(int $currencyId): bool
    {
        $currency = Currency::where('id', $currencyId)
            ->where('is_active', true)
            ->first();
            
        if ($currency) {
            Session::put('active_currency_id', $currencyId);
            
            // Clear cache
            $cacheKey = 'active_currency_' . Session::getId();
            Cache::forget($cacheKey);
            
            return true;
        }
        
        return false;
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format an amount with the active currency symbol.
     * Amount should be in base currency (KRW).
     *
     * @param float $amountInBase Amount in base currency (KRW)
     * @param bool $showCode Whether to show currency code alongside symbol
     * @param Currency|null $currency Custom currency (if null, uses active currency)
     * @param bool $convertFromBase Whether to convert from base currency (default: true)
     * @return string
     */
    function formatCurrency(
        float $amountInBase, 
        bool $showCode = false, 
        ?Currency $currency = null,
        bool $convertFromBase = true
    ): string {
        $currency = $currency ?? getActiveCurrency();
        
        // Convert from base currency to active currency if needed
        $amount = $convertFromBase 
            ? $currency->convertFromBase($amountInBase) 
            : $amountInBase;
        
        // Determine decimal places based on currency
        $decimals = in_array($currency->code, ['KRW', 'JPY']) ? 0 : 2;
        
        $formatted = $currency->symbol . number_format($amount, $decimals);
        
        if ($showCode) {
            $formatted .= ' ' . $currency->code;
        }
        
        return $formatted;
    }
}

if (!function_exists('formatCurrencyRaw')) {
    /**
     * Format an amount that's already in the target currency.
     * Use this when the amount is not in base currency.
     *
     * @param float $amount Amount already in the target currency
     * @param bool $showCode Whether to show currency code alongside symbol
     * @param Currency|null $currency Custom currency (if null, uses active currency)
     * @return string
     */
    function formatCurrencyRaw(
        float $amount, 
        bool $showCode = false, 
        ?Currency $currency = null
    ): string {
        return formatCurrency($amount, $showCode, $currency, false);
    }
}

if (!function_exists('convertCurrency')) {
    /**
     * Convert amount from base currency to active currency.
     *
     * @param float $amountInBase Amount in base currency (KRW)
     * @param Currency|null $currency Target currency (if null, uses active currency)
     * @return float
     */
    function convertCurrency(float $amountInBase, ?Currency $currency = null): float
    {
        $currency = $currency ?? getActiveCurrency();
        return $currency->convertFromBase($amountInBase);
    }
}

if (!function_exists('getAllActiveCurrencies')) {
    /**
     * Get all active currencies for dropdowns/selection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getAllActiveCurrencies()
    {
        return Cache::remember('all_active_currencies', 3600, function () {
            return Currency::getActive();
        });
    }
}

if (!function_exists('clearCurrencyCache')) {
    /**
     * Clear all currency-related caches.
     * Call this when currencies are updated.
     *
     * @return void
     */
    function clearCurrencyCache(): void
    {
        Cache::forget('all_active_currencies');
        Cache::forget('active_currency_' . Session::getId());
    }
}
