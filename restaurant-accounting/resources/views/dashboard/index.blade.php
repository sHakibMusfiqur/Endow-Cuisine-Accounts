@extends('layouts.app')

@section('title', 'Dashboard - Restaurant Accounting')
@section('page-title', 'Dashboard')

@section('content')
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

    <!-- Category Expenses & Recent Transactions -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-tags text-primary"></i> Top Expenses (This Month)</h5>
                    <div class="list-group list-group-flush">
                        @forelse($categoryExpenses as $category => $amount)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $category }}
                            <span class="badge bg-danger">{{ formatCurrency($amount) }}</span>
                        </div>
                        @empty
                        <p class="text-muted">No expenses recorded</p>
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
                                    <td>{{ Str::limit($transaction->description, 30) }}</td>
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
