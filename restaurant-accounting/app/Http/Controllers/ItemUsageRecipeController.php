<?php

namespace App\Http\Controllers;

use App\Models\ItemUsageRecipe;
use App\Models\InventoryItem;
use App\Models\Category;
use Illuminate\Http\Request;

class ItemUsageRecipeController extends Controller
{
    /**
     * Display a listing of usage recipes.
     */
    public function index()
    {
        $recipes = ItemUsageRecipe::with(['category', 'inventoryItem'])
            ->orderBy('category_id')
            ->paginate(20);

        return view('inventory.recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create()
    {
        $categories = Category::where('type', 'income')->orderBy('name')->get();
        $items = InventoryItem::active()->orderBy('name')->get();

        return view('inventory.recipes.create', compact('categories', 'items'));
    }

    /**
     * Store a newly created recipe.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_per_sale' => 'required|numeric|min:0.01',
            'is_active' => 'required|boolean',
        ]);

        // Check if recipe already exists
        $existing = ItemUsageRecipe::where('category_id', $validated['category_id'])
            ->where('inventory_item_id', $validated['inventory_item_id'])
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This recipe combination already exists.');
        }

        // is_active is already validated as boolean, cast to ensure type safety
        $validated['is_active'] = (bool) $validated['is_active'];

        ItemUsageRecipe::create($validated);

        return redirect()->route('inventory.recipes.index')
            ->with('success', 'Usage recipe created successfully.');
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(ItemUsageRecipe $recipe)
    {
        $categories = Category::where('type', 'income')->orderBy('name')->get();
        $items = InventoryItem::active()->orderBy('name')->get();

        return view('inventory.recipes.edit', compact('recipe', 'categories', 'items'));
    }

    /**
     * Update the specified recipe.
     */
    public function update(Request $request, ItemUsageRecipe $recipe)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_per_sale' => 'required|numeric|min:0.01',
            'is_active' => 'required|boolean',
        ]);

        // Check if recipe already exists (excluding current)
        $existing = ItemUsageRecipe::where('category_id', $validated['category_id'])
            ->where('inventory_item_id', $validated['inventory_item_id'])
            ->where('id', '!=', $recipe->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This recipe combination already exists.');
        }

        // is_active is already validated as boolean, cast to ensure type safety
        $validated['is_active'] = (bool) $validated['is_active'];

        $recipe->update($validated);

        return redirect()->route('inventory.recipes.index')
            ->with('success', 'Usage recipe updated successfully.');
    }

    /**
     * Remove the specified recipe.
     */
    public function destroy(ItemUsageRecipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('inventory.recipes.index')
            ->with('success', 'Usage recipe deleted successfully.');
    }
}
