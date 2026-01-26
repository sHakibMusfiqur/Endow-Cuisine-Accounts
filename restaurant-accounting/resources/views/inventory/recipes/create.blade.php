@extends('layouts.app')

@section('title', 'Add Usage Recipe - Restaurant Accounting')
@section('page-title', 'Add Usage Recipe')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>New Usage Recipe</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Define which inventory items are automatically used when a specific food category is sold.
                    </div>

                    <form action="{{ route('inventory.recipes.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Food Category (When Sold) <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Select a food category...</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Income categories only (food sales)</small>
                        </div>

                        <div class="mb-3">
                            <label for="inventory_item_id" class="form-label">Inventory Item Used <span class="text-danger">*</span></label>
                            <select class="form-select @error('inventory_item_id') is-invalid @enderror" 
                                    id="inventory_item_id" name="inventory_item_id" required>
                                <option value="">Select an inventory item...</option>
                                @foreach($items as $item)
                                <option value="{{ $item->id }}" 
                                        data-unit="{{ $item->unit }}"
                                        {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} ({{ $item->unit }})
                                </option>
                                @endforeach
                            </select>
                            @error('inventory_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity_per_sale" class="form-label">Quantity Per Sale <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('quantity_per_sale') is-invalid @enderror" 
                                   id="quantity_per_sale" 
                                   name="quantity_per_sale" 
                                   value="{{ old('quantity_per_sale') }}" 
                                   step="0.01" 
                                   min="0.01" 
                                   required
                                   placeholder="How much is used per sale">
                            @error('quantity_per_sale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Unit: <span id="unit-display">-</span></small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <!-- Hidden input ensures a value is always sent -->
                                <input type="hidden" name="is_active" value="0">
                                <!-- Checkbox overrides when checked -->
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                                <small class="text-muted d-block">Uncheck to disable automatic stock deduction</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Recipe
                            </button>
                            <a href="{{ route('inventory.recipes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('inventory_item_id');
    const unitDisplay = document.getElementById('unit-display');

    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            unitDisplay.textContent = selectedOption.dataset.unit;
        } else {
            unitDisplay.textContent = '-';
        }
    });
});
</script>
@endsection
