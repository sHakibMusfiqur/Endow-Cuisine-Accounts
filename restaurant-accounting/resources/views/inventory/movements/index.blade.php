@extends('layouts.app')

@section('title', 'Stock Movements - Restaurant Accounting')
@section('page-title', 'Stock Movements')

@section('content')
<div class="container-fluid">
    <!-- Action Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>All Stock Movements</h5>
                @can('manage inventory')
                <div class="d-flex gap-2">
                    <a href="{{ route('inventory.movements.stock-in') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Add Stock
                    </a>
                    <a href="{{ route('inventory.movements.stock-out') }}" class="btn btn-danger">
                        <i class="fas fa-minus me-1"></i>Remove Stock
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.movements.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="usage" {{ request('type') == 'usage' ? 'selected' : '' }}>Usage (Sales)</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="item_id" class="form-label">Item</label>
                        <select class="form-select" id="item_id" name="item_id">
                            <option value="">All Items</option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">From</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">To</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>Balance After</th>
                            <th>Notes</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->movement_date->format('M d, Y') }}</td>
                            <td><strong>{{ $movement->inventoryItem->name }}</strong></td>
                            <td>
                                @if($movement->type == 'in')
                                    <span class="badge bg-success">Stock In</span>
                                @elseif($movement->type == 'out')
                                    <span class="badge bg-danger">Stock Out</span>
                                @elseif($movement->type == 'usage')
                                    <span class="badge bg-info">Usage</span>
                                @else
                                    <span class="badge bg-warning">Adjustment</span>
                                @endif
                            </td>
                            <td>
                                @if($movement->type == 'in')
                                    <span class="text-success">+{{ number_format($movement->quantity, 2) }}</span>
                                @else
                                    <span class="text-danger">-{{ number_format($movement->quantity, 2) }}</span>
                                @endif
                                {{ $movement->inventoryItem->unit }}
                            </td>
                            <td>
                                @if($movement->total_cost)
                                    ₩{{ number_format($movement->total_cost, 0) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format($movement->balance_after, 2) }} {{ $movement->inventoryItem->unit }}</td>
                            <td>{{ Str::limit($movement->notes ?? '-', 30) }}</td>
                            <td>{{ $movement->creator->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-exchange-alt fa-3x mb-3 d-block"></i>
                                No stock movements found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-pagination :items="$movements" />
        </div>
    </div>
</div>
@endsection
