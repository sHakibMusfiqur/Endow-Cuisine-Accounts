<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Summary Report - Restaurant Accounting</title>
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
            padding-bottom: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .company-logo {
            margin-bottom: 15px;
        }

        .company-logo img {
            max-width: 120px;
            max-height: 120px;
            object-fit: contain;
            display: inline-block;
        }

        .company-info {
            margin-top: 10px;
        }

        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
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

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
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

        /* Insight Box */
        .insight-box {
            background: #f0f0f0;
            border-left: 4px solid #000000;
            padding: 12px 15px;
            margin-bottom: 25px;
            font-size: 9pt;
        }

        .insight-title {
            font-weight: bold;
            color: #000000;
            margin-bottom: 5px;
        }

        .insight-content {
            color: #333333;
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

        .data-table tbody tr.total-row {
            background: #e0e0e0;
            font-weight: bold;
            border-top: 2px solid #000000;
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
        <button class="btn btn-print" onclick="window.print()">Print Report</button>
        <button class="btn btn-close" onclick="window.close()">Close</button>
    </div>

    <!-- Document Header -->
    <div class="document-header">
        @if(file_exists(public_path('images/logo.png')))
        <div class="company-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Endow Cuisine Logo">
        </div>
        @endif
        <div class="company-info">
            {{-- <div class="company-name">Endow Cuisine</div> --}}
            <div class="report-title">Financial Summary Report</div>
        </div>
    </div>

    <!-- Report Metadata -->
    <div class="report-meta">
        <div class="report-meta-row">
            <span class="meta-label">Generated:</span>
            <span class="meta-value">{{ $generated_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="report-meta-row">
            <span class="meta-label">Period:</span>
            <span class="meta-value">{{ $date_from }} to {{ $date_to }}</span>
        </div>
        <div class="report-meta-row">
            <span class="meta-label">Report ID:</span>
            <span class="meta-value">SUM-{{ date('YmdHis') }}</span>
        </div>
        <div class="report-meta-row">
            <span class="meta-label">Analysis Type:</span>
            <span class="meta-value">{{ ucfirst($period) }}</span>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Currency</div>
            <div class="info-value">{{ $activeCurrency->code }} ({{ $activeCurrency->symbol }})</div>
        </div>
        <div class="info-box">
            <div class="info-label">Total Transactions</div>
            <div class="info-value">{{ collect($category_wise)->sum('count') }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Categories</div>
            <div class="info-value">{{ count($category_wise) }}</div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="section-header">Executive Summary</div>
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
            <div class="summary-amount" style="color: {{ ($total_income - $total_expense) >= 0 ? '#2e7d32' : '#d32f2f' }}">
                {{ formatCurrency($total_income - $total_expense) }}
            </div>
        </div>
        @if(isset($total_damage_loss) && $total_damage_loss > 0)
        <div class="summary-card" style="border-left-color: #f57c00;">
            <div class="summary-label">Total Inventory Damage / Loss</div>
            <div class="summary-amount" style="color: #f57c00;">{{ formatCurrency($total_damage_loss) }}</div>
        </div>
        @endif
    </div>

    @php
        // PRODUCTION-SAFE: Type casting and null handling
        $safeIncome = isset($total_income) ? (float) $total_income : 0.0;
        $safeExpense = isset($total_expense) ? (float) $total_expense : 0.0;
        $netAmount = $safeIncome - $safeExpense;
        
        // PRODUCTION-SAFE: Epsilon for float comparison (avoids floating-point precision issues)
        $epsilon = 0.01; // Considered equal if difference < 1 cent
        
        // Calculate profit margin (accounting-correct, production-hardened)
        if ($safeIncome > $epsilon) {
            // Standard profit margin: (Net Income / Revenue) × 100
            $profitMargin = ($netAmount / $safeIncome) * 100;
        } elseif ($safeExpense > $epsilon) {
            // No income but expenses exist = 100% loss
            $profitMargin = -100.0;
        } else {
            // No income and no expenses = true break-even
            $profitMargin = 0.0;
        }
        
        // PRODUCTION-SAFE: Classify financial status explicitly
        $isProfit = $profitMargin > $epsilon;
        $isLoss = $profitMargin < -$epsilon;
        $isBreakeven = abs($profitMargin) <= $epsilon;
        $hasNoActivity = (abs($safeIncome) < $epsilon && abs($safeExpense) < $epsilon);
        $hasPureLoss = (abs($safeIncome) < $epsilon && $safeExpense > $epsilon);
    @endphp

    <div class="insight-box">
        <div class="insight-title">Financial Insight</div>
        <div class="insight-content">
            @if($isProfit)
                Profit Margin: <strong>{{ number_format($profitMargin, 2) }}%</strong> -
                Your restaurant is operating profitably with positive net income.
            @elseif($hasNoActivity)
                No Activity - No income or expenses recorded for this period.
            @elseif($isBreakeven)
                Break-even Status - Income and expenses are balanced.
            @elseif($hasPureLoss)
                Loss Margin: <strong>{{ number_format(abs($profitMargin), 2) }}%</strong> -
                Operating at a loss with no income. Immediate action required.
            @else
                Loss Margin: <strong>{{ number_format(abs($profitMargin), 2) }}%</strong> -
                Expenses exceed income. Review cost management strategies.
            @endif
        </div>
    </div>

    <!-- Category Analysis -->
    <div class="section-header">Category-wise Analysis</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Category</th>
                <th class="text-right" style="width: 17%;">Income</th>
                <th class="text-right" style="width: 17%;">Expense</th>
                <th class="text-right" style="width: 17%;">Net Amount</th>
                <th class="text-right" style="width: 12%;">Count</th>
                <th class="text-right" style="width: 12%;">% Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTransactions = collect($category_wise)->sum('count');
            @endphp
            @foreach($category_wise as $category => $data)
            <tr>
                <td><strong>{{ $category }}</strong></td>
                <td class="text-right">
                    @if($data['total_income'] > 0)
                        <span class="amount-positive">{{ formatCurrency($data['total_income']) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($data['total_expense'] > 0)
                        <span class="amount-negative">{{ formatCurrency($data['total_expense']) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right">
                    @php $netAmount = $data['total_income'] - $data['total_expense']; @endphp
                    <span style="color: {{ $netAmount >= 0 ? '#2e7d32' : '#d32f2f' }}; font-weight: bold;">
                        {{ formatCurrency($netAmount) }}
                    </span>
                </td>
                <td class="text-right"><span class="tag">{{ $data['count'] }}</span></td>
                <td class="text-right">{{ $totalTransactions > 0 ? number_format(($data['count'] / $totalTransactions) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right"><span class="amount-positive">{{ formatCurrency($total_income) }}</span></td>
                <td class="text-right"><span class="amount-negative">{{ formatCurrency($total_expense) }}</span></td>
                <td class="text-right">
                    <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#2e7d32' : '#d32f2f' }}">
                        {{ formatCurrency($total_income - $total_expense) }}
                    </span>
                </td>
                <td class="text-right"><span class="tag">{{ $totalTransactions }}</span></td>
                <td class="text-right">100.0%</td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Method Analysis -->
    <div class="section-header">Payment Method Analysis</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Payment Method</th>
                <th class="text-right" style="width: 17%;">Income</th>
                <th class="text-right" style="width: 17%;">Expense</th>
                <th class="text-right" style="width: 17%;">Net Amount</th>
                <th class="text-right" style="width: 12%;">Count</th>
                <th class="text-right" style="width: 12%;">% Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPaymentTransactions = $payment_method_wise->sum('count');
            @endphp
            @foreach($payment_method_wise as $method => $data)
            <tr>
                <td><strong>{{ $method }}</strong></td>
                <td class="text-right">
                    @if($data['total_income'] > 0)
                        <span class="amount-positive">{{ formatCurrency($data['total_income']) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($data['total_expense'] > 0)
                        <span class="amount-negative">{{ formatCurrency($data['total_expense']) }}</span>
                    @else
                        <span class="amount-neutral">—</span>
                    @endif
                </td>
                <td class="text-right">
                    @php $netAmount = $data['total_income'] - $data['total_expense']; @endphp
                    <span style="color: {{ $netAmount >= 0 ? '#2e7d32' : '#d32f2f' }}; font-weight: bold;">
                        {{ formatCurrency($netAmount) }}
                    </span>
                </td>
                <td class="text-right"><span class="tag">{{ $data['count'] }}</span></td>
                <td class="text-right">{{ $totalPaymentTransactions > 0 ? number_format(($data['count'] / $totalPaymentTransactions) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right"><span class="amount-positive">{{ formatCurrency($total_income) }}</span></td>
                <td class="text-right"><span class="amount-negative">{{ formatCurrency($total_expense) }}</span></td>
                <td class="text-right">
                    <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#2e7d32' : '#d32f2f' }}">
                        {{ formatCurrency($total_income - $total_expense) }}
                    </span>
                </td>
                <td class="text-right"><span class="tag">{{ $totalPaymentTransactions }}</span></td>
                <td class="text-right">100.0%</td>
            </tr>
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
