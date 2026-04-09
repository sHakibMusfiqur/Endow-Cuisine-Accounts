@extends('layouts.app')

@section('title', 'Restaurant Dashboard - Restaurant Accounting')
@section('page-title', 'Restaurant Dashboard')

@section('content')
<style>
    /* Mobile Responsive Styles for Dashboard */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        /* Stat Cards - 2 columns on mobile */
        .col-md-3 {
            width: 50%;
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 5px;
        }

        /* Period Summary Cards */
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

        .card-title {
            font-size: 1rem;
            margin-bottom: 12px;
        }

        .card-body .d-flex {
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-body .d-flex > div {
            flex: 1;
            min-width: 80px;
            text-align: center;
        }

        .card-body small {
            font-size: 0.75rem;
        }

        .card-body h6 {
            font-size: 0.95rem;
            margin-top: 4px;
        }

        /* Charts */
        .col-md-6 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        canvas {
            max-height: 250px !important;
        }

        /* Category Expenses & Recent Transactions */
        .col-md-8 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* Table */
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

        /* List Groups */
        .list-group-item {
            font-size: 0.9rem;
            padding: 10px 12px;
        }

        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
    }

    @media (max-width: 480px) {
        /* Stat Cards - Single column on very small screens */
        .col-md-3 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0;
        }

        .card-title {
            font-size: 0.95rem;
        }

        .card-body .d-flex > div {
            min-width: 70px;
        }

        .card-body h6 {
            font-size: 0.9rem;
        }

        canvas {
            max-height: 200px !important;
        }

        .table {
            font-size: 0.8rem;
            min-width: 500px;
        }

        .table thead th,
        .table tbody td {
            padding: 8px 6px;
        }

        .list-group-item {
            font-size: 0.85rem;
            padding: 8px 10px;
        }
    }

    /* Tablet - Landscape orientation */
    @media (min-width: 481px) and (max-width: 768px) {
        .col-md-3 {
            width: 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-md-4 {
            width: 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
        .stat-card {
            min-height: 140px;
        }

        .list-group-item {
            min-height: 44px;
        }
    }

    /* Scrollable widget styling */
    .widget-scrollable {
        max-height: 400px;
        overflow-y: auto;
    }

    .widget-scrollable::-webkit-scrollbar {
        width: 8px;
    }

    .widget-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .widget-scrollable::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .widget-scrollable::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .stat-card {
        --stat-card-from: #16a34a;
        --stat-card-to: #22c55e;
        --stat-card-shadow: rgba(22, 163, 74, 0.22);
        --stat-card-icon-bg: rgba(255, 255, 255, 0.16);
        --stat-card-sparkline: rgba(255, 255, 255, 0.72);
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
        padding: 24px 28px 22px;
        border-radius: 22px;
        overflow: hidden;
        color: #ffffff;
        background: linear-gradient(135deg, var(--stat-card-from) 0%, var(--stat-card-to) 100%);
        box-shadow: 0 18px 40px var(--stat-card-shadow);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.24), transparent 38%),
            radial-gradient(circle at left center, rgba(255, 255, 255, 0.12), transparent 46%);
        pointer-events: none;
    }

    .stat-card.income {
        --stat-card-from: #0f9d58;
        --stat-card-to: #22c55e;
        --stat-card-shadow: rgba(15, 157, 88, 0.24);
    }

    .stat-card.expense {
        --stat-card-from: #dc2626;
        --stat-card-to: #f97316;
        --stat-card-shadow: rgba(220, 38, 38, 0.24);
    }

    .stat-card.balance {
        --stat-card-from: #0ea5e9;
        --stat-card-to: #2563eb;
        --stat-card-shadow: rgba(37, 99, 235, 0.24);
    }

    .stat-card.net {
        --stat-card-from: #1f2937;
        --stat-card-to: #6d28d9;
        --stat-card-shadow: rgba(109, 40, 217, 0.26);
    }

    .stat-card__header {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .stat-card__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.08em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .stat-card__value {
        margin-top: 10px;
        color: #ffffff;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: -0.04em;
        line-height: 1.05;
        word-break: break-word;
    }

    .stat-card__icon {
        align-items: center;
        background: var(--stat-card-icon-bg);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        color: #ffffff;
        display: inline-flex;
        flex: 0 0 auto;
        height: 52px;
        justify-content: center;
        width: 52px;
        z-index: 1;
    }

    .stat-card__icon i {
        font-size: 1rem;
    }

    .stat-card__trend {
        bottom: -2px;
        left: 0;
        opacity: 0.32;
        pointer-events: none;
        position: absolute;
        right: 0;
        z-index: 0;
    }

    .stat-card__trend svg {
        display: block;
        height: 52px;
        width: 100%;
    }

    .stat-card__trend path {
        fill: none;
        stroke: var(--stat-card-sparkline);
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 3;
    }

    .summary-metrics {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .summary-metric {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .summary-label {
        color: #6c757d;
        font-size: 0.875rem;
        flex: 1 1 auto;
        min-width: 0;
    }

    .summary-value {
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .summary-value.income {
        color: #198754;
    }

    .summary-value.expense {
        color: #dc3545;
    }

    .summary-value.net {
        color: #0d6efd;
    }

    .dashboard-hover-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .dashboard-hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }

    .payment-method-overview {
        margin-top: 0.75rem;
    }

    .dashboard-section {
        margin-top: 1.5rem;
    }

    .dashboard-section-card,
    .payment-method-overview-card {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(17, 24, 39, 0.08);
        padding: 20px;
    }

    .dashboard-section__header,
    .payment-method-overview__header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0;
    }

    .dashboard-section__divider,
    .payment-method-overview__divider {
        border-top: 1px solid #E5E7EB;
        margin: 16px 0;
    }

    .payment-method-grid {
        margin-top: 0;
    }

    .payment-method-card {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, 0.08);
        padding: 20px;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .payment-method-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }

    .dashboard-metric-card {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(17, 24, 39, 0.08);
        padding: 20px;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }

    .payment-method-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .payment-method-card__label {
        color: #9aa0a6;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .payment-method-card__amount {
        color: #1f2937;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.05;
        word-break: break-word;
    }

    .payment-method-card__subtitle {
        color: #9aa0a6;
        font-size: 0.82rem;
        font-weight: 600;
        margin-top: 12px;
    }

    .payment-method-card__icon {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e9edf3;
        border-radius: 12px;
        color: #0d6efd;
        display: inline-flex;
        flex: 0 0 auto;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .payment-method-card__icon i {
        font-size: 0.95rem;
    }

    .payment-method-filter {
        background: transparent;
        border: 0;
        box-shadow: none;
        margin: 0;
        padding: 0;
    }

    .payment-method-filter__label {
        color: #6c757d;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 0.45rem;
    }

    .payment-method-filter__input,
    .payment-method-filter__select {
        border-color: #dfe5ee;
        border-radius: 12px;
        min-height: 44px;
    }

    .payment-method-filter__input:focus,
    .payment-method-filter__select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.12);
    }

    .payment-method-filter__actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .payment-method-filter__btn {
        align-items: center;
        border-radius: 12px;
        display: inline-flex;
        font-weight: 600;
        gap: 0.45rem;
        justify-content: center;
        min-height: 44px;
        min-width: 112px;
        padding: 0.65rem 1.1rem;
    }

    .payment-method-filter__btn i {
        font-size: 0.9rem;
    }

    .payment-method-filter__btn--search {
        background: #E11D2E;
        border-color: #E11D2E;
        color: #ffffff;
    }

    .payment-method-filter__btn--search:hover,
    .payment-method-filter__btn--search:focus {
        background: #c81e2f;
        border-color: #c81e2f;
        color: #ffffff;
    }

    .payment-method-filter__btn--reset {
        background: transparent;
        border: 1px solid #111111;
        color: #111111;
    }

    .payment-method-filter__btn--reset:hover,
    .payment-method-filter__btn--reset:focus {
        background: #111111;
        border-color: #111111;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .stat-card {
            min-height: 130px;
            padding: 22px 22px 20px;
        }

        .stat-card__eyebrow {
            font-size: 12px;
        }

        .stat-card__value {
            font-size: 28px;
        }

        .stat-card__icon {
            height: 46px;
            width: 46px;
        }

        .stat-card__trend svg {
            height: 46px;
        }

        .summary-metrics {
            gap: 0.6rem;
        }

        .summary-label,
        .summary-value {
            font-size: 0.82rem;
        }

        .dashboard-section-card,
        .payment-method-overview-card {
            padding: 16px;
        }

        .payment-method-filter__actions {
            flex-direction: column;
            justify-content: stretch;
        }

        .payment-method-filter__btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .stat-card {
            min-height: 124px;
            padding: 20px 18px 18px;
        }

        .stat-card__eyebrow {
            font-size: 11px;
        }

        .stat-card__value {
            font-size: 26px;
        }

        .stat-card__icon {
            height: 44px;
            width: 44px;
        }

        .stat-card__trend svg {
            height: 42px;
        }

        .summary-label,
        .summary-value {
            font-size: 0.78rem;
        }

        .dashboard-section-card,
        .payment-method-overview-card {
            padding: 14px;
        }
    }
