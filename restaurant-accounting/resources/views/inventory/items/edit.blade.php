@extends('layouts.app')

@section('title', 'Edit Inventory Item - Restaurant Accounting')
@section('page-title', 'Edit Inventory Item')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-edit text-danger me-2"></i>Edit Inventory Item
            </h4>
            <p class="text-muted mb-0">{{ $item->name }}</p>
        </div>
        <a href="{{ route('inventory.items.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Inventory
        </a>
    </div>

    <!-- Always-Visible Info Section -->
    <div class="row mb-4">
        <!-- Current Stock Card -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-boxes me-2"></i>Current Stock Level
                    </h6>
                    <div class="d-flex align-items-end gap-2 mb-2">
                        <span class="display-5 fw-bold text-dark">{{ number_format($item->current_stock, 2) }}</span>
                        <span class="fs-4 text-muted mb-2">{{ $item->unit }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($item->isLowStock())
                                <span class="badge bg-danger px-3 py-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                </span>
                            @else
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>In Stock
                                </span>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Stock Value</small>
                            <span class="fs-5 fw-bold text-dark">₩{{ number_format($item->stock_value, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Unit Cost Card -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-won-sign me-2"></i>Current Unit Cost
                    </h6>
                    <div class="mb-3">
                        <span class="display-5 fw-bold text-dark">₩{{ number_format($item->unit_cost, 2) }}</span>
                    </div>
                    <div class="alert alert-light border mb-0 py-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            To change unit cost, use <strong>"Purchase Entry Correction"</strong> below
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Edit Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form action="{{ route('inventory.items.update', $item) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-info-circle text-danger me-2"></i>Basic Information
                        </h6>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $item->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="sku" class="form-label fw-semibold">SKU/Code</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" name="sku" value="{{ old('sku', $item->sku) }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label for="unit" class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
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
                                <label for="minimum_stock" class="form-label fw-semibold">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       id="minimum_stock" name="minimum_stock" 
                                       value="{{ old('minimum_stock', $item->minimum_stock) }}" 
                                       step="0.01" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="selling_price_per_unit" class="form-label fw-semibold">Selling Price per Unit (₩) <span class="text-danger">*</span></label>
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

                        <!-- Stock Adjustment Section -->
                        <div class="card border shadow-sm mt-4 mb-4">
                            <div class="card-header bg-light border-bottom">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-balance-scale text-danger me-2"></i>Stock Adjustment / Correction
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <!-- Correction Type Dropdown (Always Visible) -->
                                <div class="mb-3">
                                    <label for="correction_type" class="form-label fw-semibold">
                                        Correction Type <span class="text-danger" id="correction_type_required" style="display:none;">*</span>
                                    </label>
                                    <select class="form-select @error('correction_type') is-invalid @enderror" 
                                            id="correction_type" name="correction_type">
                                        <option value="">-- Select the type of stock adjustment --</option>
                                        <option value="purchase_correction" {{ old('correction_type') == 'purchase_correction' ? 'selected' : '' }}>
                                            Purchase Entry Correction
                                        </option>
                                        <option value="damage_spoilage" {{ old('correction_type') == 'damage_spoilage' ? 'selected' : '' }}>
                                            Damage / Spoilage
                                        </option>
                                    </select>
                                    @error('correction_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Select the type of stock adjustment</small>
                                </div>

                                <!-- Purchase Entry Correction Fields -->
                                <div id="purchase_correction_section" class="border rounded p-3 bg-info bg-opacity-10" style="display:none;">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        <small class="text-muted mb-0">Used to fix incorrect purchase quantity or unit cost</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="adjusted_stock" class="form-label fw-semibold">Corrected Stock Quantity</label>
                                            <input type="number" class="form-control @error('adjusted_stock') is-invalid @enderror" 
                                                   id="adjusted_stock" name="adjusted_stock" 
                                                   value="{{ old('adjusted_stock') }}" 
                                                   step="0.01" min="0" placeholder="Enter corrected total quantity">
                                            @error('adjusted_stock')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Enter the correct stock quantity after fixing the error</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="corrected_unit_cost" class="form-label fw-semibold">Corrected Unit Cost (₩)</label>
                                            <input type="number" class="form-control @error('corrected_unit_cost') is-invalid @enderror" 
                                                   id="corrected_unit_cost" name="corrected_unit_cost" 
                                                   value="{{ old('corrected_unit_cost') }}" 
                                                   step="0.01" min="0" placeholder="Leave blank if no change">
                                            @error('corrected_unit_cost')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Enter the correct unit cost to update purchase expense</small>
                                        </div>

                                        <div class="col-12">
                                            <label for="adjustment_notes" class="form-label fw-semibold">Adjustment Reason / Notes</label>
                                            <textarea class="form-control @error('adjustment_notes') is-invalid @enderror" 
                                                      id="adjustment_notes" name="adjustment_notes" 
                                                      rows="2" placeholder="Explain why this adjustment is needed">{{ old('adjustment_notes') }}</textarea>
                                            @error('adjustment_notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Required if making a stock adjustment</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Damage / Spoilage Fields -->
                                <div id="damage_spoilage_section" class="border rounded p-3 bg-warning bg-opacity-10" style="display:none;">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-warning text-dark me-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Warning
                                        </span>
                                        <small class="text-muted mb-0">Damaged stock will be deducted and recorded as loss</small>
                                    </div>
                                    <div class="alert alert-warning border-warning mb-3 py-2">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Important:</strong> Damage/Spoilage does NOT affect your income, expenses, or cash balance. 
                                            The item was already purchased (expense already recorded). This only reduces your inventory asset.
                                        </small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="adjusted_stock_damage" class="form-label fw-semibold">Damage Quantity</label>
                                            <input type="number" class="form-control @error('adjusted_stock') is-invalid @enderror" 
                                                   id="adjusted_stock_damage" 
                                                   value="{{ old('adjusted_stock') }}" 
                                                   step="0.01" min="0" placeholder="e.g., 20 (amount damaged)">
                                            @error('adjusted_stock')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Enter quantity lost due to damage or spoilage</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="adjustment_notes_damage" class="form-label fw-semibold">Adjustment Reason / Notes</label>
                                            <textarea class="form-control @error('adjustment_notes') is-invalid @enderror" 
                                                      id="adjustment_notes_damage" 
                                                      rows="3" placeholder="Explain the reason for damage/spoilage">{{ old('adjustment_notes') }}</textarea>
                                            @error('adjustment_notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Required if making a stock adjustment</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        // Dynamic correction type section visibility
                        document.addEventListener('DOMContentLoaded', function() {
                            const correctionType = document.getElementById('correction_type');
                            const purchaseCorrectionSection = document.getElementById('purchase_correction_section');
                            const damageSpoilageSection = document.getElementById('damage_spoilage_section');
                            const correctionRequired = document.getElementById('correction_type_required');
                            
                            // Get all input fields
                            const adjustedStock = document.getElementById('adjusted_stock');
                            const adjustedStockDamage = document.getElementById('adjusted_stock_damage');
                            const correctedUnitCost = document.getElementById('corrected_unit_cost');
                            const adjustmentNotes = document.getElementById('adjustment_notes');
                            const adjustmentNotesDamage = document.getElementById('adjustment_notes_damage');
                            
                            function updateSectionVisibility() {
                                const selectedType = correctionType.value;
                                
                                // Hide all sections by default
                                purchaseCorrectionSection.style.display = 'none';
                                damageSpoilageSection.style.display = 'none';
                                
                                // Clear all hidden fields to prevent submission
                                if (selectedType !== 'purchase_correction') {
                                    adjustedStock.removeAttribute('name');
                                    correctedUnitCost.removeAttribute('name');
                                    adjustmentNotes.removeAttribute('name');
                                }
                                if (selectedType !== 'damage_spoilage') {
                                    adjustedStockDamage.removeAttribute('name');
                                    adjustmentNotesDamage.removeAttribute('name');
                                }
                                
                                // Show appropriate section and enable fields
                                if (selectedType === 'purchase_correction') {
                                    purchaseCorrectionSection.style.display = 'block';
                                    adjustedStock.setAttribute('name', 'adjusted_stock');
                                    correctedUnitCost.setAttribute('name', 'corrected_unit_cost');
                                    adjustmentNotes.setAttribute('name', 'adjustment_notes');
                                } else if (selectedType === 'damage_spoilage') {
                                    damageSpoilageSection.style.display = 'block';
                                    adjustedStockDamage.setAttribute('name', 'adjusted_stock');
                                    adjustmentNotesDamage.setAttribute('name', 'adjustment_notes');
                                }
                            }
                            
                            function checkRequirements() {
                                // Show required marker if any adjustment field has a value
                                const hasValue = adjustedStock.value || adjustedStockDamage.value || correctedUnitCost.value;
                                if (hasValue) {
                                    correctionRequired.style.display = 'inline';
                                } else {
                                    correctionRequired.style.display = 'none';
                                }
                            }
                            
                            // Event listeners
                            correctionType.addEventListener('change', updateSectionVisibility);
                            adjustedStock.addEventListener('input', checkRequirements);
                            adjustedStockDamage.addEventListener('input', checkRequirements);
                            correctedUnitCost.addEventListener('input', checkRequirements);
                            
                            // Initialize on page load
                            updateSectionVisibility();
                            checkRequirements();
                        });
                        </script>

                        <!-- Status Toggle -->
                        <div class="mb-4 pb-2 border-bottom">
                            <div class="form-check form-switch">
                                <!-- Hidden input ensures a value is always sent (unchecked = 0) -->
                                <input type="hidden" name="is_active" value="0">
                                <!-- Checkbox overrides hidden input when checked (checked = 1) -->
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1"
                                       {{ old('is_active', $item->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Active Item
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-outline-dark">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
