@extends('layouts.app')

@section('title', 'Edit Usage Recipe - Restaurant Accounting')
@section('page-title', 'Edit Usage Recipe')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Recipe</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('inventory.recipes.update', $recipe) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Food Category (When Sold) <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Select a food category...</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('category_id', $recipe->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="inventory_item_id" class="form-label">Inventory Item Used <span class="text-danger">*</span></label>
                            <select class="form-select @error('inventory_item_id') is-invalid @enderror" 
                                    id="inventory_item_id" name="inventory_item_id" required>
                                <option value="">Select an inventory item...</option>
                                @foreach($items as $item)
                                <option value="{{ $item->id }}" 
                                        data-unit="{{ $item->unit }}"
                                        {{ old('inventory_item_id', $recipe->inventory_item_id) == $item->id ? 'selected' : '' }}>
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
                                   value="{{ old('quantity_per_sale', $recipe->quantity_per_sale) }}" 
                                   step="0.01" 
                                   min="0.01" 
                                   required>
                            @error('quantity_per_sale')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Unit: {{ $recipe->inventoryItem->unit }}</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <!-- Hidden input ensures a value is always sent -->
                                <input type="hidden" name="is_active" value="0">
                                <!-- Checkbox overrides when checked -->
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1"
                                       {{ old('is_active', $recipe->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Recipe
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
@endsection
