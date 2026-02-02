@extends('layouts.app')

@section('title', $item->name . ' - Restaurant Accounting')
@section('page-title', 'Item Details: ' . $item->name)

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Items
        </a>
    </div>

    <!-- Item Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>{{ $item->name }}</h5>
                @can('manage inventory')
                <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit Item
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">SKU:</th>
                            <td>{{ $item->sku ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $item->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Unit:</th>
                            <td><span class="badge bg-secondary">{{ $item->unit }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($item->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Current Stock:</th>
                            <td>
                                <span class="badge {{ $item->isLowStock() ? 'bg-danger' : 'bg-success' }} fs-6">
                                    {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Minimum Stock:</th>
                            <td>{{ number_format($item->minimum_stock, 2) }} {{ $item->unit }}</td>
                        </tr>
                        <tr>
                            <th>Unit Cost:</th>
                            <td>₩{{ number_format($item->unit_cost, 0) }}</td>
                        </tr>
                        <tr>
                            <th>Total Value:</th>
                            <td><strong>₩{{ number_format($item->stock_value, 0) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Recipes -->
    @if($item->usageRecipes->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Used in Recipes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Food Category</th>
                            <th>Quantity Per Sale</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->usageRecipes as $recipe)
                        <tr>
                            <td>{{ $recipe->category->name }}</td>
                            <td>{{ number_format($recipe->quantity_per_sale, 2) }} {{ $item->unit }}</td>
                            <td>
                                @if($recipe->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Stock Movements -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Stock Movement History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>Balance After</th>
                            <th>Notes</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->stockMovements as $movement)
                        <tr>
                            <td>{{ $movement->movement_date->format('M d, Y') }}</td>
                            <td>
                                @if($movement->type == 'in')
                                    <span class="badge bg-success">Stock In</span>
                                @elseif($movement->type == 'out')
                                    <span class="badge bg-danger">Stock Out</span>
                                @elseif($movement->type == 'usage')
                                    <span class="badge bg-info">Usage</span>
                                @elseif($movement->type == 'adjustment' && $movement->reference_type == 'damage_spoilage')
                                    <span class="badge bg-warning text-dark">Damage/Spoilage</span>
                                @elseif($movement->type == 'sale')
                                    <span class="badge bg-primary">Sale</span>
                                @elseif($movement->type == 'opening')
                                    <span class="badge bg-secondary">Opening Stock</span>
                                @else
                                    <span class="badge bg-secondary">Adjustment</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->type == 'in' || $movement->type == 'opening')
                                    <span class="text-success">+{{ number_format(abs($movement->quantity), 2) }}</span>
                                @else
                                    <span class="text-danger">-{{ number_format(abs($movement->quantity), 2) }}</span>
                                @endif
                                {{ $item->unit }}
                            </td>
                            <td>
                                @if($movement->total_cost)
                                    ₩{{ number_format($movement->total_cost, 0) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format($movement->balance_after, 2) }} {{ $item->unit }}</td>
                            <td>{{ Str::limit($movement->notes ?? '-', 40) }}</td>
                            <td>{{ $movement->creator->name ?? 'System' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No stock movements yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
