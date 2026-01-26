@extends('layouts.app')

@section('title', 'Low Stock Items - Restaurant Accounting')
@section('page-title', 'Low Stock Alert')

@section('content')
<div class="container-fluid">
    <!-- Alert Banner -->
    <div class="alert alert-warning border-warning border-2 mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-3x me-3"></i>
            <div>
                <h5 class="mb-1">Low Stock Alert</h5>
                <p class="mb-0">The following items have stock levels at or below their minimum threshold. Please restock soon.</p>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to All Items
        </a>
    </div>

    <!-- Low Stock Items -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock Items ({{ $items->total() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Priority</th>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th>Minimum Required</th>
                            <th>Shortage</th>
                            <th>Unit Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                        <tr>
                            <td>
                                @php
                                    $percentage = ($item->current_stock / $item->minimum_stock) * 100;
                                @endphp
                                @if($percentage <= 25)
                                    <span class="badge bg-danger">Critical</span>
                                @elseif($percentage <= 50)
                                    <span class="badge bg-warning">High</span>
                                @else
                                    <span class="badge bg-info">Low</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->sku)
                                <br><small class="text-muted">SKU: {{ $item->sku }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger fs-6">
                                    {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                                </span>
                            </td>
                            <td>{{ number_format($item->minimum_stock, 2) }} {{ $item->unit }}</td>
                            <td class="text-danger">
                                <strong>-{{ number_format($item->minimum_stock - $item->current_stock, 2) }} {{ $item->unit }}</strong>
                            </td>
                            <td>₩{{ number_format($item->unit_cost, 0) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    {{-- View button --}}
                                    <a href="{{ route('inventory.items.show', $item) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       data-bs-toggle="tooltip" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- Restock button - locked to this specific item --}}
                                    @can('manage inventory')
                                    <a href="{{ route('inventory.movements.stock-in', ['item_id' => $item->id]) }}" 
                                       class="btn btn-success btn-sm" 
                                       data-bs-toggle="tooltip" 
                                       title="Restock This Item">
                                        <i class="fas fa-box"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3 d-block"></i>
                                <h5 class="text-success">All Good!</h5>
                                <p class="text-muted">No items are below their minimum stock level.</p>
                                <a href="{{ route('inventory.items.index') }}" class="btn btn-primary">
                                    View All Items
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->count() > 0)
            <div class="mt-4">
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Tip:</strong> Set up Usage Recipes to automatically track which items are used in your food sales. 
                    This helps predict when items will run low.
                </div>
            </div>
            @endif

            <!-- Pagination -->
            <x-pagination :items="$items" />
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
