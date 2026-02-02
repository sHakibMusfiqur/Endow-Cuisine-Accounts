@extends('layouts.app')

@section('title', 'Transactions - Restaurant Accounting')
@section('page-title', 'Transactions')

@section('content')
<style>
    /* Professional Red Button Theme - Matches Restaurant Accounting Brand */
    .btn-add-transaction {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        white-space: nowrap;
    }

    .btn-add-transaction:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .btn-add-transaction:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
    }

    .btn-add-transaction i {
        margin-right: 6px;
    }

    /* Empty State Styling */
    .empty-state-container {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-container .btn-add-transaction {
        padding: 12px 30px;
        font-size: 16px;
        margin-top: 20px;
    }

    /* ============================================
       MOBILE RESPONSIVE DESIGN - COMPREHENSIVE
       ============================================ */

    /* Tablets and below - 768px */
    @media (max-width: 768px) {
        /* Header Responsive */
        .page-header-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .page-header-flex h4 {
            font-size: 1.1rem;
        }

        .btn-add-transaction {
            width: 100%;
            justify-content: center;
            display: flex;
            align-items: center;
        }

        /* Filter Card Improvements */
        .card-body form .row {
            row-gap: 10px;
        }

        .card-body form .col-md-2 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Make input group stack on very small screens */
        .input-group {
            flex-wrap: nowrap;
        }

        .input-group .btn {
            flex-shrink: 0;
        }

        /* Table Wrapper - Horizontal Scroll */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -10px;
            padding: 0 10px;
        }

        /* Table Optimizations */
        .table {
            font-size: 0.85rem;
            min-width: 900px; /* Ensures horizontal scroll */
        }

        .table thead th {
            font-size: 0.8rem;
            padding: 10px 8px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
        }

        .table tbody td {
            padding: 10px 8px;
        }

        /* Badge Sizing */
        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        /* Action Buttons - Better Touch Targets */
        .btn-group .btn-sm {
            padding: 8px 10px;
            font-size: 0.85rem;
        }
    }

    /* Small Phones - 480px and below */
    @media (max-width: 480px) {
        .page-header-flex h4 {
            font-size: 1rem;
        }

        .btn-add-transaction {
            padding: 10px 16px;
            font-size: 0.9rem;
        }

        /* Compact Filter Inputs */
        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 8px 10px;
        }

        /* Smaller table text */
        .table {
            font-size: 0.8rem;
            min-width: 800px;
        }

        .table thead th {
            font-size: 0.75rem;
            padding: 8px 6px;
        }

        .table tbody td {
            padding: 8px 6px;
        }

        /* Compact badges */
        .badge {
            font-size: 0.7rem;
            padding: 3px 6px;
        }

        /* Smaller action buttons */
        .btn-group .btn-sm {
            padding: 6px 8px;
            font-size: 0.8rem;
        }

        .btn-group .btn-sm i {
            font-size: 0.85rem;
        }

        /* Empty state adjustments */
        .empty-state-container {
            padding: 40px 15px;
        }

        .empty-state-container .fa-3x {
            font-size: 2rem !important;
        }

        .empty-state-container h5 {
            font-size: 1.1rem;
        }

        .empty-state-container p {
            font-size: 0.9rem;
        }
    }

    /* Landscape Mode - Phones */
    @media (max-height: 500px) and (orientation: landscape) {
        .page-header-flex {
            flex-direction: row !important;
            gap: 15px;
        }

        .btn-add-transaction {
            width: auto;
        }
    }

    /* Touch Device Optimizations */
    @media (hover: none) and (pointer: coarse) {
        .btn-add-transaction {
            min-height: 44px;
        }

        .form-control,
        .form-select {
            min-height: 44px;
        }

        .btn-group .btn-sm {
            min-height: 38px;
            min-width: 38px;
        }
    }
</style>

<div class="container-fluid">
    <!-- Header with Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3 page-header-flex">
        <h4 class="mb-0"><i class="fas fa-exchange-alt"></i> All Transactions</h4>
        @can('create transactions')
        <a href="{{ route('transactions.create') }}" class="btn btn-add-transaction">
            <i class="fas fa-plus"></i> Add New Transaction
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_method_id" class="form-select">
                            <option value="">All Payment Methods</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ request('payment_method_id') == $method->id ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Payment Method</th>
                    <th>Currency</th>
                    <th class="text-end">Income</th>
                    <th class="text-end">Expense</th>
                    <th class="text-end">Balance</th>
                    <th>Created By</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date->format('M d, Y') }}</td>
                    <td title="{{ strip_tags($transaction->description) }}">
                        {{ Str::limit(strip_tags($transaction->description), 50) }}
                    </td>
                    <td>
                        <span class="badge {{ $transaction->category->type == 'income' ? 'bg-success' : 'bg-warning' }}">
                            {{ $transaction->category->name }}
                        </span>
                    </td>
                    <td>{{ $transaction->paymentMethod->name }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $transaction->currency->code }}</span>
                    </td>
                    <td class="text-success fw-bold text-end">
                        @if($transaction->income > 0)
                            {{ formatCurrency($transaction->income, false, $activeCurrency, true) }}
                            @if($activeCurrency->code !== $transaction->currency->code)
                                <br><small class="text-muted">{{ $transaction->currency->symbol }}{{ number_format($transaction->income, 2) }}</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-danger fw-bold text-end">
                        @if($transaction->expense > 0)
                            {{ formatCurrency($transaction->expense, false, $activeCurrency, true) }}
                            @if($activeCurrency->code !== $transaction->currency->code)
                                <br><small class="text-muted">{{ $transaction->currency->symbol }}{{ number_format($transaction->expense, 2) }}</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="fw-bold text-end {{ $transaction->balance < 0 ? 'text-danger' : 'text-dark' }}">
                        {{ formatCurrency($transaction->balance, false, $activeCurrency, true) }}
                    </td>
                    <td>{{ $transaction->creator->name }}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('transactions.show', $transaction) }}" 
                               class="btn btn-sm btn-primary" 
                               title="View Transaction">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @can('edit transactions')
                                <a href="{{ route('transactions.edit', $transaction) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="Edit Transaction">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            
                            @can('delete transactions')
                                <form action="{{ route('transactions.destroy', $transaction) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            title="Delete Transaction">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state-container">
                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                        <h5>No transactions found</h5>
                        <p class="text-muted mb-3">
                            @if(request()->hasAny(['date_from', 'date_to', 'category_id', 'payment_method_id', 'type', 'search']))
                                Try adjusting your filters to see more results.
                            @else
                                Get started by adding your first transaction.
                            @endif
                        </p>
                        @can('create transactions')
                        <a href="{{ route('transactions.create') }}" class="btn btn-add-transaction">
                            <i class="fas fa-plus"></i> Add New Transaction
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <x-pagination :items="$transactions" />
</div>
@endsection