</style>

<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card income">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__eyebrow">Today's Income</div>
                        <div class="stat-card__value">{{ formatCurrency($todayIncome) }}</div>
                    </div>
                    <div class="stat-card__icon" aria-hidden="true">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="stat-card__trend" aria-hidden="true">
                    <svg viewBox="0 0 320 60" preserveAspectRatio="none" focusable="false">
                        <path d="M0 38 C 24 28, 44 14, 70 20 S 118 50, 148 40 S 200 10, 232 22 S 280 45, 320 18" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card expense">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__eyebrow">Today's Expense</div>
                        <div class="stat-card__value">{{ formatCurrency($todayExpense) }}</div>
                    </div>
                    <div class="stat-card__icon" aria-hidden="true">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="stat-card__trend" aria-hidden="true">
                    <svg viewBox="0 0 320 60" preserveAspectRatio="none" focusable="false">
                        <path d="M0 40 C 22 18, 48 18, 74 30 S 126 52, 156 36 S 208 8, 240 24 S 286 42, 320 20" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card balance">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__eyebrow">Current Balance</div>
                        <div class="stat-card__value">{{ formatCurrency($currentBalance) }}</div>
                    </div>
                    <div class="stat-card__icon" aria-hidden="true">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="stat-card__trend" aria-hidden="true">
                    <svg viewBox="0 0 320 60" preserveAspectRatio="none" focusable="false">
                        <path d="M0 42 C 30 22, 48 8, 82 18 S 138 52, 168 36 S 220 4, 250 20 S 290 48, 320 24" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card net">
                <div class="stat-card__header">
                    <div>
                        <div class="stat-card__eyebrow">Today's Net</div>
                        <div class="stat-card__value">{{ formatCurrency($todayNet) }}</div>
                    </div>
                    <div class="stat-card__icon" aria-hidden="true">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-card__trend" aria-hidden="true">
                    <svg viewBox="0 0 320 60" preserveAspectRatio="none" focusable="false">
                        <path d="M0 42 C 30 22, 48 8, 82 18 S 138 52, 168 36 S 220 4, 250 20 S 290 48, 320 24" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="payment-method-overview">
        <div class="payment-method-overview-card dashboard-section-card">
            <div class="payment-method-overview__header">
                <h5 class="card-title mb-0"><i class="fas fa-wallet text-primary me-2"></i>Payment Method Overview</h5>
            </div>

            <div class="payment-method-overview__divider"></div>

            <div class="payment-method-filter">
                <form method="GET" action="{{ route('restaurant.dashboard') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label for="from_date" class="payment-method-filter__label">From Date</label>
                            <input
                                type="date"
                                class="form-control payment-method-filter__input"
                                id="from_date"
                                name="from_date"
                                value="{{ $fromDate ?? '' }}"
                            >
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="to_date" class="payment-method-filter__label">To Date</label>
                            <input
                                type="date"
                                class="form-control payment-method-filter__input"
                                id="to_date"
                                name="to_date"
                                value="{{ $toDate ?? '' }}"
                            >
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="transaction_type" class="payment-method-filter__label">Type</label>
                            <select
                                class="form-select payment-method-filter__select"
                                id="transaction_type"
                                name="transaction_type"
                            >
                                <option value="all" {{ ($transactionType ?? 'all') === 'all' ? 'selected' : '' }}>All Transactions</option>
                                <option value="income" {{ ($transactionType ?? 'all') === 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ ($transactionType ?? 'all') === 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="payment-method-filter__actions">
                                <button type="submit" class="btn payment-method-filter__btn payment-method-filter__btn--search">
                                    <i class="fas fa-search"></i>
                                    <span>Search</span>
                                </button>
                                <a href="{{ route('restaurant.dashboard') }}" class="btn payment-method-filter__btn payment-method-filter__btn--reset">
                                    <i class="fas fa-rotate-right"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="payment-method-overview__divider"></div>

            <div class="row g-3 payment-method-grid">
            @forelse($paymentMethodTotals as $method)
            <div class="col-12 col-md-6 col-lg-3">
                @php
                    $methodName = strtolower(trim($method->name ?? ''));
                    $paymentIcon = 'fa-wallet';

                    if (stripos($methodName, 'cash') !== false) {
                        $paymentIcon = 'fa-money-bill-wave';
                    } elseif (stripos($methodName, 'bank') !== false || stripos($methodName, 'transfer') !== false) {
                        $paymentIcon = 'fa-building-columns';
                    } elseif (stripos($methodName, 'credit') !== false || stripos($methodName, 'card') !== false) {
                        $paymentIcon = 'fa-credit-card';
                    } elseif (stripos($methodName, 'mobile') !== false || stripos($methodName, 'phone') !== false) {
                        $paymentIcon = 'fa-mobile-screen-button';
                    }
                @endphp
                <div class="payment-method-card">
                    <div class="payment-method-card__header">
                        <div>
                            <div class="payment-method-card__label">{{ strtoupper(trim($method->name ?? '')) }}</div>
                            <div class="payment-method-card__amount">{{ formatCurrency($method->total_amount) }}</div>
                        </div>
                        <div class="payment-method-card__icon" aria-hidden="true">
                            <i class="fas {{ $paymentIcon }}"></i>
                        </div>
                    </div>
                    <div class="payment-method-card__subtitle">Total Transactions</div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-muted mb-0">No payment methods found.</p>
            </div>
            @endforelse
            </div>
        </div>
    </div>

    <!-- Period Summaries -->
    <div class="dashboard-section">
        <div class="dashboard-section-card">
            <div class="dashboard-section__header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Summary</h5>
            </div>
            <div class="dashboard-section__divider"></div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">Today</small>
                                <h6 class="mb-0">Summary</h6>
                            </div>
                            <div class="summary-metrics">
                                <div class="summary-metric">
                                    <span class="summary-label">Income</span>
                                    <span class="summary-value income">{{ formatCurrency($todaySummary['total_income']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Expense</span>
                                    <span class="summary-value expense">{{ formatCurrency($todaySummary['total_expense']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Net</span>
                                    <span class="summary-value net">{{ formatCurrency($todaySummary['net_amount']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Week</small>
                                <h6 class="mb-0">Summary</h6>
                            </div>
                            <div class="summary-metrics">
                                <div class="summary-metric">
                                    <span class="summary-label">Income</span>
                                    <span class="summary-value income">{{ formatCurrency($weekSummary['total_income']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Expense</span>
                                    <span class="summary-value expense">{{ formatCurrency($weekSummary['total_expense']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Net</span>
                                    <span class="summary-value net">{{ formatCurrency($weekSummary['net_amount']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Month</small>
                                <h6 class="mb-0">Summary</h6>
                            </div>
                            <div class="summary-metrics">
                                <div class="summary-metric">
                                    <span class="summary-label">Income</span>
                                    <span class="summary-value income">{{ formatCurrency($monthSummary['total_income']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Expense</span>
                                    <span class="summary-value expense">{{ formatCurrency($monthSummary['total_expense']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Net</span>
                                    <span class="summary-value net">{{ formatCurrency($monthSummary['net_amount']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Year</small>
                                <h6 class="mb-0">Summary</h6>
                            </div>
                            <div class="summary-metrics">
                                <div class="summary-metric">
                                    <span class="summary-label">Income</span>
                                    <span class="summary-value income">{{ formatCurrency($yearSummary['total_income']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Expense</span>
                                    <span class="summary-value expense">{{ formatCurrency($yearSummary['total_expense']) }}</span>
                                </div>
                                <div class="summary-metric">
                                    <span class="summary-label">Net</span>
                                    <span class="summary-value net">{{ formatCurrency($yearSummary['net_amount']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Summary -->
    <div class="dashboard-section">
        <div class="dashboard-section-card">
            <div class="dashboard-section__header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Profit Breakdown</h5>
            </div>
            <div class="dashboard-section__divider"></div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">Today</small>
                                <h6 class="mb-0">Profit Breakdown</h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Restaurant</small>
                                <span class="fw-semibold {{ $todayProfit['normal_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($todayProfit['normal_profit']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">Inventory</small>
                                <span class="fw-semibold {{ $todayProfit['inventory_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($todayProfit['inventory_profit']) }}
                                </span>
                            </div>
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Profit</span>
                                    <h5 class="mb-0 fw-semibold {{ $todayProfit['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ formatCurrency($todayProfit['total_profit']) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Week</small>
                                <h6 class="mb-0">Profit Breakdown</h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Restaurant</small>
                                <span class="fw-semibold {{ $weekProfit['normal_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($weekProfit['normal_profit']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">Inventory</small>
                                <span class="fw-semibold {{ $weekProfit['inventory_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($weekProfit['inventory_profit']) }}
                                </span>
                            </div>
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Profit</span>
                                    <h5 class="mb-0 fw-semibold {{ $weekProfit['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ formatCurrency($weekProfit['total_profit']) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Month</small>
                                <h6 class="mb-0">Profit Breakdown</h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Restaurant</small>
                                <span class="fw-semibold {{ $monthProfit['normal_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($monthProfit['normal_profit']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">Inventory</small>
                                <span class="fw-semibold {{ $monthProfit['inventory_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($monthProfit['inventory_profit']) }}
                                </span>
                            </div>
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Profit</span>
                                    <h5 class="mb-0 fw-semibold {{ $monthProfit['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ formatCurrency($monthProfit['total_profit']) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-metric-card">
                        <div class="card-body p-0">
                            <div class="mb-3">
                                <small class="text-muted text-uppercase d-block mb-1">This Year</small>
                                <h6 class="mb-0">Profit Breakdown</h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Restaurant</small>
                                <span class="fw-semibold {{ $yearProfit['normal_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($yearProfit['normal_profit']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">Inventory</small>
                                <span class="fw-semibold {{ $yearProfit['inventory_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatCurrency($yearProfit['inventory_profit']) }}
                                </span>
                            </div>
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Profit</span>
                                    <h5 class="mb-0 fw-semibold {{ $yearProfit['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ formatCurrency($yearProfit['total_profit']) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line text-primary"></i> Weekly Trend (Last 7 Days)</h5>
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-bar text-primary"></i> Monthly Trend</h5>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Income & Expenses -->
    <div class="row mt-4">
        <div class="col-md-4">
            <!-- Top Income Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-arrow-trend-up text-success"></i> Top Income (This Month)</h5>
                    <div class="list-group list-group-flush widget-scrollable">
                        @forelse($categoryIncomes as $entry)
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="text-muted small">{{ $entry['category'] }}</div>
                                <div class="fw-bold">{{ $entry['item'] }}</div>
                            </div>
                            <span class="badge bg-success rounded-pill">{{ formatCurrency($entry['amount']) }}</span>
                        </div>
                        @empty
                        <p class="text-muted">No income recorded this month</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top Expenses Card -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-arrow-trend-down text-danger"></i> Top Expenses (This Month)</h5>
                    <div class="list-group list-group-flush widget-scrollable">
                        @forelse($categoryExpenses as $entry)
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="text-muted small">{{ $entry['category'] }}</div>
                                <div class="fw-bold">{{ $entry['item'] }}</div>
                            </div>
                            <span class="badge bg-danger rounded-pill">{{ formatCurrency($entry['amount']) }}</span>
                        </div>
                        @empty
                        <p class="text-muted">No expenses recorded this month</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-history text-primary"></i> Recent Transactions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Income</th>
                                    <th>Expense</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                @php $isDamageEntry = $transaction->is_damage_entry ?? false; @endphp
                                <tr>
                                    <td>{{ $transaction->date->format('M d, Y') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($transaction->description), 50) }}</td>
                                    <td>
                                        @if($isDamageEntry)
                                            <span class="badge bg-warning text-dark">Inventory Damage</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $transaction->category->name }}</span>
                                        @endif
                                    </td>
                                    <td class="text-success">{{ $isDamageEntry ? '-' : ($transaction->income > 0 ? formatCurrency($transaction->income) : '-') }}</td>
                                    <td class="text-danger">{{ $isDamageEntry ? '-₩' . number_format((float) $transaction->expense, 0) : ($transaction->expense > 0 ? formatCurrency($transaction->expense) : '-') }}</td>
                                    <td>{{ $isDamageEntry ? '-' : formatCurrency($transaction->balance) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Weekly Chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: @json($weeklyChartData['labels']),
            datasets: [
                {
                    label: 'Income',
                    data: @json($weeklyChartData['income']),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Expense',
                    data: @json($weeklyChartData['expense']),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyChartData['labels']),
            datasets: [
                {
                    label: 'Income',
                    data: @json($monthlyChartData['income']),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)'
                },
                {
                    label: 'Expense',
                    data: @json($monthlyChartData['expense']),
                    backgroundColor: 'rgba(255, 99, 132, 0.8)'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
@endsection
