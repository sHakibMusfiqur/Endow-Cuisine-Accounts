@extends('layouts.app')

@section('title', 'Inventory Reports - Restaurant Accounting')
@section('page-title', 'Inventory Reports')

@section('content')
<div class="container-fluid">
    <!-- Date Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.reports.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sync me-1"></i>Update Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Items</h6>
                    <h3 class="mb-0">{{ $totalItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Low Stock</h6>
                    <h3 class="mb-0 text-{{ $lowStockItems > 0 ? 'danger' : 'success' }}">{{ $lowStockItems }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Stock Value</h6>
                    <h3 class="mb-0">₩{{ number_format($totalStockValue, 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Purchases (Period)</h6>
                    <h3 class="mb-0 text-success">₩{{ number_format($stockInValue, 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Movement Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Stock In (Purchases)</h6>
                    <h4 class="text-success mb-0">{{ $stockInCount }} movements</h4>
                    <small class="text-muted">Total: ₩{{ number_format($stockInValue, 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Stock Out (Waste/Damage)</h6>
                    <h4 class="text-danger mb-0">{{ $stockOutCount }} movements</h4>
                    <small class="text-muted">Total: ₩{{ number_format($stockOutValue, 0) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Usage (From Sales)</h6>
                    <h4 class="text-info mb-0">{{ $usageCount }} movements</h4>
                    <small class="text-muted">Total: ₩{{ number_format($usageValue, 0) }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockList->count() > 0)
    <div class="card border-danger border-2 shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Minimum Required</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockList as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td><span class="badge bg-danger">{{ number_format($item->current_stock, 2) }} {{ $item->unit }}</span></td>
                            <td>{{ number_format($item->minimum_stock, 2) }} {{ $item->unit }}</td>
                            <td>
                                @can('manage inventory')
                                <a href="{{ route('inventory.movements.stock-in') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i>Add Stock
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Export Button -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.reports.export-csv') }}">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-csv me-1"></i>Export to CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Items with Movements -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Items with Movements ({{ $startDate }} to {{ $endDate }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Movements</th>
                            <th>Total In</th>
                            <th>Total Out</th>
                            <th>Total Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemsWithMovements as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>{{ number_format($item->current_stock, 2) }} {{ $item->unit }}</td>
                            <td>{{ $item->stockMovements->count() }} movements</td>
                            <td class="text-success">
                                +{{ number_format($item->stockMovements->where('type', 'in')->sum('quantity'), 2) }} {{ $item->unit }}
                            </td>
                            <td class="text-danger">
                                -{{ number_format($item->stockMovements->where('type', 'out')->sum('quantity'), 2) }} {{ $item->unit }}
                            </td>
                            <td class="text-info">
                                -{{ number_format($item->stockMovements->where('type', 'usage')->sum('quantity'), 2) }} {{ $item->unit }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No inventory movements in the selected period.
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
