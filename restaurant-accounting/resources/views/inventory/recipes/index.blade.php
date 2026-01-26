@extends('layouts.app')

@section('title', 'Usage Recipes - Restaurant Accounting')
@section('page-title', 'Usage Recipes')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Info Alert -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>What are Usage Recipes?</strong><br>
        Usage recipes define how much inventory is automatically deducted when a food sale happens. 
        For example, if you sell "Fried Chicken", the system will automatically reduce the stock of 
        chicken, oil, and flour based on your recipes.
    </div>

    <!-- Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>All Usage Recipes</h5>
                @can('manage inventory')
                <a href="{{ route('inventory.recipes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Recipe
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Recipes Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Food Category (Sale)</th>
                            <th>Inventory Item Used</th>
                            <th>Quantity Per Sale</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipes as $recipe)
                        <tr>
                            <td>
                                <strong>{{ $recipe->category->name }}</strong>
                                <br><small class="text-muted">When this is sold...</small>
                            </td>
                            <td>
                                <strong>{{ $recipe->inventoryItem->name }}</strong>
                                <br><small class="text-muted">...this item is used</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ number_format($recipe->quantity_per_sale, 2) }} {{ $recipe->inventoryItem->unit }}
                                </span>
                            </td>
                            <td>
                                @if($recipe->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('manage inventory')
                                    <a href="{{ route('inventory.recipes.edit', $recipe) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Delete"
                                            onclick="confirmDelete('{{ $recipe->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 d-block"></i>
                                No usage recipes found. Add recipes to automatically track inventory from sales.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-pagination :items="$recipes" />
        </div>
    </div>
</div>

@can('manage inventory')
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(recipeId) {
    if (confirm('Are you sure you want to delete this recipe?\n\nThis will stop automatic inventory deduction for this combination.')) {
        const form = document.getElementById('delete-form');
        form.action = `/inventory/recipes/${recipeId}`;
        form.submit();
    }
}
</script>
@endcan
@endsection
