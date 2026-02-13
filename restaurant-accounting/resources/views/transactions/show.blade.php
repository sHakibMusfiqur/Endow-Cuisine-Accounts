@extends('layouts.app')

@section('title', 'Transaction Details - Restaurant Accounting')
@section('page-title', 'Transaction Details')

@section('content')
<style>
    /* Transaction Details Page - Project Consistent Design */
    .transaction-card {
        border-left: 4px solid #dc2626;
    }

    .detail-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .detail-value {
        color: #1f2937;
        font-size: 1rem;
    }

    .info-row {
        padding: 15px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .amount-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 8px;
    }

    .transaction-description {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 16px;
        line-height: 1.6;
        color: #374151;
    }

    .section-divider {
        margin: 25px 0;
        border-top: 2px solid #f3f4f6;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        .col-md-8,
        .col-md-4 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .card {
            margin-bottom: 15px;
        }

        .card-body {
            padding: 15px;
        }

        .info-row {
            padding: 12px 0;
        }

        .detail-label {
            font-size: 0.85rem;
        }

        .detail-value {
            font-size: 0.95rem;
        }

        .amount-value {
            font-size: 1.4rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 480px) {
        .card-body {
            padding: 12px;
        }

        .detail-label {
            font-size: 0.8rem;
        }

        .detail-value {
            font-size: 0.9rem;
        }

        .amount-value {
            font-size: 1.3rem;
        }

        .transaction-description {
            padding: 12px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="bg-danger text-white py-2 px-3 fw-semibold rounded d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-receipt me-2"></i>
                <span>Transaction Details</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Transactions
                </a>
                @can('edit transactions')
                <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Transaction Information -->
        <div class="col-md-8">
            <div class="card transaction-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-info-circle"></i> Transaction Information
                    </h5>

                    <!-- Date -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-calendar-alt text-muted"></i> Transaction Date
                        </div>
                        <div class="detail-value">
                            {{ $transaction->date->format('l, F d, Y') }}
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-exchange-alt text-muted"></i> Transaction Type
                        </div>
                        <div class="detail-value">
                            @if($transaction->category->type == 'income')
                                <span class="badge bg-success">
                                    <i class="fas fa-arrow-up"></i> Income
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-arrow-down"></i> Expense
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-tag text-muted"></i> Category
                        </div>
                        <div class="detail-value">
                            <strong>{{ $transaction->category->name }}</strong>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-credit-card text-muted"></i> Payment Method
                        </div>
                        <div class="detail-value">
                            {{ $transaction->paymentMethod->name }}
                        </div>
                    </div>

                    <!-- Created By -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-user text-muted"></i> Created By
                        </div>
                        <div class="detail-value">
                            {{ $transaction->creator->name }}
                            <br>
                            <small class="text-muted">
                                {{ $transaction->created_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    </div>

                    @if($transaction->created_at != $transaction->updated_at)
                    <!-- Last Updated -->
                    <div class="info-row">
                        <div class="detail-label">
                            <i class="fas fa-clock text-muted"></i> Last Updated
                        </div>
                        <div class="detail-value">
                            <small class="text-muted">
                                {{ $transaction->updated_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Description Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-align-left"></i> Description
                    </h5>
                    <div class="transaction-description">
                        {!! $transaction->description !!}
                    </div>
                </div>
            </div>

            @if($transaction->isDualEntry())
            <!-- Linked Transactions (Dual-Entry) -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-link"></i> Linked Transactions (Dual-Entry Accounting)
                    </h5>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Reference ID:</strong> {{ $transaction->internal_reference_id }}<br>
                        <strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $transaction->internal_reference_type)) }}<br>
                        <small class="text-muted">This transaction is part of a dual-entry accounting set.</small>
                    </div>
                    
                    @php
                        $linkedTransactions = $transaction->linkedTransactions();
                    @endphp
                    
                    @if($linkedTransactions->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Category</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($linkedTransactions as $linked)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($linked->source ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            @if($linked->income > 0)
                                                <span class="badge bg-success">Income</span>
                                            @else
                                                <span class="badge bg-danger">Expense</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($linked->income > 0)
                                                <span class="text-success fw-bold">₩{{ number_format($linked->income, 0) }}</span>
                                            @else
                                                <span class="text-danger fw-bold">₩{{ number_format($linked->expense, 0) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $linked->category->name ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('transactions.show', $linked) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Financial Information -->
        <div class="col-md-4">
            <!-- Currency -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="detail-label">
                        <i class="fas fa-money-bill-wave text-muted"></i> Currency
                    </div>
                    <div class="detail-value">
                        <span class="badge bg-secondary">{{ $transaction->currency->code }}</span>
                        {{ $transaction->currency->name }}
                    </div>
                </div>
            </div>

            <!-- Income Amount -->
            @if($transaction->income > 0)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="detail-label">
                        <i class="fas fa-plus-circle text-success"></i> Income Amount
                    </div>
                    <div class="detail-value">
                        <div class="amount-value text-success">
                            {{ formatCurrency($transaction->income, false, $activeCurrency, true) }}
                        </div>
                        @if($activeCurrency->code !== $transaction->currency->code)
                            <small class="text-muted">
                                Original: {{ $transaction->currency->symbol }}{{ number_format($transaction->income, 2) }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Expense Amount -->
            @if($transaction->expense > 0)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="detail-label">
                        <i class="fas fa-minus-circle text-danger"></i> Expense Amount
                    </div>
                    <div class="detail-value">
                        <div class="amount-value text-danger">
                            {{ formatCurrency($transaction->expense, false, $activeCurrency, true) }}
                        </div>
                        @if($activeCurrency->code !== $transaction->currency->code)
                            <small class="text-muted">
                                Original: {{ $transaction->currency->symbol }}{{ number_format($transaction->expense, 2) }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Balance Impact -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="detail-label">
                        <i class="fas fa-balance-scale text-muted"></i> Balance Impact
                    </div>
                    <div class="detail-value">
                        <div class="amount-value {{ $transaction->balance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ formatCurrency($transaction->balance, false, $activeCurrency, true) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
