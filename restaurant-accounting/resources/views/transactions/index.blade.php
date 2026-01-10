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

    /* Mobile Responsive Header */
    @media (max-width: 576px) {
        .page-header-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .btn-add-transaction {
            width: 100%;
            justify-content: center;
            display: flex;
            align-items: center;
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
                    <td>
                        <div class="text-truncate" style="max-width: 250px;" title="{{ strip_tags($transaction->description) }}">
                            {!! Str::limit(strip_tags($transaction->description), 50) !!}
                        </div>
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
                            
                            @cannot('edit transactions')
                                <button class="btn btn-sm btn-secondary" 
                                        disabled 
                                        title="View Only">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcannot
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
    <div class="d-flex justify-content-center mt-3">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
