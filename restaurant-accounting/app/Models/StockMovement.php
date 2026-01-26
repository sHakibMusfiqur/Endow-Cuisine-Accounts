<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'movement_date',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'movement_date' => 'date',
    ];

    /**
     * Get the inventory item for this movement.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the user who created this movement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the related reference (polymorphic).
     */
    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            return $this->morphTo('reference', 'reference_type', 'reference_id');
        }
        return null;
    }

    /**
     * Scope for stock in movements.
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for stock out movements.
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for usage movements (auto from sales).
     */
    public function scopeUsage($query)
    {
        return $query->where('type', 'usage');
    }

    /**
     * Scope for adjustment movements.
     */
    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope for sale movements (direct inventory sales).
     */
    public function scopeSale($query)
    {
        return $query->where('type', 'sale');
    }

    /**
     * Scope for opening stock movements.
     */
    public function scopeOpening($query)
    {
        return $query->where('type', 'opening');
    }
    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted type name.
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'opening' => 'Opening Stock',
            'in' => 'Stock In (Purchase)',
            'out' => 'Stock Out (Waste/Damage)',
            'adjustment' => 'Adjustment',
            'usage' => 'Usage (From Sales)',
            'sale' => 'Direct Sale',
            default => ucfirst($this->type),
        };
    }
}
