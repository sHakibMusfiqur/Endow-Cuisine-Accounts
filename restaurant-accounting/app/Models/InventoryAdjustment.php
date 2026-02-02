<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_item_id',
        'old_quantity',
        'new_quantity',
        'difference',
        'old_expense_amount',
        'corrected_expense_amount',
        'expense_transaction_id',
        'reason',
        'correction_type',
        'notes',
        'adjusted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'old_expense_amount' => 'decimal:2',
        'corrected_expense_amount' => 'decimal:2',
    ];

    /**
     * Get the inventory item that was adjusted.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the related expense transaction if this was a purchase correction.
     */
    public function expenseTransaction()
    {
        return $this->belongsTo(\App\Models\DailyTransaction::class, 'expense_transaction_id');
    }

    /**
     * Scope query to only purchase corrections.
     */
    public function scopePurchaseCorrections($query)
    {
        return $query->where('correction_type', 'purchase_correction');
    }

    /**
     * Scope query to only damage/spoilage records.
     */
    public function scopeDamageSpoilage($query)
    {
        return $query->where('correction_type', 'damage_spoilage');
    }

    /**
     * Scope query to only non-financial adjustments.
     */
    public function scopeInventoryAdjustments($query)
    {
        return $query->where('correction_type', 'inventory_adjustment');
    }

    /**
     * Log an inventory adjustment
     */
    public static function logAdjustment(
        int $itemId,
        float $oldQuantity,
        float $newQuantity,
        string $reason = 'Manual Adjustment / Correction',
        ?string $notes = null
    ): self {
        $difference = $newQuantity - $oldQuantity;

        return self::create([
            'inventory_item_id' => $itemId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $difference,
            'reason' => $reason,
            'notes' => $notes,
            'adjusted_by' => auth()->id(),
        ]);
    }
}
