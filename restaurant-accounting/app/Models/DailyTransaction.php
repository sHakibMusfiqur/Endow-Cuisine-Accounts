<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'description',
        'source',
        'income',
        'expense',
        'balance',
        'category_id',
        'payment_method_id',
        'currency_id',
        'amount_original',
        'amount_base',
        'exchange_rate_snapshot',
        'internal_reference_id',
        'internal_reference_type',
        'batch_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'income' => 'decimal:2',
        'expense' => 'decimal:2',
        'balance' => 'decimal:2',
        'amount_original' => 'decimal:2',
        'amount_base' => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:6',
    ];

    /**
     * Get the category that owns the transaction.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the payment method that owns the transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the user who created the transaction.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the currency for this transaction.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get inventory adjustments linked to this transaction.
     * Used for filtering inventory-related transactions.
     */
    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class, 'expense_transaction_id');
    }

    /**
     * Scope a query to only include transactions for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('income', '>', 0);
    }

    /**
     * Scope a query to only include expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('expense', '>', 0);
    }

    /**
     * Scope a query for today's transactions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope a query for this week's transactions.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope a query for this month's transactions.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
    }

    /**
     * Scope a query for this year's transactions.
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('date', Carbon::now()->year);
    }

    /**
     * Format the original amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedOriginalAmountAttribute(): string
    {
        if (!$this->currency) {
            return number_format($this->amount_original, 2);
        }
        
        return $this->currency->formatAmount($this->amount_original);
    }

    /**
     * Format the base amount (KRW).
     *
     * @return string
     */
    public function getFormattedBaseAmountAttribute(): string
    {
        $krw = Currency::where('code', 'KRW')->first();
        
        if (!$krw) {
            return '₩' . number_format($this->amount_base, 2);
        }
        
        return $krw->formatAmount($this->amount_base);
    }

    /**
     * Get display amount (shows conversion if not KRW).
     *
     * @return string
     */
    public function getDisplayAmountAttribute(): string
    {
        if (!$this->currency) {
            return $this->formatted_base_amount;
        }
        
        // If transaction is in KRW, just show the amount
        if ($this->currency->code === 'KRW') {
            return $this->formatted_base_amount;
        }
        
        // If in another currency, show: $100 (₩132,000)
        return $this->formatted_original_amount . ' (' . $this->formatted_base_amount . ')';
    }

    /**
     * Convert amount to base currency using stored exchange rate.
     *
     * @param float $amount
     * @return float
     */
    public function convertToBase(float $amount): float
    {
        return $amount * $this->exchange_rate_snapshot;
    }

    /**
     * Get the net amount (income - expense).
     */
    public function getNetAmountAttribute()
    {
        return $this->income - $this->expense;
    }

    /**
     * Scope a query to filter by source.
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for inventory source transactions.
     */
    public function scopeInventorySource($query)
    {
        return $query->where('source', 'inventory');
    }

    /**
     * Scope for restaurant source transactions.
     */
    public function scopeRestaurantSource($query)
    {
        return $query->where('source', 'restaurant');
    }

    /**
     * Scope for internal consumption transactions.
     */
    public function scopeInternalConsumption($query)
    {
        return $query->where('internal_reference_type', 'inventory_internal_consumption');
    }

    /**
     * Get linked transactions (transactions with same internal_reference_id).
     */
    public function linkedTransactions()
    {
        if (!$this->internal_reference_id) {
            return collect();
        }

        return self::where('internal_reference_id', $this->internal_reference_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Check if this transaction is part of a dual-entry set.
     */
    public function isDualEntry(): bool
    {
        return !empty($this->internal_reference_id) && !empty($this->internal_reference_type);
    }
}

