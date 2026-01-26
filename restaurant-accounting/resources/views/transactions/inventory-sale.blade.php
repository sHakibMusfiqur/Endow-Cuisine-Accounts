@extends('layouts.app')

@section('title', 'Inventory Item Sale - Restaurant Accounting')
@section('page-title', 'Inventory Item Sale')

@section('content')
<style>
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }

        .col-md-8 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0;
        }

        .card {
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .card-body {
            padding: 15px;
        }

        .form-label {
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            font-size: 16px; /* Prevents zoom on iOS */
            padding: 10px 12px;
        }

        .input-group-text {
            font-size: 0.9rem;
            padding: 10px 12px;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
            justify-content: center;
        }

        .d-flex.justify-content-between {
            flex-direction: column-reverse;
        }
    }

    @media (max-width: 480px) {
        .card-body {
            padding: 12px;
        }

        .form-label {
            font-size: 0.85rem;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            padding: 8px 10px;
        }

        .btn {
            padding: 10px 15px;
            font-size: 0.9rem;
        }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
        .form-control,
        .form-select {
            min-height: 44px;
        }

        .btn {
            min-height: 44px;
        }
    }

    .stock-info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .stock-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .stock-info-item:last-child {
        margin-bottom: 0;
    }

    .stock-info-label {
        font-weight: 600;
        color: #495057;
    }

    .stock-info-value {
        color: #212529;
    }

    .stock-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .stock-danger {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .total-amount-display {
        background: #d1ecf1;
        border: 2px solid #0c5460;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-top: 20px;
    }

    .total-amount-label {
        font-size: 0.9rem;
        color: #0c5460;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .total-amount-value {
        font-size: 2rem;
        color: #0c5460;
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-box"></i> Inventory Item Sale Transaction</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form action="{{ route('transactions.inventory-sale.store') }}" method="POST" id="inventory-sale-form">
                        @csrf

                        <div class="mb-3">
                            <label for="date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                   id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="inventory_item_id" class="form-label">Inventory Item <span class="text-danger">*</span></label>
                            <select class="form-select @error('inventory_item_id') is-invalid @enderror" 
                                    id="inventory_item_id" name="inventory_item_id" required>
                                <option value="" disabled selected>Select Inventory Item</option>
                                @foreach($inventoryItems as $item)
                                <option value="{{ $item->id }}" 
                                        data-stock="{{ $item->current_stock }}"
                                        data-unit="{{ $item->unit }}"
                                        data-cost="{{ $item->unit_cost }}"
                                        data-selling-price="{{ $item->selling_price_per_unit }}"
                                        data-name="{{ $item->name }}"
                                        data-min-stock="{{ $item->minimum_stock }}"
                                        {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} (Stock: {{ number_format($item->current_stock, 2) }} {{ $item->unit }})
                                </option>
                                @endforeach
                            </select>
                            @error('inventory_item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stock Information Card (Hidden initially) -->
                        <div id="stock-info-card" class="stock-info-card" style="display: none;">
                            <h6 class="mb-3"><i class="fas fa-info-circle"></i> Stock Information</h6>
                            <div class="stock-info-item">
                                <span class="stock-info-label">Available Stock:</span>
                                <span class="stock-info-value" id="available-stock">-</span>
                            </div>
                            <div class="stock-info-item">
                                <span class="stock-info-label">Unit:</span>
                                <span class="stock-info-value" id="item-unit">-</span>
                            </div>
                            <div class="stock-info-item">
                                <span class="stock-info-label">Cost per Unit:</span>
                                <span class="stock-info-value" id="unit-cost">-</span>
                            </div>
                            <div class="stock-info-item">
                                <span class="stock-info-label">Minimum Stock Level:</span>
                                <span class="stock-info-value" id="min-stock">-</span>
                            </div>
                        </div>

                        <!-- Stock Warning (Hidden initially) -->
                        <div id="stock-warning" class="stock-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This sale will bring stock below minimum level!
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity to Sell <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" value="{{ old('quantity') }}" 
                                   step="0.01" min="0.01" placeholder="Enter quantity" required>
                            @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="quantity-hint"></small>
                        </div>

                        <div class="mb-3">
                            <label for="selling_price_display" class="form-label">Selling Price per Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">₩</span>
                                <input type="text" 
                                       class="form-control bg-light" 
                                       id="selling_price_display" 
                                       value="-" 
                                       readonly 
                                       disabled>
                            </div>
                            <small class="form-text text-muted">This price is set in the inventory item master and cannot be changed during transaction</small>
                        </div>

                        <!-- Final Price Display (Auto-calculated) - PROMINENTLY STYLED -->
                        <div class="mb-4">
                            <div class="card border-danger shadow-sm" style="background-color: #fff5f5;">
                                <div class="card-body text-center py-4">
                                    <label for="final_price_display" class="form-label fw-semibold mb-3" style="font-size: 1rem; color: #666;">
                                        Total Amount
                                    </label>
                                    <div class="text-center fw-bold text-danger" id="final_price_display" style="font-size: 2.5rem;">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method_id') is-invalid @enderror" 
                                    id="payment_method_id" name="payment_method_id" required>
                                <option value="" disabled selected>Select Payment Method</option>
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

                        <div class="mb-3">
                            <label for="description" class="form-label">Description / Note</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Optional: Add any additional notes about this sale">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Leave blank for auto-generated description</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transactions.create') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-success" id="submit-btn">
                                <i class="fas fa-check-circle"></i> Record Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemSelect = document.getElementById('inventory_item_id');
        const quantityInput = document.getElementById('quantity');
        const sellingPriceDisplay = document.getElementById('selling_price_display');
        const finalPriceDisplay = document.getElementById('final_price_display');
        const stockInfoCard = document.getElementById('stock-info-card');
        const stockWarning = document.getElementById('stock-warning');
        const submitBtn = document.getElementById('submit-btn');
        const form = document.getElementById('inventory-sale-form');

        let currentStock = 0;
        let currentUnit = '';
        let minStock = 0;
        let sellingPrice = 0;

        // Update stock information when item is selected
        itemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                currentStock = parseFloat(selectedOption.dataset.stock);
                currentUnit = selectedOption.dataset.unit;
                const unitCost = parseFloat(selectedOption.dataset.cost);
                const itemName = selectedOption.dataset.name;
                minStock = parseFloat(selectedOption.dataset.minStock);
                sellingPrice = parseFloat(selectedOption.dataset.sellingPrice);

                // Update stock info card
                document.getElementById('available-stock').textContent = 
                    `${formatNumber(currentStock)} ${currentUnit}`;
                document.getElementById('item-unit').textContent = currentUnit;
                document.getElementById('unit-cost').textContent = 
                    `₩ ${formatNumber(unitCost)}`;
                document.getElementById('min-stock').textContent = 
                    `${formatNumber(minStock)} ${currentUnit}`;
                
                stockInfoCard.style.display = 'block';
                
                // Update quantity hint
                document.getElementById('quantity-hint').textContent = 
                    `Available: ${formatNumber(currentStock)} ${currentUnit}`;
                
                // Display selling price (read-only)
                sellingPriceDisplay.value = formatNumber(sellingPrice);
                
                // Recalculate total
                calculateTotal();
            } else {
                stockInfoCard.style.display = 'none';
                stockWarning.style.display = 'none';
                sellingPriceDisplay.value = '-';
                finalPriceDisplay.textContent = '-';
                sellingPrice = 0;
            }
        });

        // Calculate total and validate stock
        function calculateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            
            // Update Final Price (INSTANT calculation)
            if (quantity > 0 && sellingPrice > 0) {
                const totalAmount = quantity * sellingPrice;
                finalPriceDisplay.textContent = '₩' + formatNumber(totalAmount);
            } else {
                finalPriceDisplay.textContent = '-';
            }
            
            // Check stock availability
            if (quantity > 0 && currentStock > 0) {
                if (quantity > currentStock) {
                    // Insufficient stock
                    quantityInput.classList.add('is-invalid');
                    submitBtn.disabled = true;
                    
                    // Show error message
                    let errorDiv = document.getElementById('quantity-error');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.id = 'quantity-error';
                        errorDiv.className = 'invalid-feedback d-block';
                        quantityInput.parentNode.appendChild(errorDiv);
                    }
                    errorDiv.textContent = `Insufficient stock! Available: ${formatNumber(currentStock)} ${currentUnit}`;
                } else {
                    quantityInput.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                    
                    const errorDiv = document.getElementById('quantity-error');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                    
                    // Check if will go below minimum stock
                    const remainingStock = currentStock - quantity;
                    if (remainingStock < minStock) {
                        stockWarning.style.display = 'block';
                    } else {
                        stockWarning.style.display = 'none';
                    }
                }
            }
        }

        // Recalculate on quantity change (INSTANT - triggers on keyup, change, input)
        quantityInput.addEventListener('input', calculateTotal);
        quantityInput.addEventListener('keyup', calculateTotal);
        quantityInput.addEventListener('change', calculateTotal);

        // Format numbers with commas
        function formatNumber(num) {
            return num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Prevent form submission if stock is insufficient
        form.addEventListener('submit', function(e) {
            const quantity = parseFloat(quantityInput.value) || 0;
            
            if (quantity > currentStock) {
                e.preventDefault();
                alert(`Insufficient stock! Available: ${formatNumber(currentStock)} ${currentUnit}`);
                return false;
            }
            
            if (quantity <= 0) {
                e.preventDefault();
                alert('Please enter a valid quantity greater than 0.');
                return false;
            }
            
            if (sellingPrice <= 0) {
                e.preventDefault();
                alert('This item does not have a valid selling price set. Please update the inventory item first.');
                return false;
            }
        });

        // Initialize if item was previously selected (validation error)
        if (itemSelect.value) {
            itemSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
@endsection
