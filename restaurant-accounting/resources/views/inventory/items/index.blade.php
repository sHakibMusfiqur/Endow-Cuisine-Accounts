@extends('layouts.app')

@section('title', 'Inventory Items - Restaurant Accounting')
@section('page-title', 'Inventory Items')

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ $items->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-boxes fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock Items</h6>
                            <h3 class="mb-0 {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}">{{ $lowStockCount }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Stock Value</h6>
                            <h3 class="mb-0">₩{{ number_format($totalValue, 0) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-won-sign fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>All Items</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('inventory.items.low-stock') }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                    </a>
                    @can('manage inventory')
                    <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add New Item
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th>Min. Stock</th>
                            <th>Unit Cost</th>
                            <th>Stock Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->description)
                                <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $item->sku ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $item->isLowStock() ? 'bg-danger' : 'bg-success' }}">
                                    {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                                </span>
                            </td>
                            <td>{{ number_format($item->minimum_stock, 2) }} {{ $item->unit }}</td>
                            <td>₩{{ number_format($item->unit_cost, 0) }}</td>
                            <td>₩{{ number_format($item->stock_value, 0) }}</td>
                            <td>
                                @if($item->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    {{-- View button --}}
                                    <a href="{{ route('inventory.items.show', $item) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       data-bs-toggle="tooltip" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Edit button --}}
                                    @can('manage inventory')
                                    <a href="{{ route('inventory.items.edit', $item) }}" 
                                       class="btn btn-outline-warning btn-sm" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Item">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endcan
                                    
                                    {{-- Delete button: Admin only --}}
                                    @can('delete inventory')
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            data-bs-toggle="tooltip" 
                                            title="Delete Item"
                                            onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                    
                                    {{-- Restock button - always visible for authorized users --}}
                                    @can('manage inventory')
                                    <a href="{{ route('inventory.movements.stock-in', ['item_id' => $item->id]) }}" 
                                       class="btn btn-outline-success btn-sm rounded" 
                                       data-bs-toggle="tooltip" 
                                       title="Restock Item">
                                        <i class="fas fa-box"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                No inventory items found. Add your first item to get started.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-pagination :items="$items" />
        </div>
    </div>
</div>

@can('delete inventory')
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5 class="mb-3">You are about to delete this inventory item</h5>
                    <p class="text-muted mb-2">Item: <strong id="item-name-display"></strong></p>
                </div>
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> All related stock history will also be removed.<br>
                    <strong>This action cannot be undone.</strong>
                </div>
                <div class="mt-3 text-center">
                    <p class="mb-0 text-muted small">Do you want to continue?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fas fa-trash me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (hidden) -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let deleteItemId = null;
let deleteItemName = null;

function confirmDelete(itemId, itemName) {
    // Store the item details
    deleteItemId = itemId;
    deleteItemName = itemName;
    
    // Update modal content
    document.getElementById('item-name-display').textContent = itemName;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Handle confirm button click
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (deleteItemId) {
                const form = document.getElementById('delete-form');
                form.action = `/inventory/items/${deleteItemId}`;
                form.submit();
            }
        });
    }
});
</script>
@endcan
@endsection
