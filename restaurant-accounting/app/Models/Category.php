<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'module',
    ];

    /**
     * Get the transactions for this category.
     */
    public function transactions()
    {
        return $this->hasMany(DailyTransaction::class);
    }

    /**
     * Scope a query to only include income categories.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include expense categories.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to only include restaurant module categories.
     */
    public function scopeRestaurant($query)
    {
        return $query->where('module', 'restaurant');
    }

    /**
     * Scope a query to only include inventory module categories.
     */
    public function scopeInventory($query)
    {
        return $query->where('module', 'inventory');
    }
}
