@extends('layouts.app')

@section('title', 'Add Stock (Purchase) - Restaurant Accounting')
@section('page-title', 'Add Stock (Purchase)')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger rounded-top py-3">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white me-2" style="width: 32px; height: 32px; font-size: 18px;">
                            <i class="fas fa-box"></i>
                        </span>
                        Add Stock (Purchase)
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('inventory.movements.stock-in.store') }}" method="POST">
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
                                            id="inventory_item_id" name="inventory_item_id" required
                                            {{ request('item_id') ? 'disabled' : '' }}>
                                        <option value="">Choose an item...</option>
                                        @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                data-unit="{{ $item->unit }}"
                                                data-cost="{{ $item->unit_cost }}"
                                                {{ (request('item_id') == $item->id || old('inventory_item_id') == $item->id) ? 'selected' : '' }}>
                                            {{ $item->name }} (Current: {{ number_format($item->current_stock, 2) }} {{ $item->unit }})
                                        </option>
                                        @endforeach
                                    </select>
                                    {{-- Hidden input to ensure the value is submitted when disabled --}}
                                    @if(request('item_id'))
                                        <input type="hidden" name="inventory_item_id" value="{{ request('item_id') }}">
                                    @endif
                                    @error('inventory_item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(request('item_id'))
                                        <small class="text-success">
                                            <i class="fas fa-lock me-1"></i>Item locked for restock
                                        </small>
                                    @else
                                        <small class="text-muted">Select the inventory item you want to purchase</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Details Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Purchase Details</h6>
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
                                               placeholder="e.g., 25">
                                        @error('quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Unit: <span id="unit-display">-</span></small>
                                    </div>

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
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="movement_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
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
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Total Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₩</span>
                                        <input type="text" 
                                               class="form-control bg-light text-end fw-bold" 
                                               id="total-cost-display" 
                                               value="0" 
                                               readonly>
                                    </div>
                                    <small class="text-muted">Calculated as Quantity × Unit Cost</small>
                                </div>

                                <div class="mb-0">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Optional notes (supplier, invoice number, etc.)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This purchase will automatically create an expense transaction in your accounting records and update the item's inventory level.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Inventory
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-check"></i> Add Stock
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
    const unitCostInput = document.getElementById('unit_cost');
    const unitDisplay = document.getElementById('unit-display');
    const totalCostDisplay = document.getElementById('total-cost-display');

    // When item is selected, populate unit and default cost
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const unit = selectedOption.dataset.unit;
            const cost = selectedOption.dataset.cost;
            unitDisplay.textContent = unit;
            
            // Only set unit cost if it's currently empty
            if (!unitCostInput.value) {
                unitCostInput.value = cost;
            }
            
            calculateTotal();
        } else {
            unitDisplay.textContent = '-';
            totalCostDisplay.value = '0';
        }
    });

    // Calculate total cost
    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitCost = parseFloat(unitCostInput.value) || 0;
        const total = quantity * unitCost;
        totalCostDisplay.value = total.toLocaleString('ko-KR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    }

    quantityInput.addEventListener('input', calculateTotal);
    unitCostInput.addEventListener('input', calculateTotal);

    // Initialize if there's a value (pre-selected or old value)
    if (itemSelect.value) {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const unit = selectedOption.dataset.unit;
        const cost = selectedOption.dataset.cost;
        unitDisplay.textContent = unit;
        
        // Auto-populate unit cost for restock
        if (!unitCostInput.value && cost) {
            unitCostInput.value = cost;
        }
        
        calculateTotal();
    }
});
</script>
@endsection
