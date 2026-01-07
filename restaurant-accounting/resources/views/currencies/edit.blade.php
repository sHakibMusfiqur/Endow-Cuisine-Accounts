@extends('layouts.app')

@section('title', 'Edit Currency - Restaurant Accounting')
@section('page-title', 'Edit Currency')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-edit"></i> Edit Currency: {{ $currency->name }}</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('currencies.update', $currency) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Currency Code</label>
                            <input type="text" class="form-control" value="{{ $currency->code }}" disabled>
                            <small class="text-muted">Currency code cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency Name</label>
                            <input type="text" class="form-control" value="{{ $currency->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Symbol</label>
                            <input type="text" class="form-control" value="{{ $currency->symbol }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="exchange_rate" class="form-label">
                                Exchange Rate to KRW <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('exchange_rate') is-invalid @enderror" 
                                   id="exchange_rate" 
                                   name="exchange_rate" 
                                   value="{{ old('exchange_rate', $currency->exchange_rate) }}" 
                                   step="0.000001" 
                                   min="0.000001"
                                   {{ $currency->is_default ? 'readonly' : 'required' }}>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                1 {{ $currency->code }} = <span id="rate_display">{{ number_format($currency->exchange_rate, 2) }}</span> KRW
                            </small>
                            @if($currency->is_default)
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-lock"></i> Exchange rate for default currency is locked at 1.0
                            </div>
                            @endif
                            @error('exchange_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active"
                                       {{ old('is_active', $currency->is_active) ? 'checked' : '' }}
                                       {{ $currency->is_default ? 'disabled' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive currencies cannot be used for new transactions</small>
                        </div>

                        @if(!$currency->is_default)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> Changing the exchange rate will only affect new transactions. 
                            Existing transactions will retain their original conversion rates.
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary" {{ $currency->is_default ? 'disabled' : '' }}>
                                <i class="fas fa-save"></i> Update Currency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rateInput = document.getElementById('exchange_rate');
        const rateDisplay = document.getElementById('rate_display');

        if (rateInput && rateDisplay) {
            rateInput.addEventListener('input', function() {
                const rate = parseFloat(this.value) || 0;
                rateDisplay.textContent = rate.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });
        }
    });
</script>
@endpush
@endsection
