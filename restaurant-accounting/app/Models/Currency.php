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
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
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
     * Get all active currencies.
     */
    public static function getActive()
    {
        return static::where('is_active', true)->orderBy('is_default', 'desc')->get();
    }

    /**
     * Convert amount from this currency to base currency (KRW).
     *
     * @param float $amount
     * @return float
     */
    public function convertToBase(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert amount from base currency (KRW) to this currency.
     *
     * @param float $amountBase
     * @return float
     */
    public function convertFromBase(float $amountBase): float
    {
        if ($this->exchange_rate == 0) {
            return 0;
        }
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
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($currency) {
            if ($currency->is_default) {
                throw new \Exception('Cannot delete the default currency.');
            }

            if ($currency->transactions()->count() > 0) {
                throw new \Exception('Cannot delete a currency that has transactions.');
            }
        });
    }
}
