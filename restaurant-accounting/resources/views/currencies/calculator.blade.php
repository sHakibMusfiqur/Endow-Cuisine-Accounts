@extends('layouts.app')

@section('title', 'Currency Calculator - Restaurant Accounting')
@section('page-title', 'Currency Calculator')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-calculator"></i> Currency Calculator</h5>
                    <a href="{{ route('currencies.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Currencies
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted">Convert amounts between different currencies using current exchange rates.</p>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>From</h6>
                                    <div class="mb-3">
                                        <label for="from_currency" class="form-label">Currency</label>
                                        <select class="form-select" id="from_currency">
                                            @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" 
                                                    data-symbol="{{ $currency->symbol }}"
                                                    data-code="{{ $currency->code }}"
                                                    {{ $currency->is_default ? 'selected' : '' }}>
                                                {{ $currency->code }} ({{ $currency->symbol }}) — {{ $currency->is_base ? '1.00' : number_format($currency->exchange_rate, 2) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="from_amount" class="form-label">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="from_symbol">₩</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="from_amount" 
                                                   value="100"
                                                   step="0.01" 
                                                   min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <button class="btn btn-primary btn-lg rounded-circle" id="swap_btn" title="Swap currencies">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>

                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>To</h6>
                                    <div class="mb-3">
                                        <label for="to_currency" class="form-label">Currency</label>
                                        <select class="form-select" id="to_currency">
                                            @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" 
                                                    data-symbol="{{ $currency->symbol }}"
                                                    data-code="{{ $currency->code }}">
                                                {{ $currency->code }} ({{ $currency->symbol }}) — {{ $currency->is_base ? '1.00' : number_format($currency->exchange_rate, 2) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="to_amount" class="form-label">Converted Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="to_symbol">$</span>
                                            <input type="text" 
                                                   class="form-control fw-bold" 
                                                   id="to_amount" 
                                                   readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button class="btn btn-success btn-lg" id="convert_btn">
                            <i class="fas fa-sync"></i> Convert
                        </button>
                    </div>

                    <div class="alert alert-info mt-4" id="result_info" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> Conversion Details</h6>
                        <p class="mb-0" id="result_text"></p>
                        <p class="mb-0"><small class="text-muted" id="result_base"></small></p>
                    </div>

                    <div class="mt-4">
                        <h6>Quick Reference</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Currency</th>
                                        <th>100 units to KRW</th>
                                        <th>10,000 KRW to currency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currencies->where('is_active', true) as $currency)
                                    <tr>
                                        <td><strong>{{ $currency->code }}</strong> ({{ $currency->symbol }})</td>
                                        <td>₩{{ number_format($currency->exchange_rate * 100, 2) }}</td>
                                        <td>{{ $currency->symbol }}{{ number_format(10000 / $currency->exchange_rate, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fromCurrency = document.getElementById('from_currency');
        const toCurrency = document.getElementById('to_currency');
        const fromAmount = document.getElementById('from_amount');
        const toAmount = document.getElementById('to_amount');
        const fromSymbol = document.getElementById('from_symbol');
        const toSymbol = document.getElementById('to_symbol');
        const convertBtn = document.getElementById('convert_btn');
        const swapBtn = document.getElementById('swap_btn');
        const resultInfo = document.getElementById('result_info');
        const resultText = document.getElementById('result_text');
        const resultBase = document.getElementById('result_base');

        function updateSymbols() {
            const fromOption = fromCurrency.options[fromCurrency.selectedIndex];
            const toOption = toCurrency.options[toCurrency.selectedIndex];
            fromSymbol.textContent = fromOption.dataset.symbol;
            toSymbol.textContent = toOption.dataset.symbol;
        }

        function convert() {
            const amount = parseFloat(fromAmount.value) || 0;
            const fromId = fromCurrency.value;
            const toId = toCurrency.value;

            fetch('{{ route('currencies.convert') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    amount: amount,
                    from_currency_id: fromId,
                    to_currency_id: toId
                })
            })
            .then(response => response.json())
            .then(data => {
                toAmount.value = data.converted_amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                resultText.textContent = `${data.amount.toLocaleString()} ${data.from_currency} = ${data.converted_amount.toLocaleString()} ${data.to_currency}`;
                resultBase.textContent = `(Via KRW: ₩${data.amount_base.toLocaleString()})`;
                resultInfo.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Conversion failed. Please try again.');
            });
        }

        function swap() {
            const tempValue = fromCurrency.value;
            fromCurrency.value = toCurrency.value;
            toCurrency.value = tempValue;
            updateSymbols();
        }

        fromCurrency.addEventListener('change', updateSymbols);
        toCurrency.addEventListener('change', updateSymbols);
        convertBtn.addEventListener('click', convert);
        swapBtn.addEventListener('click', swap);
        fromAmount.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                convert();
            }
        });

        // Initialize
        updateSymbols();
        convert();
    });
</script>
@endpush
@endsection
