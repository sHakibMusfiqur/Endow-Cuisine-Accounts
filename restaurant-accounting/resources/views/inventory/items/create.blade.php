@extends('layouts.app')

@section('title', 'Add Inventory Item - Restaurant Accounting')
@section('page-title', 'Add New Inventory Item')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger rounded-top py-3">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white me-2" style="width: 32px; height: 32px; font-size: 18px;">
                            <i class="fas fa-plus"></i>
                        </span>
                        Add New Inventory Item
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.items.store') }}" method="POST">
                        @csrf

                        <!-- Item Information Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Item Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           placeholder="e.g., Chicken, Beef, Tomatoes, Cooking Oil">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU / Code</label>
                                    <input type="text" 
                                           class="form-control @error('sku') is-invalid @enderror" 
                                           id="sku" 
                                           name="sku" 
                                           value="{{ old('sku') }}" 
                                           placeholder="Optional unique identifier">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="Optional details about this item">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Stock & Pricing Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-boxes me-2"></i>Stock & Pricing</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                        <select class="form-select @error('unit') is-invalid @enderror" 
                                                id="unit" 
                                                name="unit" 
                                                required>
                                            <option value="">Select Unit</option>
                                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg (Kilogram)</option>
                                            <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>g (Gram)</option>
                                            <option value="L" {{ old('unit') == 'L' ? 'selected' : '' }}>L (Liter)</option>
                                            <option value="mL" {{ old('unit') == 'mL' ? 'selected' : '' }}>mL (Milliliter)</option>
                                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs (Pieces)</option>
                                            <option value="packet" {{ old('unit') == 'packet' ? 'selected' : '' }}>packet (Packet)</option>
                                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box (Box)</option>
                                            <option value="bag" {{ old('unit') == 'bag' ? 'selected' : '' }}>bag (Bag)</option>
                                            <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>bottle (Bottle)</option>
                                        </select>
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="opening_stock" class="form-label">Opening Stock <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control @error('opening_stock') is-invalid @enderror" 
                                               id="opening_stock" 
                                               name="opening_stock" 
                                               value="{{ old('opening_stock') }}" 
                                               step="0.01" 
                                               min="0" 
                                               required 
                                               placeholder="e.g., 25">
                                        @error('opening_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control @error('minimum_stock') is-invalid @enderror" 
                                               id="minimum_stock" 
                                               name="minimum_stock" 
                                               value="{{ old('minimum_stock') }}" 
                                               step="0.01" 
                                               min="0" 
                                               required 
                                               placeholder="e.g., 10">
                                        @error('minimum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select @error('payment_method_id') is-invalid @enderror" 
                                                id="payment_method_id" 
                                                name="payment_method_id" 
                                                required>
                                            <option value="">Select Payment Method</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('payment_method_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="unit_cost" class="form-label">Unit Cost (KRW) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₩</span>
                                            <input type="number" 
                                                   class="form-control @error('unit_cost') is-invalid @enderror" 
                                                   id="unit_cost" 
                                                   name="unit_cost" 
                                                   value="{{ old('unit_cost') }}" 
                                                   step="0.01" 
                                                   min="0" 
                                                   required 
                                                   placeholder="e.g., 1200">
                                        </div>
                                        @error('unit_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="selling_price_per_unit" class="form-label">Selling Price per Unit (KRW) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₩</span>
                                            <input type="number" 
                                                   class="form-control @error('selling_price_per_unit') is-invalid @enderror" 
                                                   id="selling_price_per_unit" 
                                                   name="selling_price_per_unit" 
                                                   value="{{ old('selling_price_per_unit') }}" 
                                                   step="0.01" 
                                                   min="0" 
                                                   required 
                                                   placeholder="e.g., 1800">
                                        </div>
                                        @error('selling_price_per_unit')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                    <small class="text-muted d-block">Uncheck to hide this item from active inventory</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Opening stock represents the initial inventory purchase. The system will automatically record this as an expense transaction if opening stock > 0. After creation, stock changes only through "Add Stock" or "Stock Out" operations.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save"></i> Create Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
