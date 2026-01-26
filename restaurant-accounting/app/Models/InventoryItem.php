<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'unit',
        'current_stock',
        'minimum_stock',
        'unit_cost',
        'selling_price_per_unit',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'selling_price_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all stock movements for this item.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get usage recipes that use this item.
     */
    public function usageRecipes()
    {
        return $this->hasMany(ItemUsageRecipe::class);
    }

    /**
     * Check if stock is low.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    /**
     * Update stock after a movement.
     */
    public function updateStock(float $quantity, string $type): void
    {
        if ($type === 'in' || $type === 'adjustment') {
            $this->current_stock += $quantity;
        } elseif ($type === 'out' || $type === 'usage') {
            $this->current_stock -= $quantity;
        }

        $this->save();
    }

    /**
     * Get total value of current stock.
     */
    public function getStockValueAttribute(): float
    {
        return $this->current_stock * $this->unit_cost;
    }
}
