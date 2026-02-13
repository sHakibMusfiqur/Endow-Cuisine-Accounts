@extends('layouts.app')

@section('title', 'Dashboard - Restaurant Accounting')
@section('page-title', 'Dashboard')

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

        .stat-card {
            padding: 12px;
            margin-bottom: 10px;
        }

        .stat-card h6 {
            font-size: 0.8rem;
            margin-bottom: 8px;
        }

        .stat-card h2 {
            font-size: 1.3rem;
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

        .stat-card {
            padding: 10px;
            margin-bottom: 8px;
        }

        .stat-card h6 {
            font-size: 0.75rem;
        }

        .stat-card h2 {
            font-size: 1.2rem;
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
            min-height: 100px;
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
</style>

<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card income">
                <h6><i class="fas fa-arrow-up"></i> Today's Income</h6>
                <h2>{{ formatCurrency($todaySummary['total_income']) }}</h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card expense">
                <h6><i class="fas fa-arrow-down"></i> Today's Expense</h6>
                <h2>{{ formatCurrency($todaySummary['total_expense']) }}</h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card balance">
                <h6><i class="fas fa-wallet"></i> Current Balance</h6>
                <h2>{{ formatCurrency($todaySummary['current_balance']) }}</h2>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card net">
                <h6><i class="fas fa-chart-pie"></i> Today's Net</h6>
                <h2>{{ formatCurrency($todaySummary['net_amount']) }}</h2>
            </div>
        </div>
    </div>

    <!-- Period Summaries -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-week text-primary"></i> This Week</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Income</small>
                            <h6 class="text-success">{{ formatCurrency($weekSummary['total_income']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Expense</small>
                            <h6 class="text-danger">{{ formatCurrency($weekSummary['total_expense']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Net</small>
                            <h6 class="text-info">{{ formatCurrency($weekSummary['net_amount']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt text-primary"></i> This Month</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Income</small>
                            <h6 class="text-success">{{ formatCurrency($monthSummary['total_income']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Expense</small>
                            <h6 class="text-danger">{{ formatCurrency($monthSummary['total_expense']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Net</small>
                            <h6 class="text-info">{{ formatCurrency($monthSummary['net_amount']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar text-primary"></i> This Year</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Income</small>
                            <h6 class="text-success">{{ formatCurrency($yearSummary['total_income']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Expense</small>
                            <h6 class="text-danger">{{ formatCurrency($yearSummary['total_expense']) }}</h6>
                        </div>
                        <div>
                            <small class="text-muted">Net</small>
                            <h6 class="text-info">{{ formatCurrency($yearSummary['net_amount']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Summary -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
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
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
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
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
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
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
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
                                <tr>
                                    <td>{{ $transaction->date->format('M d, Y') }}</td>
                                    <td>{{ Str::limit(strip_tags($transaction->description), 50) }}</td>
                                    <td><span class="badge bg-secondary">{{ $transaction->category->name }}</span></td>
                                    <td class="text-success">{{ $transaction->income > 0 ? formatCurrency($transaction->income) : '-' }}</td>
                                    <td class="text-danger">{{ $transaction->expense > 0 ? formatCurrency($transaction->expense) : '-' }}</td>
                                    <td>{{ formatCurrency($transaction->balance) }}</td>
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
