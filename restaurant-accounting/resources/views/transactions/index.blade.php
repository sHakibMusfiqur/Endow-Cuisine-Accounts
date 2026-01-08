@extends('layouts.app')

@section('title', 'Transactions - Restaurant Accounting')
@section('page-title', 'Transactions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-exchange-alt"></i> All Transactions</h4>
        @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Transaction
        </a>
        @endif
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
                    <th>Income</th>
                    <th>Expense</th>
                    <th>Balance (KRW)</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date->format('M d, Y') }}</td>
                    <td>{!! Str::limit(strip_tags($transaction->description), 40) !!}</td>
                    <td>
                        <span class="badge {{ $transaction->category->type == 'income' ? 'bg-success' : 'bg-warning' }}">
                            {{ $transaction->category->name }}
                        </span>
                    </td>
                    <td>{{ $transaction->paymentMethod->name }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $transaction->currency->code }}</span>
                    </td>
                    <td class="text-success fw-bold">
                        @if($transaction->income > 0)
                            {{ formatCurrency($transaction->income) }}
                            @if($activeCurrency->code !== $transaction->currency->code)
                                <br><small class="text-muted">Original: {{ $transaction->currency->symbol }}{{ number_format($transaction->income, 2) }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-danger fw-bold">
                        @if($transaction->expense > 0)
                            {{ formatCurrency($transaction->expense) }}
                            @if($activeCurrency->code !== $transaction->currency->code)
                                <br><small class="text-muted">Original: {{ $transaction->currency->symbol }}{{ number_format($transaction->expense, 2) }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="fw-bold">{{ formatCurrency($transaction->balance) }}</td>
                    <td>{{ $transaction->creator->name }}</td>
                    <td>
                        @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                        <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if(auth()->user()->isAdmin())
                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No transactions found</td>
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
