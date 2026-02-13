@extends('layouts.app')

@section('title', 'Multi-Item Internal Inventory Consumption - Restaurant Accounting')
@section('page-title', 'Internal Inventory Consumption')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-info rounded-top py-3">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white me-2" style="width: 32px; height: 32px; font-size: 18px;">
                            <i class="fas fa-dolly-flatbed"></i>
                        </span>
                        Internal Inventory Consumption
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('inventory.movements.internal-purchase-multi.store') }}" method="POST" id="multi-item-form">
                        @csrf

                        <!-- Common Details Card -->
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-clipboard me-2"></i>Transaction Details</h6>
                            </div>
                            <div class="card-body">
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
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="2" 
                                              placeholder="Optional notes (applies to all items)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items Table Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Items to Consume</h6>
                                <button type="button" class="btn btn-sm btn-success" id="add-item-btn">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="items-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="30%">Item</th>
                                                <th width="15%">Quantity</th>
                                                <th width="15%">Cost Basis</th>
                                                <th width="15%">Unit Price</th>
                                                <th width="15%">Line Total</th>
                                                <th width="10%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-tbody">
                                            <!-- Rows will be added dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total Display -->
                        <div class="card mb-3 border-danger">
                            <div class="card-body text-center py-4 px-3" style="background-color: #fff5f5;">
                                <div class="text-uppercase text-muted small mb-2">Total Amount</div>
                                <div id="grand-total" class="fs-1 fw-bold text-danger">₩0</div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Multi-Item Dual-Entry Accounting:</strong> Each item creates TWO linked transactions:
                            <ul class="mb-0 mt-2">
                                <li><strong>Inventory Income</strong> (source: inventory) – Reduces inventory value</li>
                                <li><strong>Restaurant Expense</strong> (source: restaurant) – Records operational cost</li>
                            </ul>
                            <small class="text-muted d-block mt-2">
                                All items share the same batch ID, date, and payment method.
                            </small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back Transactions
                            </a>
                            <button type="submit" class="btn btn-info text-white" id="submit-btn" disabled>
                                <i class="fas fa-check"></i> Process Multi-Item Consumption
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Row Template -->
<template id="item-row-template">
    <tr class="item-row">
        <td>
            <select class="form-select form-select-sm item-select" name="items[INDEX][inventory_item_id]" required>
                <option value="">Choose item...</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" 
                        data-unit="{{ $item->unit }}"
                        data-cost="{{ $item->unit_cost }}"
                        data-price="{{ $item->selling_price_per_unit }}"
                        data-stock="{{ $item->current_stock }}"
                        data-name="{{ $item->name }}">
                    {{ $item->name }} ({{ number_format($item->current_stock, 2) }} {{ $item->unit }})
                </option>
                @endforeach
            </select>
            <small class="text-muted unit-display"></small>
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm quantity-input" 
                   name="items[INDEX][quantity]" 
                   step="0.01" 
                   min="0.01" 
                   placeholder="0.00"
                   required>
            <small class="text-danger stock-warning" style="display: none;"></small>
        </td>
        <td>
            <select class="form-select form-select-sm cost-basis-select" name="items[INDEX][cost_basis]" required>
                <option value="">Select...</option>
                <option value="unit_cost">Unit Cost</option>
                <option value="selling_price">Selling Price</option>
            </select>
        </td>
        <td>
            <div class="text-primary fw-bold unit-price-display">-</div>
        </td>
        <td>
            <div class="text-danger fw-bold line-total-display">₩0</div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 0;
    const itemsTable = document.getElementById('items-tbody');
    const addItemBtn = document.getElementById('add-item-btn');
    const submitBtn = document.getElementById('submit-btn');
    const grandTotalDisplay = document.getElementById('grand-total');
    const template = document.getElementById('item-row-template');

    // Add first row automatically
    addItemRow();

    // Add item button click
    addItemBtn.addEventListener('click', addItemRow);

    function addItemRow() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.item-row');
        
        // Update name attributes with current index
        row.querySelectorAll('[name*="INDEX"]').forEach(el => {
            el.name = el.name.replace('INDEX', rowIndex);
        });

        // Add event listeners
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const costBasisSelect = row.querySelector('.cost-basis-select');
        const removeBtn = row.querySelector('.remove-item-btn');

        itemSelect.addEventListener('change', () => updateRow(row));
        quantityInput.addEventListener('input', () => updateRow(row));
        costBasisSelect.addEventListener('change', () => updateRow(row));
        removeBtn.addEventListener('click', () => removeRow(row));

        itemsTable.appendChild(row);
        rowIndex++;
        updateAvailableItems();
        updateSubmitButton();
    }

    function removeRow(row) {
        if (itemsTable.children.length > 1) {
            row.remove();
            updateAvailableItems();
            updateGrandTotal();
            updateSubmitButton();
        } else {
            alert('At least one item is required.');
        }
    }

    function getSelectedItemIds() {
        const selectedIds = [];
        document.querySelectorAll('.item-row').forEach(row => {
            const itemSelect = row.querySelector('.item-select');
            if (itemSelect.value) {
                selectedIds.push(itemSelect.value);
            }
        });
        return selectedIds;
    }

    function updateAvailableItems() {
        const selectedIds = getSelectedItemIds();
        
        document.querySelectorAll('.item-row').forEach(row => {
            const itemSelect = row.querySelector('.item-select');
            const currentValue = itemSelect.value;
            
            Array.from(itemSelect.options).forEach(option => {
                if (option.value && option.value !== currentValue) {
                    option.disabled = selectedIds.includes(option.value);
                }
            });
        });
    }

    function updateRow(row) {
        const itemSelect = row.querySelector('.item-select');
        const quantityInput = row.querySelector('.quantity-input');
        const costBasisSelect = row.querySelector('.cost-basis-select');
        const unitDisplay = row.querySelector('.unit-display');
        const unitPriceDisplay = row.querySelector('.unit-price-display');
        const lineTotalDisplay = row.querySelector('.line-total-display');
        const stockWarning = row.querySelector('.stock-warning');

        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        
        if (!selectedOption.value) {
            unitDisplay.textContent = '';
            unitPriceDisplay.textContent = '-';
            lineTotalDisplay.textContent = '₩0';
            stockWarning.style.display = 'none';
            updateAvailableItems();
            updateGrandTotal();
            return;
        }

        const selectedIds = getSelectedItemIds();
        const duplicateCount = selectedIds.filter(id => id === selectedOption.value).length;
        if (duplicateCount > 1) {
            alert('This item is already added.');
            itemSelect.value = '';
            unitDisplay.textContent = '';
            unitPriceDisplay.textContent = '-';
            lineTotalDisplay.textContent = '₩0';
            stockWarning.style.display = 'none';
            updateAvailableItems();
            updateGrandTotal();
            return;
        }

        updateAvailableItems();

        const unit = selectedOption.dataset.unit;
        const unitCost = parseFloat(selectedOption.dataset.cost) || 0;
        const sellingPrice = parseFloat(selectedOption.dataset.price) || 0;
        const availableStock = parseFloat(selectedOption.dataset.stock) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const costBasis = costBasisSelect.value;

        unitDisplay.textContent = `Unit: ${unit} | Available: ${availableStock.toFixed(2)} ${unit}`;

        // Check stock availability
        if (quantity > availableStock) {
            stockWarning.textContent = `Exceeds stock by ${(quantity - availableStock).toFixed(2)} ${unit}`;
            stockWarning.style.display = 'block';
        } else {
            stockWarning.style.display = 'none';
        }

        // Calculate based on cost basis
        let unitPrice = 0;
        if (costBasis === 'unit_cost') {
            unitPrice = unitCost;
        } else if (costBasis === 'selling_price') {
            unitPrice = sellingPrice;
        }

        const lineTotal = quantity * unitPrice;

        unitPriceDisplay.textContent = unitPrice > 0 ? `₩${unitPrice.toLocaleString('ko-KR', {minimumFractionDigits: 0})}` : '-';
        lineTotalDisplay.textContent = `₩${lineTotal.toLocaleString('ko-KR', {minimumFractionDigits: 0})}`;

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const quantityInput = row.querySelector('.quantity-input');
            const costBasisSelect = row.querySelector('.cost-basis-select');
            const itemSelect = row.querySelector('.item-select');
            
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            if (selectedOption.value) {
                const quantity = parseFloat(quantityInput.value) || 0;
                const costBasis = costBasisSelect.value;
                const unitCost = parseFloat(selectedOption.dataset.cost) || 0;
                const sellingPrice = parseFloat(selectedOption.dataset.price) || 0;
                
                let unitPrice = 0;
                if (costBasis === 'unit_cost') {
                    unitPrice = unitCost;
                } else if (costBasis === 'selling_price') {
                    unitPrice = sellingPrice;
                }
                
                grandTotal += quantity * unitPrice;
            }
        });

        grandTotalDisplay.textContent = `₩${grandTotal.toLocaleString('ko-KR', {minimumFractionDigits: 0})}`;
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const rows = document.querySelectorAll('.item-row');
        let isValid = rows.length > 0;

        rows.forEach(row => {
            const itemSelect = row.querySelector('.item-select');
            const quantityInput = row.querySelector('.quantity-input');
            const costBasisSelect = row.querySelector('.cost-basis-select');
            const stockWarning = row.querySelector('.stock-warning');

            if (!itemSelect.value || !quantityInput.value || !costBasisSelect.value || 
                parseFloat(quantityInput.value) <= 0 || stockWarning.style.display !== 'none') {
                isValid = false;
            }
        });

        submitBtn.disabled = !isValid;
    }
});
</script>

<style>
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    
    #items-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .item-row:hover {
        background-color: #f8f9fa;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .form-control-sm, .form-select-sm {
            font-size: 0.8rem;
        }
    }
</style>
@endsection
