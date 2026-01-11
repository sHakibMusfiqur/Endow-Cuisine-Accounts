{{-- 
    Currency Rate Display Component
    
    This is a read-only display component for showing currency information.
    It demonstrates how to display currency rates and update times to users
    WITHOUT allowing manual editing.
    
    Usage in Blade:
    @include('components.currency-display')
--}}

<div class="currency-info-panel">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-coins"></i> Currency Information
            </h5>
        </div>
        <div class="card-body">
            {{-- Active Currencies Display (Read-Only) --}}
            <div class="mb-3">
                <h6 class="text-muted">Active Currencies</h6>
                <div class="list-group">
                    @foreach(getAllActiveCurrencies() as $currency)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $currency->code }}</strong> 
                                <span class="ms-2">{{ $currency->symbol }}</span>
                                <small class="text-muted ms-2">{{ $currency->name }}</small>
                            </div>
                            <div class="text-end">
                                @if($currency->is_base)
                                    <span class="badge bg-primary">Base Currency — 1.00</span>
                                @else
                                    <small class="text-muted">
                                        Rate: <strong>{{ number_format($currency->exchange_rate, 2) }}</strong> ₩
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Last Update Information --}}
            <div class="alert alert-info mb-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div class="flex-grow-1">
                        @if($lastUpdate = getCurrencyLastUpdateTime())
                            <small>
                                <strong>Last Updated:</strong> {{ $lastUpdate }}
                            </small>
                        @else
                            <small>
                                <strong>Status:</strong> Rates have not been updated yet
                            </small>
                        @endif
                    </div>
                </div>
                
                {{-- Warning if rates are stale --}}
                @if(shouldDisplayCurrencyUpdateWarning())
                    <div class="mt-2 pt-2 border-top border-warning">
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Currency rates haven't been updated recently. The system will update them automatically.
                        </small>
                    </div>
                @endif
            </div>

            {{-- Information Note --}}
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-lock me-1"></i>
                    Currency rates are updated automatically daily and cannot be manually edited.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.currency-info-panel .list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.currency-info-panel .list-group-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}
</style>
