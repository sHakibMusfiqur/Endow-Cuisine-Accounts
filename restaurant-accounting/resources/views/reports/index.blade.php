@extends('layouts.app')

@section('title', 'Reports - Restaurant Accounting')
@section('page-title', 'Reports & Analytics')

@section('content')
<style>
    /* Professional Reports Page Styling */
    .reports-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-radius: 12px;
        padding: 40px;
        margin-bottom: 40px;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .page-header p {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .report-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        border-color: #d1d5db;
    }

    .report-card-header {
        padding: 24px;
        border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .report-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 24px;
    }

    .icon-csv {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .icon-pdf {
        background: #e3f2fd;
        color: #1565c0;
    }

    .icon-summary {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    .report-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 8px;
    }

    .report-card p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
        line-height: 1.5;
    }

    .report-card-body {
        padding: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-export {
        width: 100%;
        padding: 14px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-export i {
        font-size: 1.1rem;
    }

    .btn-csv {
        background: #2e7d32;
        color: white;
    }

    .btn-csv:hover {
        background: #1b5e20;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }

    .btn-pdf {
        background: #1565c0;
        color: white;
    }

    .btn-pdf:hover {
        background: #0d47a1;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
    }

    .btn-summary {
        background: #6a1b9a;
        color: white;
    }

    .btn-summary:hover {
        background: #4a148c;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(106, 27, 154, 0.3);
    }

    .quick-reports-section {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .quick-reports-section h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .quick-reports-section h2 i {
        color: #3b82f6;
    }

    .btn-quick {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-quick:hover {
        background: #f9fafb;
        border-color: #3b82f6;
        color: #1e40af;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn-quick i {
        font-size: 1.1rem;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 991px) {
        .page-header {
            padding: 24px;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .report-card-header {
            padding: 20px;
        }

        .report-card-body {
            padding: 20px;
        }

        .form-control,
        .form-select {
            font-size: 16px; /* Prevents iOS zoom on mobile */
            padding: 10px 14px;
        }

        .btn-export {
            padding: 12px 18px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        .card {
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .card-body {
            padding: 15px;
        }

        .card-title {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        /* Form Elements */
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .mb-3 {
            margin-bottom: 12px !important;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            font-size: 0.9rem;
        }

        .btn i {
            margin-right: 6px;
        }

        /* Quick Reports Section */
        .card-header h5 {
            font-size: 1rem;
        }

        .row.g-3 > div {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .btn-outline-primary {
            padding: 12px 16px;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
    }

    @media (max-width: 480px) {
        .container-fluid {
            padding: 0 5px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 1.25rem;
        }

        .report-card-header {
            padding: 16px;
        }

        .report-card-body {
            padding: 16px;
        }

        .card-body {
            padding: 12px;
        }

        .card-title {
            font-size: 0.95rem;
        }

        .card-text {
            font-size: 0.85rem;
        }

        .form-label,
        .form-group label {
            font-size: 0.85rem;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            padding: 8px 10px;
        }

        .btn {
            padding: 10px 14px;
            font-size: 0.85rem;
        }

        .btn-export {
            padding: 10px 14px;
            font-size: 0.85rem;
        }

        .btn-outline-primary {
            padding: 10px 14px;
            font-size: 0.85rem;
        }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
        .form-control,
        .form-select {
            min-height: 44px;
        }

        .btn {
            min-height: 44px;
        }
    }
</style>

<div class="reports-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Reports & Analytics</h1>
        <p>Generate comprehensive financial reports and export data for analysis</p>
    </div>

    <!-- Filters and Summary Section -->
    <div class="mb-4">
        <div class="bg-danger text-white py-2 px-3 fw-semibold rounded d-flex align-items-center mb-3">
            <i class="fas fa-filter me-2"></i>
            <span>Report Filters & Summary</span>
        </div>
        <div class="bg-white rounded p-3">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('reports.index') }}" class="mb-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label fw-semibold small mb-1">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ $date_from }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label fw-semibold small mb-1">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ $date_to }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="transaction_source" class="form-label fw-semibold small mb-1">Transaction Source</label>
                        <select class="form-select" id="transaction_source" name="transaction_source">
                            <option value="all" {{ $transaction_source === 'all' ? 'selected' : '' }}>All Transactions</option>
                            <option value="normal" {{ $transaction_source === 'normal' ? 'selected' : '' }}>Normal Transactions</option>
                            <option value="inventory" {{ $transaction_source === 'inventory' ? 'selected' : '' }}>Inventory Transactions</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sync me-1"></i>Update
                        </button>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="bg-light rounded p-3 text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Total Records</div>
                        <div class="fs-5 fw-semibold text-primary">{{ number_format($total_records) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded p-3 text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Total Income</div>
                        <div class="fs-5 fw-semibold text-success">₩{{ number_format($total_income, 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded p-3 text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Total Expense</div>
                        <div class="fs-5 fw-semibold text-danger">₩{{ number_format($total_expense, 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded p-3 text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Net Amount</div>
                        <div class="fs-5 fw-semibold {{ $net_amount >= 0 ? 'text-success' : 'text-danger' }}">
                            ₩{{ number_format($net_amount, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            
            @if($transaction_source === 'inventory' && $total_damage_loss > 0)
            <!-- Inventory Damage Loss Summary -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="bg-warning bg-opacity-10 rounded p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-warning mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Total Inventory Damage / Loss</h6>
                                <p class="text-muted small mb-0">Includes all spoilage and damage entries from stock movements</p>
                            </div>
                            <h3 class="mb-0 text-warning">₩{{ number_format($total_damage_loss, 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Transactions Table -->
    @if($total_records > 0)
    <div class="mb-4">
        <div class="bg-danger text-white py-2 px-3 fw-semibold rounded d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-table me-2"></i>
                <span>Transaction Details</span>
            </div>
            <span class="badge bg-white text-dark fw-semibold">({{ number_format($total_records) }} records)</span>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2 px-3 fw-semibold small">Date</th>
                            <th class="py-2 px-3 fw-semibold small">Description</th>
                            <th class="py-2 px-3 fw-semibold small">Category</th>
                            <th class="py-2 px-3 fw-semibold small">Payment Method</th>
                            <th class="py-2 px-3 text-end fw-semibold small">Income</th>
                            <th class="py-2 px-3 text-end fw-semibold small">Expense</th>
                            <th class="py-2 px-3 text-end fw-semibold small">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td class="py-2 px-3 align-middle small">{{ $transaction->date->format('Y-m-d') }}</td>
                            <td class="py-2 px-3 align-middle small" style="max-width: 300px; white-space: normal; word-wrap: break-word;" title="{{ strip_tags($transaction->description) }}">{{ Str::limit(strip_tags($transaction->description), 50) }}</td>
                            <td class="py-2 px-3 align-middle">
                                <span class="badge bg-{{ $transaction->category->type === 'income' ? 'success' : 'danger' }} badge-sm">
                                    {{ $transaction->category->name }}
                                </span>
                            </td>
                            <td class="py-2 px-3 align-middle small">{{ $transaction->paymentMethod->name }}</td>
                            <td class="py-2 px-3 text-end align-middle text-success fw-semibold small">
                                {{ $transaction->income > 0 ? '₩' . number_format($transaction->income, 0) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-end align-middle text-danger fw-semibold small">
                                {{ $transaction->expense > 0 ? '₩' . number_format($transaction->expense, 0) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-end align-middle fw-bold small">
                                ₩{{ number_format($transaction->balance, 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No transactions found for the selected date range and filters.
    </div>
    @endif

    <!-- Report Export Cards -->
    <div class="row mb-4 row-cols-1 row-cols-lg-3 g-4">
        <!-- CSV Export -->
        <div class="col">
            <div class="report-card h-100 d-flex flex-column">
                <div class="report-card-header">
                    <div class="report-card-icon icon-csv">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    <h3>CSV Export</h3>
                    <p>Download transaction data for Excel and spreadsheet applications</p>
                </div>
                <div class="report-card-body flex-grow-1 d-flex flex-column">
                    <form action="{{ route('reports.export-csv') }}" method="POST" class="d-flex flex-column h-100">
                        @csrf
                        <div class="flex-grow-1">
                            <div class="form-group">
                                <label for="csv_transaction_source">Transaction Source</label>
                                <select id="csv_transaction_source" name="transaction_source" class="form-select" required>
                                    <option value="all" {{ $transaction_source === 'all' ? 'selected' : '' }}>All Transactions</option>
                                    <option value="normal" {{ $transaction_source === 'normal' ? 'selected' : '' }}>Normal Transactions</option>
                                    <option value="inventory" {{ $transaction_source === 'inventory' ? 'selected' : '' }}>Inventory Transactions (Sales & Purchases)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="csv_date_from">Date From</label>
                                <input type="date" id="csv_date_from" name="date_from" class="form-control" value="{{ $date_from }}" required>
                            </div>
                            <div class="form-group">
                                <label for="csv_date_to">Date To</label>
                                <input type="date" id="csv_date_to" name="date_to" class="form-control" value="{{ $date_to }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-export btn-csv mt-auto">
                            <i class="fas fa-download"></i>
                            <span>Download CSV</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PDF Export -->
        <div class="col">
            <div class="report-card h-100 d-flex flex-column">
                <div class="report-card-header">
                    <div class="report-card-icon icon-pdf">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3>PDF Report</h3>
                    <p>Generate professional PDF reports for printing and archiving</p>
                </div>
                <div class="report-card-body flex-grow-1 d-flex flex-column">
                    <form action="{{ route('reports.export-pdf') }}" method="POST" target="_blank" class="d-flex flex-column h-100">
                        @csrf
                        <div class="flex-grow-1">
                            <div class="form-group">
                                <label for="pdf_transaction_source">Transaction Source</label>
                                <select id="pdf_transaction_source" name="transaction_source" class="form-select" required>
                                    <option value="all" {{ $transaction_source === 'all' ? 'selected' : '' }}>All Transactions</option>
                                    <option value="normal" {{ $transaction_source === 'normal' ? 'selected' : '' }}>Normal Transactions</option>
                                    <option value="inventory" {{ $transaction_source === 'inventory' ? 'selected' : '' }}>Inventory Transactions (Sales & Purchases)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="pdf_date_from">Date From</label>
                                <input type="date" id="pdf_date_from" name="date_from" class="form-control" value="{{ $date_from }}" required>
                            </div>
                            <div class="form-group">
                                <label for="pdf_date_to">Date To</label>
                                <input type="date" id="pdf_date_to" name="date_to" class="form-control" value="{{ $date_to }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-export btn-pdf mt-auto">
                            <i class="fas fa-file-pdf"></i>
                            <span>Generate PDF</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Report -->
        <div class="col">
            <div class="report-card h-100 d-flex flex-column">
                <div class="report-card-header">
                    <div class="report-card-icon icon-summary">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Summary Report</h3>
                    <p>View detailed analytics with category and payment breakdowns</p>
                </div>
                <div class="report-card-body flex-grow-1 d-flex flex-column">
                    <form action="{{ route('reports.export-summary') }}" method="POST" target="_blank" class="d-flex flex-column h-100">
                        @csrf
                        <div class="flex-grow-1">
                            <div class="form-group">
                                <label for="summary_transaction_source">Transaction Source</label>
                                <select id="summary_transaction_source" name="transaction_source" class="form-select" required>
                                    <option value="all" {{ $transaction_source === 'all' ? 'selected' : '' }}>All Transactions</option>
                                    <option value="normal" {{ $transaction_source === 'normal' ? 'selected' : '' }}>Normal Transactions</option>
                                    <option value="inventory" {{ $transaction_source === 'inventory' ? 'selected' : '' }}>Inventory Transactions (Sales & Purchases)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="summary_period">Period</label>
                                <select id="summary_period" name="period" class="form-select" required>
                                    <option value="daily">Daily Analysis</option>
                                    <option value="weekly">Weekly Analysis</option>
                                    <option value="monthly" selected>Monthly Analysis</option>
                                    <option value="yearly">Yearly Analysis</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="summary_date_from">Date From</label>
                                <input type="date" id="summary_date_from" name="date_from" class="form-control" value="{{ $date_from }}" required>
                            </div>
                            <div class="form-group">
                                <label for="summary_date_to">Date To</label>
                                <input type="date" id="summary_date_to" name="date_to" class="form-control" value="{{ $date_to }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-export btn-summary mt-auto">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Summary</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports Section -->
    <div class="quick-reports-section">
        <h2><i class="fas fa-bolt"></i> Quick Reports</h2>
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <form action="{{ route('reports.export-csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_source" value="all">
                    <input type="hidden" name="date_from" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                    <button type="submit" class="btn-quick">
                        <i class="fas fa-calendar-day"></i>
                        <span>Today</span>
                    </button>
                </form>
            </div>
            <div class="col-md-3 col-sm-6">
                <form action="{{ route('reports.export-csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_source" value="all">
                    <input type="hidden" name="date_from" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                    <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                    <button type="submit" class="btn-quick">
                        <i class="fas fa-calendar-week"></i>
                        <span>Last 7 Days</span>
                    </button>
                </form>
            </div>
            <div class="col-md-3 col-sm-6">
                <form action="{{ route('reports.export-csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_source" value="all">
                    <input type="hidden" name="date_from" value="{{ date('Y-m-01') }}">
                    <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                    <button type="submit" class="btn-quick">
                        <i class="fas fa-calendar-alt"></i>
                        <span>This Month</span>
                    </button>
                </form>
            </div>
            <div class="col-md-3 col-sm-6">
                <form action="{{ route('reports.export-csv') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_source" value="all">
                    <input type="hidden" name="date_from" value="{{ date('Y-01-01') }}">
                    <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                    <button type="submit" class="btn-quick">
                        <i class="fas fa-calendar"></i>
                        <span>Year to Date</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
