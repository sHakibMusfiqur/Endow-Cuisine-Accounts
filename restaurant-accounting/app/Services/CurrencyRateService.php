<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CurrencyRateService
{
    /**
     * API endpoint for exchange rates
     * Using ExchangeRate-API.com (free tier available)
     */
    private string $apiUrl;
    private string $apiKey;
    
    /**
     * Base currency code (always KRW)
     */
    private const BASE_CURRENCY = 'KRW';
    
    /**
     * Target currencies to fetch rates for
     */
    private const TARGET_CURRENCIES = ['USD', 'BDT'];

    public function __construct()
    {
        $this->apiUrl = config('currency.api_url');
        $this->apiKey = config('currency.api_key');
    }

    /**
     * Update all currency rates from external API
     * 
     * @return array Status information about the update
     */
    public function updateAllRates(): array
    {
        $results = [
            'success' => false,
            'updated' => [],
            'failed' => [],
            'message' => '',
        ];

        try {
            // Fetch rates from API
            $rates = $this->fetchRatesFromAPI();
            
            if (empty($rates)) {
                throw new Exception('No rates returned from API');
            }

            // Update each currency
            foreach (self::TARGET_CURRENCIES as $currencyCode) {
                try {
                    if (!isset($rates[$currencyCode])) {
                        $results['failed'][] = $currencyCode;
                        Log::warning("Currency rate not found in API response", ['currency' => $currencyCode]);
                        continue;
                    }

                    $this->updateCurrencyRate($currencyCode, $rates[$currencyCode]);
                    $results['updated'][] = $currencyCode;
                    
                } catch (Exception $e) {
                    $results['failed'][] = $currencyCode;
                    Log::error("Failed to update currency rate", [
                        'currency' => $currencyCode,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Ensure KRW base currency is always 1
            $this->ensureBaseCurrencyIsOne();

            $results['success'] = count($results['updated']) > 0;
            $results['message'] = sprintf(
                'Updated %d currencies. Failed: %d',
                count($results['updated']),
                count($results['failed'])
            );

            Log::info('Currency rates update completed', $results);

        } catch (Exception $e) {
            $results['message'] = 'API request failed: ' . $e->getMessage();
            Log::error('Currency rate update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Fetch exchange rates from external API
     * 
     * @return array Associative array of currency codes to rates
     * @throws Exception
     */
    private function fetchRatesFromAPI(): array
    {
        // Using ExchangeRate-API.com
        // Format: https://v6.exchangerate-api.com/v6/{api_key}/latest/{base}
        
        $url = sprintf('%s/%s/latest/%s', 
            rtrim($this->apiUrl, '/'),
            $this->apiKey,
            self::BASE_CURRENCY
        );

        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                throw new Exception('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['result']) || $data['result'] !== 'success') {
                throw new Exception('API returned error: ' . ($data['error-type'] ?? 'Unknown error'));
            }

            if (!isset($data['conversion_rates'])) {
                throw new Exception('No conversion rates in API response');
            }

            return $data['conversion_rates'];

        } catch (Exception $e) {
            Log::error('Failed to fetch rates from API', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update a specific currency's exchange rate
     * 
     * CRITICAL: Rate Storage Direction
     * ================================
     * We ALWAYS store rates as: 1 FOREIGN_CURRENCY = X KRW
     * 
     * Examples:
     *   1 USD = 1320.50 KRW  (rate stored: 1320.50)
     *   1 BDT = 12.00 KRW    (rate stored: 12.00)
     *   1 KRW = 1.00 KRW     (rate stored: 1.00, base currency)
     * 
     * The API returns: 1 KRW = 0.000757 USD (inverted)
     * We invert to get: 1 USD = 1320.50 KRW (correct direction)
     * 
     * This ensures conversion is ALWAYS multiplication:
     *   10 USD × 1320.50 = 13205 KRW ✓
     * 
     * @param string $code Currency code (USD, BDT)
     * @param float $rate Exchange rate from API (inverted, needs correction)
     */
    private function updateCurrencyRate(string $code, float $rate): void
    {
        $currency = Currency::where('code', $code)->first();

        if (!$currency) {
            Log::warning("Currency not found in database", ['code' => $code]);
            return;
        }

        // Validate rate is reasonable (not zero or negative)
        if ($rate <= 0) {
            throw new Exception("Invalid rate received: {$rate}");
        }

        // CRITICAL: The API returns rates as "1 KRW = X USD" (inverted)
        // We MUST store "1 USD = X KRW" (correct direction for multiplication)
        // Therefore, we take the reciprocal: exchangeRate = 1 / apiRate
        $exchangeRate = 1 / $rate;

        // Additional validation: ensure converted rate is reasonable
        if ($exchangeRate <= 0 || $exchangeRate > 999999) {
            throw new Exception("Converted rate out of valid range: {$exchangeRate}");
        }

        // Update the currency with the CORRECT rate direction
        $currency->update([
            'exchange_rate' => $exchangeRate,
            'last_updated_at' => now(),
        ]);

        Log::info("Currency rate updated dynamically", [
            'code' => $code,
            'stored_rate' => $exchangeRate, // 1 USD = X KRW
            'api_rate' => $rate,            // 1 KRW = X USD (inverted)
            'direction' => "1 {$code} = {$exchangeRate} KRW",
        ]);
    }

    /**
     * Ensure the base currency (KRW) always has a rate of 1
     * 
     * MANDATORY: Base Currency Rules
     * ================================
     * - Only ONE base currency allowed (KRW)
     * - Base currency rate MUST always be 1.000000
     * - This is enforced by scheduler, model, and migration
     * - Rate represents: 1 KRW = 1 KRW (by definition)
     * 
     * This ensures all conversions are relative to KRW:
     *   1 USD × 1320.50 = 1320.50 KRW
     *   1 KRW × 1.00 = 1.00 KRW ✓
     */
    private function ensureBaseCurrencyIsOne(): void
    {
        Currency::where('code', self::BASE_CURRENCY)->update([
            'exchange_rate' => 1.000000,
            'is_base' => true,
            'is_default' => true,
            'last_updated_at' => now(),
        ]);
        
        Log::info("Base currency rate enforced", [
            'currency' => self::BASE_CURRENCY,
            'rate' => 1.000000,
            'rule' => 'Base currency rate is always 1',
        ]);
    }

    /**
     * Get the last update timestamp for currencies
     * 
     * @return string|null Formatted date string
     */
    public function getLastUpdateTime(): ?string
    {
        $currency = Currency::whereNotNull('last_updated_at')
            ->orderBy('last_updated_at', 'desc')
            ->first();

        return $currency?->last_updated_at?->format('Y-m-d H:i:s');
    }

    /**
     * Check if rates need updating (older than 24 hours)
     * 
     * @return bool
     */
    public function needsUpdate(): bool
    {
        $currency = Currency::where('code', '!=', self::BASE_CURRENCY)
            ->whereNotNull('last_updated_at')
            ->orderBy('last_updated_at', 'desc')
            ->first();

        if (!$currency || !$currency->last_updated_at) {
            return true;
        }

        return $currency->last_updated_at->diffInHours(now()) >= 24;
    }
}
