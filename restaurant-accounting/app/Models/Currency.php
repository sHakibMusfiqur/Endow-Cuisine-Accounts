<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_base',
        'is_active',
        'last_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the transactions for this currency.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DailyTransaction::class);
    }

    /**
     * Get the default currency.
     */
    public static function getDefault(): ?Currency
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Get the base currency (KRW).
     */
    public static function getBaseCurrency(): ?Currency
    {
        return static::where('is_base', true)->first();
    }

    /**
     * Check if this currency is the base currency.
     */
    public function isBaseCurrency(): bool
    {
        return $this->is_base === true;
    }

    /**
     * Get all active currencies.
     */
    public static function getActive()
    {
        return static::where('is_active', true)->orderBy('is_default', 'desc')->get();
    }

    /**
     * Convert amount from this currency to base currency (KRW).
     * 
     * CRITICAL: Conversion Logic
     * ===========================
     * Rate storage: 1 FOREIGN_CURRENCY = X KRW
     * Conversion: amount × rate = base_amount
     * 
     * Examples:
     *   10 USD × 1320.50 = 13205 KRW ✓
     *   100 BDT × 12.00 = 1200 KRW ✓
     *   1000 KRW × 1.00 = 1000 KRW ✓
     * 
     * ALWAYS multiply, NEVER divide!
     * Rate is stored in correct direction for multiplication.
     * 
     * @param float $amount Amount in this currency
     * @return float Amount in base currency (KRW)
     */
    public function convertToBase(float $amount): float
    {
        // Validate amount
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
        
        // CRITICAL: Always multiply (rate is stored correctly)
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert amount from base currency (KRW) to this currency.
     * 
     * CRITICAL: Reverse Conversion Logic
     * ===================================
     * Rate storage: 1 FOREIGN_CURRENCY = X KRW
     * Reverse: base_amount ÷ rate = foreign_amount
     * 
     * Examples:
     *   13205 KRW ÷ 1320.50 = 10 USD ✓
     *   1200 KRW ÷ 12.00 = 100 BDT ✓
     *   1000 KRW ÷ 1.00 = 1000 KRW ✓
     * 
     * This is ONLY used for display purposes.
     * All storage uses base currency (KRW).
     * 
     * @param float $amountBase Amount in base currency (KRW)
     * @return float Amount in this currency
     */
    public function convertFromBase(float $amountBase): float
    {
        // Prevent division by zero
        if ($this->exchange_rate == 0) {
            throw new \RuntimeException('Exchange rate cannot be zero');
        }
        
        // Divide to get foreign currency amount
        // Note: Negative amounts are allowed (expenses, losses, negative balances)
        return $amountBase / $this->exchange_rate;
    }

    /**
     * Format amount with currency symbol.
     *
     * @param float $amount
     * @param bool $showCode
     * @return string
     */
    public function formatAmount(float $amount, bool $showCode = false): string
    {
        $formatted = $this->symbol . number_format($amount, 2);
        
        if ($showCode) {
            $formatted .= ' ' . $this->code;
        }
        
        return $formatted;
    }

    /**
     * Prevent deletion if currency is default or has transactions.
     * Prevent manual editing of exchange rates (system-controlled).
     * Enforce base currency rules.
     * 
     * CRITICAL: System-Controlled Rates
     * ==================================
     * - Rates are ONLY updated by scheduler
     * - Base currency rate is ALWAYS 1.000000
     * - Manual editing is BLOCKED
     * - Historical transactions are PROTECTED
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($currency) {
            if ($currency->is_default || $currency->is_base) {
                throw new \Exception('Cannot delete the base/default currency.');
            }

            if ($currency->transactions()->count() > 0) {
                throw new \Exception('Cannot delete a currency that has transactions.');
            }
        });

        // CRITICAL: Enforce base currency rate = 1
        static::saving(function ($currency) {
            if ($currency->is_base) {
                // Force base currency rate to 1 (non-negotiable)
                $currency->exchange_rate = 1.000000;
                
                // Only one base currency allowed
                if ($currency->isDirty('is_base') && $currency->is_base) {
                    // Remove base flag from other currencies
                    static::where('id', '!=', $currency->id)
                          ->where('is_base', true)
                          ->update(['is_base' => false]);
                }
            }
            
            // Validate exchange rate is valid
            if ($currency->exchange_rate <= 0) {
                throw new \Exception('Exchange rate must be greater than zero.');
            }
        });
    }
}
