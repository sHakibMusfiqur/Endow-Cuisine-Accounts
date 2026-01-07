@extends('layouts.app')

@section('title', 'Payment Methods - Restaurant Accounting')
@section('page-title', 'Payment Methods')

@section('content')
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
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Transactions Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentMethods as $method)
                <tr>
                    <td>{{ $method->id }}</td>
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
    <div class="d-flex justify-content-center mt-3">
        {{ $paymentMethods->links() }}
    </div>
</div>
@endsection
