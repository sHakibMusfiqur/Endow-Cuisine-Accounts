@extends('layouts.app')

@section('title', 'Currency Management - Restaurant Accounting')
@section('page-title', 'Currency Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-coins"></i> Currency Management</h4>
        <a href="{{ route('currencies.calculator') }}" class="btn btn-info">
            <i class="fas fa-calculator"></i> Currency Calculator
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Important:</strong> Exchange rates are relative to KRW (Korean Won). 
                Changing rates will only affect new transactions, not existing ones.
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Exchange Rate (to KRW)</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currencies as $currency)
                        <tr class="{{ $currency->is_default ? 'table-primary' : '' }}">
                            <td>
                                <strong>{{ $currency->code }}</strong>
                                @if($currency->is_default)
                                <span class="badge bg-primary">Base</span>
                                @endif
                            </td>
                            <td>{{ $currency->name }}</td>
                            <td><span class="fs-5">{{ $currency->symbol }}</span></td>
                            <td>
                                <strong>{{ number_format($currency->exchange_rate, 6) }}</strong>
                                @if(!$currency->is_default)
                                <br><small class="text-muted">1 {{ $currency->code }} = {{ number_format($currency->exchange_rate, 2) }} KRW</small>
                                @endif
                            </td>
                            <td>
                                @if($currency->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($currency->is_default)
                                <i class="fas fa-star text-warning"></i> Default
                                @else
                                <form action="{{ route('currencies.setDefault', $currency) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Set {{ $currency->name }} as default currency? This will recalculate all amounts.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-star"></i> Set Default
                                    </button>
                                </form>
                                @endif
                            </td>
                            <td>{{ $currency->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('currencies.edit', $currency) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                @if(!$currency->is_default)
                                <form action="{{ route('currencies.toggleActive', $currency) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $currency->is_active ? 'warning' : 'success' }}">
                                        <i class="fas fa-{{ $currency->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $currency->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <h5>Example Conversions</h5>
                <div class="row">
                    @foreach($currencies->where('is_active', true) as $currency)
                    @if(!$currency->is_default)
                    <div class="col-md-4 mb-2">
                        <div class="card">
                            <div class="card-body">
                                <strong>{{ $currency->code }} to KRW:</strong><br>
                                {{ $currency->symbol }}100 = ₩{{ number_format($currency->exchange_rate * 100, 2) }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
