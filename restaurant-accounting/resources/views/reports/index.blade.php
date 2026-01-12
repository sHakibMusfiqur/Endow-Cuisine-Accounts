@extends('layouts.app')

@section('title', 'Reports - Restaurant Accounting')
@section('page-title', 'Reports & Export')

@section('content')
<style>
    /* Mobile Responsive Styles for Reports Page */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        /* Report Cards - Stack Vertically */
        .row > [class*='col-md'] {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
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

        .form-control,
        .form-select {
            font-size: 16px; /* Prevents iOS zoom */
            padding: 10px 12px;
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

        .row.g-3 {
            gap: 10px !important;
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

        .card-body {
            padding: 12px;
        }

        .card-title {
            font-size: 0.95rem;
        }

        .card-text {
            font-size: 0.85rem;
        }

        .form-label {
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

        .btn-outline-primary {
            padding: 10px 14px;
            font-size: 0.85rem;
        }
    }

    /* Tablet - Side by side for 2 columns */
    @media (min-width: 481px) and (max-width: 768px) {
        .row > [class*='col-md-4'] {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
        }

        .row.g-3 > div {
            width: 50%;
            flex: 0 0 50%;
            max-width: 50%;
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

<div class="container-fluid">
    <div class="row">
        <!-- CSV Export -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-csv text-success"></i> Export to CSV</h5>
                    <p class="card-text">Export transaction data in CSV format for Excel or other spreadsheet applications.</p>
                    <form action="{{ route('reports.export-csv') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PDF Export -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-pdf text-danger"></i> Export to PDF</h5>
                    <p class="card-text">Generate a PDF report of transactions for the selected date range.</p>
                    <form action="{{ route('reports.export-pdf') }}" method="POST" target="_blank">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Report -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-pie text-info"></i> Summary Report</h5>
                    <p class="card-text">View category-wise and payment method-wise summary for analysis.</p>
                    <form action="{{ route('reports.export-summary') }}" method="POST" target="_blank">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Period</label>
                            <select name="period" class="form-select" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar"></i> View Summary
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Reports -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-bolt"></i> Quick Reports</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <form action="{{ route('reports.export-csv') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-day"></i> Today's Report (CSV)
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('reports.export-csv') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                        <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-week"></i> This Week (CSV)
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('reports.export-csv') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ date('Y-m-01') }}">
                        <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar-alt"></i> This Month (CSV)
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form action="{{ route('reports.export-csv') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ date('Y-01-01') }}">
                        <input type="hidden" name="date_to" value="{{ date('Y-m-d') }}">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-calendar"></i> This Year (CSV)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
