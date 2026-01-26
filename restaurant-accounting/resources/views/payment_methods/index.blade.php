@extends('layouts.app')

@section('title', 'Payment Methods - Restaurant Accounting')
@section('page-title', 'Payment Methods')

@section('content')
<style>
    /* Mobile Responsive Styles for Index Pages */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .d-flex.justify-content-between h4 {
            font-size: 1.1rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
            justify-content: center;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            font-size: 0.85rem;
            min-width: 600px;
        }

        .table thead th {
            font-size: 0.8rem;
            padding: 10px 8px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    }

    @media (max-width: 480px) {
        .d-flex.justify-content-between h4 {
            font-size: 1rem;
        }

        .table {
            font-size: 0.8rem;
            min-width: 500px;
        }

        .table thead th,
        .table tbody td {
            padding: 8px 6px;
        }

        .btn-sm {
            padding: 5px 8px;
            font-size: 0.75rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 3px 6px;
        }
    }

    @media (hover: none) and (pointer: coarse) {
        .btn {
            min-height: 44px;
        }

        .btn-sm {
            min-height: 38px;
        }
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-credit-card"></i> All Payment Methods</h4>
        <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Payment Method
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Transactions Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentMethods as $method)
                <tr>
                    <td>{{ $loop->iteration + (($paymentMethods->currentPage() - 1) * $paymentMethods->perPage()) }}</td>
                    <td>{{ $method->name }}</td>
                    <td>
                        <span class="badge {{ $method->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($method->status) }}
                        </span>
                    </td>
                    <td>{{ $method->transactions->count() }}</td>
                    <td>
                        <a href="{{ route('payment-methods.edit', $method) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('payment-methods.destroy', $method) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this payment method?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No payment methods found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <x-pagination :items="$paymentMethods" />
</div>
@endsection
