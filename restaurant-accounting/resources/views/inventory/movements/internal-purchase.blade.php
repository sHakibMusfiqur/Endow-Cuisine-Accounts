@extends('layouts.app')

@section('title', 'Internal Inventory Consumption - Restaurant Accounting')
@section('page-title', 'Internal Inventory Consumption')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info rounded-top py-3">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center justify-content-between">
                        <span class="d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white me-2" style="width: 32px; height: 32px; font-size: 18px;">
                                <i class="fas fa-dolly"></i>
                            </span>
                            Internal Inventory Consumption (Single Item)
                        </span>
                        <a href="{{ route('inventory.movements.internal-purchase-multi') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-dolly-flatbed"></i> Multi-Item Version
                        </a>
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('inventory.movements.internal-purchase.store') }}" method="POST">
                        @csrf

                        <!-- Item Selection Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-cube me-2"></i>Item Selection</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-0">
                                    <label for="inventory_item_id" class="form-label">Select Item <span class="text-danger">*</span></label>
                                    <select class="form-select @error('inventory_item_id') is-invalid @enderror" 
                                            id="inventory_item_id" name="inventory_item_id" required>
                                        <option value="">Choose an item...</option>
                                        @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                data-unit="{{ $item->unit }}"
                                                data-cost="{{ $item->unit_cost }}"
                                                data-price="{{ $item->selling_price_per_unit }}"
                                                data-stock="{{ $item->current_stock }}"
                                                {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} (Available: {{ number_format($item->current_stock, 2) }} {{ $item->unit }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('inventory_item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select the inventory item you want to use internally</small>
                                </div>
                            </div>
                        </div>

                        <!-- Consumption Details Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Consumption Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control @error('quantity') is-invalid @enderror" 
                                               id="quantity" 
                                               name="quantity" 
                                               value="{{ old('quantity') }}" 
                                               step="0.01" 
                                               min="0.01" 
                                               required
                                               placeholder="e.g., 10">
                                        @error('quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Unit: <span id="unit-display">-</span> | Available: <span id="available-stock" class="text-info fw-bold">-</span></small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="cost_basis" class="form-label">Cost Basis <span class="text-danger">*</span></label>
                                        <select class="form-select @error('cost_basis') is-invalid @enderror" 
                                                id="cost_basis" 
                                                name="cost_basis" 
                                                required>
                                            <option value="">Select Cost Basis...</option>
                                            <option value="unit_cost" {{ old('cost_basis') == 'unit_cost' ? 'selected' : '' }}>Unit Cost (Purchase Price)</option>
                                            <option value="selling_price" {{ old('cost_basis') == 'selling_price' ? 'selected' : '' }}>Selling Price per Unit</option>
                                        </select>
                                        @error('cost_basis')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Choose pricing method for consumption cost</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="selected_price_display" class="form-label">Selected Price (KRW)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₩</span>
                                            <input type="text" 
                                                   class="form-control bg-light text-primary fw-bold" 
                                                   id="selected_price_display" 
                                                   value="0" 
                                                   readonly>
                                        </div>
                                        <small class="text-muted" id="price-type-label">Select cost basis to see price</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Total Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">₩</span>
                                            <input type="text" 
                                                   class="form-control bg-light text-end fw-bold text-danger" 
                                                   id="total-cost-display" 
                                                   value="0" 
                                                   readonly>
                                        </div>
                                        <small class="text-muted">Calculated as Quantity × Selected Price</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="movement_date" class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" 
                                               class="form-control @error('movement_date') is-invalid @enderror" 
                                               id="movement_date" 
                                               name="movement_date" 
                                               value="{{ old('movement_date', date('Y-m-d')) }}" 
                                               required>
                                        @error('movement_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select @error('payment_method_id') is-invalid @enderror" 
                                                id="payment_method_id" name="payment_method_id" required>
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
                                        <small class="text-muted">How this expense will be recorded</small>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Optional notes (purpose, event, reason for consumption, etc.)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Dual-Entry Accounting:</strong> This creates TWO linked transactions from one action:
                            <ul class="mb-0 mt-2">
                                <li><strong>Inventory Income</strong> (source: inventory) – Reduces inventory value</li>
                                <li><strong>Restaurant Expense</strong> (source: restaurant) – Records operational cost</li>
                            </ul>
                            <small class="text-muted d-block mt-2">
                                <strong>Net Effect:</strong> Inventory stock decreases, Today's Expense increases, Today's Income increases (from inventory). 
                                Current Balance returns to same (internal transfer). Both transactions use the same amount based on your selected cost basis.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Inventory
                            </a>
                            <button type="submit" class="btn btn-info text-white">
                                <i class="fas fa-check"></i> Process Internal Consumption
                            </button>
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
    const quantityInput = document.getElementById('quantity');
    const costBasisSelect = document.getElementById('cost_basis');
    const unitDisplay = document.getElementById('unit-display');
    const availableStock = document.getElementById('available-stock');
    const selectedPriceDisplay = document.getElementById('selected_price_display');
    const priceTypeLabel = document.getElementById('price-type-label');
    const totalCostDisplay = document.getElementById('total-cost-display');
    
    let currentUnitCost = 0;
    let currentSellingPrice = 0;
    let selectedPrice = 0;

    // When item is selected, populate unit, costs, and available stock
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const unit = selectedOption.dataset.unit;
            const unitCost = parseFloat(selectedOption.dataset.cost) || 0;
            const sellingPrice = parseFloat(selectedOption.dataset.price) || 0;
            const stock = parseFloat(selectedOption.dataset.stock) || 0;
            
            unitDisplay.textContent = unit;
            availableStock.textContent = stock.toFixed(2) + ' ' + unit;
            currentUnitCost = unitCost;
            currentSellingPrice = sellingPrice;
            
            // Update selected price based on current cost basis
            updateSelectedPrice();
        } else {
            unitDisplay.textContent = '-';
            availableStock.textContent = '-';
            currentUnitCost = 0;
            currentSellingPrice = 0;
            selectedPrice = 0;
            selectedPriceDisplay.value = '0';
            priceTypeLabel.textContent = 'Select cost basis to see price';
            totalCostDisplay.value = '0';
        }
    });

    // When cost basis is changed, update selected price
    costBasisSelect.addEventListener('change', function() {
        updateSelectedPrice();
    });

    // Update selected price based on cost basis
    function updateSelectedPrice() {
        const costBasis = costBasisSelect.value;
        
        if (!costBasis) {
            selectedPrice = 0;
            selectedPriceDisplay.value = '0';
            priceTypeLabel.textContent = 'Select cost basis to see price';
            calculateTotal();
            return;
        }

        if (costBasis === 'unit_cost') {
            selectedPrice = currentUnitCost;
            priceTypeLabel.textContent = 'Unit Cost (Purchase Price)';
        } else if (costBasis === 'selling_price') {
            selectedPrice = currentSellingPrice;
            priceTypeLabel.textContent = 'Selling Price per Unit';
        }

        selectedPriceDisplay.value = selectedPrice.toLocaleString('ko-KR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        calculateTotal();
    }

    // Calculate total amount
    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const total = quantity * selectedPrice;
        totalCostDisplay.value = total.toLocaleString('ko-KR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }

    quantityInput.addEventListener('input', calculateTotal);

    // Initialize if there's a value (old value after validation error)
    if (itemSelect.value) {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const unit = selectedOption.dataset.unit;
        const unitCost = parseFloat(selectedOption.dataset.cost) || 0;
        const sellingPrice = parseFloat(selectedOption.dataset.price) || 0;
        const stock = parseFloat(selectedOption.dataset.stock) || 0;
        
        unitDisplay.textContent = unit;
        availableStock.textContent = stock.toFixed(2) + ' ' + unit;
        currentUnitCost = unitCost;
        currentSellingPrice = sellingPrice;
        
        updateSelectedPrice();
    }
});
</script>
@endsection
