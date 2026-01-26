@extends('layouts.app')

@section('title', 'Edit Inventory Item - Restaurant Accounting')
@section('page-title', 'Edit Inventory Item')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit: {{ $item->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.items.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $item->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="sku" class="form-label">SKU/Code</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" name="sku" value="{{ old('sku', $item->sku) }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                    <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                                    <option value="g" {{ old('unit', $item->unit) == 'g' ? 'selected' : '' }}>g</option>
                                    <option value="L" {{ old('unit', $item->unit) == 'L' ? 'selected' : '' }}>L</option>
                                    <option value="mL" {{ old('unit', $item->unit) == 'mL' ? 'selected' : '' }}>mL</option>
                                    <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                                    <option value="box" {{ old('unit', $item->unit) == 'box' ? 'selected' : '' }}>box</option>
                                    <option value="bag" {{ old('unit', $item->unit) == 'bag' ? 'selected' : '' }}>bag</option>
                                    <option value="bottle" {{ old('unit', $item->unit) == 'bottle' ? 'selected' : '' }}>bottle</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       id="minimum_stock" name="minimum_stock" 
                                       value="{{ old('minimum_stock', $item->minimum_stock) }}" 
                                       step="0.01" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="unit_cost" class="form-label">Unit Cost (₩) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('unit_cost') is-invalid @enderror" 
                                       id="unit_cost" name="unit_cost" 
                                       value="{{ old('unit_cost', $item->unit_cost) }}" 
                                       step="0.01" min="0" required>
                                @error('unit_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="selling_price_per_unit" class="form-label">Selling Price per Unit (₩) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('selling_price_per_unit') is-invalid @enderror" 
                                       id="selling_price_per_unit" name="selling_price_per_unit" 
                                       value="{{ old('selling_price_per_unit', $item->selling_price_per_unit) }}" 
                                       step="0.01" min="0" required>
                                @error('selling_price_per_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Selling price used during inventory sales</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <!-- Hidden input ensures a value is always sent (unchecked = 0) -->
                                <input type="hidden" name="is_active" value="0">
                                <!-- Checkbox overrides hidden input when checked (checked = 1) -->
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1"
                                       {{ old('is_active', $item->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Current Stock:</strong> {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                            (Cannot be edited directly. Use stock movements to adjust.)
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Item
                            </button>
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
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
