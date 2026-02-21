<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        ActivityLog::log(
            action: 'category_created',
            description: "Category created: {$category->name} (Type: {$category->type})",
            module: 'categories',
            metadata: [
                'category_id' => $category->id,
                'new_name' => $category->name,
                'new_type' => $category->type,
            ]
        );
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        // Only log if name or type actually changed
        if (!$category->wasChanged(['name', 'type'])) {
            return;
        }

        $oldName = $category->getOriginal('name');
        $newName = $category->name;
        $oldType = $category->getOriginal('type');
        $newType = $category->type;

        // Build description based on what changed
        $changes = [];
        if ($category->wasChanged('name')) {
            $changes[] = "{$oldName} → {$newName}";
        } else {
            $changes[] = $newName;
        }

        if ($category->wasChanged('type')) {
            $changes[] = "(Type: {$oldType} → {$newType})";
        } else {
            $changes[] = "(Type: {$newType})";
        }

        $description = "Category updated: " . implode(' ', $changes);

        ActivityLog::log(
            action: 'category_updated',
            description: $description,
            module: 'categories',
            metadata: [
                'category_id' => $category->id,
                'old_name' => $oldName,
                'new_name' => $newName,
                'old_type' => $oldType,
                'new_type' => $newType,
            ]
        );
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        ActivityLog::log(
            action: 'category_deleted',
            description: "Category deleted: {$category->name} (Type: {$category->type})",
            module: 'categories',
            metadata: [
                'category_id' => $category->id,
                'old_name' => $category->name,
                'old_type' => $category->type,
            ]
        );
    }
}
