<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report - Restaurant Accounting</title>
    <style>
        /* Professional A4 PDF Styling */
        @page {
            size: A4;
            margin: 20mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000000;
            background: #ffffff;
        }

        /* Document Header */
        .document-header {
            border-bottom: 3px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company-info {
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 3px;
        }

        .company-tagline {
            font-size: 9pt;
            color: #666666;
            margin-bottom: 12px;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            color: #000000;
        }

        /* Report Metadata */
        .report-meta {
            background: #f5f5f5;
            border-left: 4px solid #000000;
            padding: 12px 15px;
            margin-bottom: 25px;
            font-size: 9pt;
        }

        .report-meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .report-meta-row:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: bold;
            color: #000000;
        }

        .meta-value {
            color: #333333;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-box {
            border: 1px solid #dddddd;
            border-left: 3px solid #000000;
            padding: 12px;
            background: #fafafa;
        }

        .info-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666666;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .info-value {
            font-size: 11pt;
            font-weight: bold;
            color: #000000;
        }

        /* Section Headers */
        .section-header {
            font-size: 12pt;
            font-weight: bold;
            color: #000000;
            padding-bottom: 8px;
            border-bottom: 2px solid #dddddd;
            margin-bottom: 15px;
            margin-top: 25px;
        }

        /* Professional Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 9pt;
        }

        .data-table thead {
            background: #000000;
            color: #ffffff;
        }

        .data-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table th.text-right {
            text-align: right;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .data-table td {
            padding: 8px;
            color: #333333;
        }

        .data-table td.text-right {
            text-align: right;
        }

        /* Financial Amounts */
        .amount-positive {
            color: #2e7d32;
            font-weight: bold;
        }

        .amount-negative {
            color: #d32f2f;
            font-weight: bold;
        }

        .amount-neutral {
            color: #999999;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            border: 2px solid #000000;
            padding: 15px;
            background: #ffffff;
        }

        .summary-label {
            font-size: 8pt;
            text-transform: uppercase;
            color: #666666;
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .summary-amount {
            font-size: 18pt;
            font-weight: bold;
            color: #000000;
            font-family: 'Courier New', monospace;
        }

        /* Tags/Badges */
        .tag {
            display: inline-block;
            padding: 3px 8px;
            background: #e0e0e0;
            color: #333333;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
        }

        /* Document Footer */
        .document-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #000000;
            font-size: 8pt;
            text-align: center;
            color: #666666;
        }

        .footer-info {
            margin-bottom: 5px;
        }

        /* Print Optimizations */
        @media print {
            body {
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* Print Buttons */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 10pt;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-print {
            background: #000000;
            color: #ffffff;
        }

        .btn-print:hover {
            background: #333333;
        }

        .btn-close {
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
        }

        .btn-close:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
        <button class="btn btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <!-- Document Header -->
    <div class="document-header">
        <div class="company-info">
            <div class="company-name">Restaurant Accounting</div>
            <div class="company-tagline">Financial Management System</div>
        </div>
        <div class="report-title">Transaction Report</div>
    </div>

    <!-- Report Metadata -->
    <div class="report-meta">
        <div class="report-meta-row">
            <span class="meta-label">Generated:</span>
            <span class="meta-value">{{ now()->format('d M Y, h:i A') }}</span>
        </div>
        <div class="report-meta-row">
            <span class="meta-label">Period:</span>
            <span class="meta-value">{{ $date_from }} to {{ $date_to }}</span>
        </div>
        <div class="report-meta-row">
            <span class="meta-label">Report ID:</span>
            <span class="meta-value">TXN-{{ date('YmdHis') }}</span>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Currency</div>
            <div class="info-value">{{ $activeCurrency->code }} ({{ $activeCurrency->symbol }})</div>
        </div>
        <div class="info-box">
            <div class="info-label">Total Records</div>
            <div class="info-value">{{ $transactions->count() }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Date Range</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($date_from)->diffInDays(\Carbon\Carbon::parse($date_to)) + 1 }} days</div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="section-header">Financial Summary</div>
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Income</div>
            <div class="summary-amount amount-positive">{{ formatCurrency($total_income) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Expense</div>
            <div class="summary-amount amount-negative">{{ formatCurrency($total_expense) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Net Amount</div>
            <div class="summary-amount" style="color: {{ $net_amount >= 0 ? '#2e7d32' : '#d32f2f' }}">
                {{ formatCurrency($net_amount) }}
            </div>
        </div>
    </div>

    <!-- Transaction Details -->
    <div class="section-header">Transaction Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 13%;">Category</th>
                <th style="width: 12%;">Payment</th>
                <th class="text-right" style="width: 12%;">Income</th>
                <th class="text-right" style="width: 12%;">Expense</th>
                <th class="text-right" style="width: 11%;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ strip_tags($transaction->description) }}</td>
                <td><span class="tag">{{ $transaction->category->name }}</span></td>
                <td><span class="tag">{{ $transaction->paymentMethod->name }}</span></td>
                <td class="text-right">
                    @if($transaction->income > 0)
                        <span class="amount-positive">{{ formatCurrency($transaction->income) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($transaction->expense > 0)
                        <span class="amount-negative">{{ formatCurrency($transaction->expense) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right" style="font-weight: bold;">{{ formatCurrency($transaction->balance) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Document Footer -->
    <div class="document-footer">
        <div class="footer-info"><strong>Restaurant Accounting System</strong></div>
        <div class="footer-info">This report is computer-generated and does not require a signature</div>
        <div class="footer-info">Page 1 of 1 | Generated on {{ now()->format('d M Y, H:i') }}</div>
    </div>
</body>
</html>
