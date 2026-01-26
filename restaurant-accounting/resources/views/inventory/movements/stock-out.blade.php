@extends('layouts.app')

@section('title', 'Remove Stock - Restaurant Accounting')
@section('page-title', 'Remove Stock (Waste/Damage)')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-minus-circle me-2 text-danger"></i>Remove Stock</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Use this to record waste, damage, or manual stock adjustments (reductions only).
                    </div>

                    <form action="{{ route('inventory.movements.stock-out.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="inventory_item_id" class="form-label">Select Item <span class="text-danger">*</span></label>
                            <select class="form-select @error('inventory_item_id') is-invalid @enderror" 
                                    id="inventory_item_id" name="inventory_item_id" required>
                                <option value="">Choose an item...</option>
                                @foreach($items as $item)
                                <option value="{{ $item->id }}" 
                                        data-unit="{{ $item->unit }}"
                                        data-current="{{ $item->current_stock }}"
                                        {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} (Available: {{ number_format($item->current_stock, 2) }} {{ $item->unit }})
                                </option>
                                @endforeach
                            </select>
                            @error('inventory_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity to Remove <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="{{ old('quantity') }}" 
                                   step="0.01" 
                                   min="0.01" 
                                   required
                                   placeholder="Enter quantity">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Unit: <span id="unit-display">-</span> | 
                                Available: <span id="stock-display">-</span>
                            </small>
                        </div>

                        <div class="mb-3">
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

                        <div class="mb-3">
                            <label for="notes" class="form-label">Reason/Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      required
                                      placeholder="Reason for removal (e.g., spoilage, damage, expired)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-check me-1"></i>Remove Stock
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('inventory_item_id');
    const unitDisplay = document.getElementById('unit-display');
    const stockDisplay = document.getElementById('stock-display');

    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const unit = selectedOption.dataset.unit;
            const current = selectedOption.dataset.current;
            unitDisplay.textContent = unit;
            stockDisplay.textContent = `${parseFloat(current).toFixed(2)} ${unit}`;
        } else {
            unitDisplay.textContent = '-';
            stockDisplay.textContent = '-';
        }
    });
});
</script>
@endsection
