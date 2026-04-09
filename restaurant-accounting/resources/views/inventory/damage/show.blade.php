@extends('layouts.app')

@section('title', 'Inventory Damage Details - Restaurant Accounting')
@section('page-title', 'Inventory Damage Details')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Transactions
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="mb-1"><i class="fas fa-triangle-exclamation text-warning me-2"></i>Inventory Damage Details</h5>
                    <div class="text-muted">{{ $item->name }}</div>
                </div>
                <span class="badge bg-warning text-dark">Damage / Spoilage</span>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="45%">Item Name</th>
                                <td class="fw-semibold">{{ $item->name }}</td>
                            </tr>
                            <tr>
                                <th>Quantity Damaged</th>
                                <td>{{ rtrim(rtrim(number_format($quantityDamaged, 2), '0'), '.') }} {{ $item->unit ?? 'unit' }}</td>
                            </tr>
                            <tr>
                                <th>Unit Cost</th>
                                <td>₩{{ number_format($unitCost, 0) }}</td>
                            </tr>
                            <tr>
                                <th>Total Damage Value</th>
                                <td class="fw-bold text-danger">₩{{ number_format($totalDamageValue, 0) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="45%">Damage Date</th>
                                <td>{{ $damage->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>{{ $damage->adjustedBy?->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th>Adjustment Reason / Notes</th>
                                <td>
                                    @if($damage->reason || $damage->notes)
                                        <div class="fw-semibold">{{ $damage->reason ?? '-' }}</div>
                                        @if($damage->notes)
                                            <div class="text-muted mt-2" style="white-space: pre-line;">{{ $damage->notes }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection