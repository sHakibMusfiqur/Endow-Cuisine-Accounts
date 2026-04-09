<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;

class InventoryDamageController extends Controller
{
    /**
     * Display the specified inventory damage record.
     */
    public function show($id)
    {
        $damage = InventoryAdjustment::with(['inventoryItem', 'adjustedBy'])
            ->damageSpoilage()
            ->findOrFail($id);

        $item = $damage->inventoryItem;

        if (!$item) {
            abort(404);
        }

        $quantityDamaged = abs((float) $damage->difference);
        $unitCost = (float) ($item->unit_cost ?? 0);
        $totalDamageValue = $quantityDamaged * $unitCost;

        return view('inventory.damage.show', compact(
            'damage',
            'item',
            'quantityDamaged',
            'unitCost',
            'totalDamageValue'
        ));
    }
}