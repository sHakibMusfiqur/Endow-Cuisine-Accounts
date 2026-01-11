<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency Exchange Rate API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the external API used for fetching real-time currency
    | exchange rates. The system automatically updates USD, BDT, and KRW
    | rates daily without any manual intervention.
    |
    | Recommended API: ExchangeRate-API (https://www.exchangerate-api.com/)
    | Free tier: 1,500 requests/month (sufficient for daily updates)
    |
    | Alternative APIs:
    | - Open Exchange Rates: https://openexchangerates.org/
    | - CurrencyLayer: https://currencylayer.com/
    | - Fixer: https://fixer.io/
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the exchange rate API endpoint.
    | For ExchangeRate-API: https://v6.exchangerate-api.com/v6
    |
    */
    'api_url' => env('CURRENCY_API_URL', 'https://v6.exchangerate-api.com/v6'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your API key for the exchange rate service.
    | Sign up at https://app.exchangerate-api.com/sign-up to get a free key.
    |
    */
    'api_key' => env('CURRENCY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | The base currency for all exchange rate calculations.
    | This should always be KRW for the restaurant accounting system.
    | DO NOT CHANGE THIS VALUE.
    |
    */
    'base_currency' => 'KRW',

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | List of currencies supported by the system.
    | These are the currencies that will be automatically updated.
    |
    */
    'supported_currencies' => [
        'KRW' => [
            'name' => 'Korean Won',
            'symbol' => '₩',
            'is_base' => true,
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_base' => false,
        ],
        'BDT' => [
            'name' => 'Bangladeshi Taka',
            'symbol' => '৳',
            'is_base' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Update Schedule
    |--------------------------------------------------------------------------
    |
    | How often to update currency rates (in hours).
    | Default: 24 (once per day)
    |
    */
    'update_frequency_hours' => env('CURRENCY_UPDATE_FREQUENCY', 24),

    /*
    |--------------------------------------------------------------------------
    | Rate Validation
    |--------------------------------------------------------------------------
    |
    | Validation rules for exchange rates to prevent bad data.
    |
    */
    'validation' => [
        // Maximum allowed change percentage before requiring manual verification
        'max_daily_change_percent' => 10,
        
        // Minimum valid rate (prevents zero or negative rates)
        'min_rate' => 0.0001,
        
        // Maximum valid rate
        'max_rate' => 999999,
    ],

];
