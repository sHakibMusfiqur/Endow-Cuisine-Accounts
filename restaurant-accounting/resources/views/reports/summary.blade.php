<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report - Restaurant Accounting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Inter', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1a1a1a;
            line-height: 1.65;
            background: #f8f9fa;
            padding: 40px 20px;
            font-size: 13.5px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        /* Professional Header - Clean & Minimal */
        .report-header {
            border-bottom: 3px solid #DC2626;
            padding-bottom: 25px;
            margin-bottom: 35px;
            background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
            padding: 20px;
            border-radius: 6px;
            margin: -40px -40px 35px -40px;
        }

        .company-name h1 {
            color: #111827;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .report-subtitle {
            color: #6b7280;
            font-size: 13.5px;
            margin-bottom: 18px;
            font-weight: 500;
        }

        .report-title-section {
            margin: 18px 0 0 0;
            padding: 15px 0 0 0;
            border-top: 1px solid #e5e7eb;
        }

        .report-title-section h2 {
            color: #111827;
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .report-period {
            color: #6b7280;
            font-size: 13.5px;
            font-weight: 500;
        }

        /* Current Date & Time Display */
        .generation-info {
            background: linear-gradient(135deg, #fef2f2 0%, #fef9f9 100%);
            padding: 12px 18px;
            margin: 25px 0;
            border-left: 4px solid #DC2626;
            font-size: 12.5px;
            color: #6b7280;
            border-radius: 4px;
        }

        .generation-info strong {
            color: #111827;
            font-weight: 600;
        }


        /* Info Cards - Minimal & Clean */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 35px;
        }

        .info-card {
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border: 1px solid #e5e7eb;
            border-left: 4px solid #DC2626;
            padding: 16px 18px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .info-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 15px;
            color: #111827;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        /* Executive Summary - Clean Cards */
        .executive-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin: 35px 0;
        }

        .summary-card {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 24px;
            position: relative;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-card.income {
            border-top: 4px solid #059669;
            background: linear-gradient(to bottom, #ffffff 0%, #f0fdf4 100%);
        }

        .summary-card.expense {
            border-top: 4px solid #DC2626;
            background: linear-gradient(to bottom, #ffffff 0%, #fef2f2 100%);
        }

        .summary-card.net {
            border-top: 4px solid #111827;
            background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
        }

        .summary-label {
            font-size: 11.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .summary-amount {
            font-size: 28px;
            font-weight: 800;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            letter-spacing: -0.5px;
        }

        .summary-card.income .summary-amount {
            color: #059669;
        }

        .summary-card.expense .summary-amount {
            color: #DC2626;
        }

        .summary-card.net .summary-amount {
            color: #111827;
        }

        /* Section Styling */
        .section {
            margin: 40px 0;
            page-break-inside: avoid;
        }

        .section-header {
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #DC2626;
        }

        .section-title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.3px;
        }

        /* Professional Tables - Accounting Grade */
        .table-wrapper {
            overflow-x: auto;
            margin: 25px 0;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: linear-gradient(to bottom, #f9fafb 0%, #f3f4f6 100%);
        }

        th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #374151;
            border-bottom: 2px solid #DC2626;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s ease;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover:not(.totals-row) {
            background-color: #fef2f2;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: #1f2937;
        }

        .text-right {
            text-align: right;
        }

        /* Category Names */
        .category-name {
            font-weight: 600;
            color: #111827;
        }

        /* Amount Styling */
        .amount-income {
            color: #059669;
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            font-size: 13px;
        }

        .amount-expense {
            color: #DC2626;
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            font-size: 13px;
        }

        .amount-neutral {
            color: #d1d5db;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            font-weight: 500;
        }

        /* Transaction Count Badge */
        .count-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #93c5fd;
        }

        /* Totals Row */
        tbody tr.totals-row {
            background: linear-gradient(to right, #f3f4f6 0%, #e5e7eb 100%);
            font-weight: 700;
            border-top: 3px solid #111827;
        }

        tbody tr.totals-row td {
            padding: 16px;
            font-weight: 800;
            font-size: 14px;
        }


        /* Action Buttons */
        .action-buttons {
            text-align: center;
            margin: 35px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 2px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }

        /* Footer */
        .report-footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #888888;
            font-size: 11px;
        }

        .footer-note {
            margin-bottom: 5px;
        }

        /* Insight Box - Minimal */
        .insight-box {
            background: linear-gradient(135deg, #fef9f9 0%, #fef2f2 100%);
            border-left: 4px solid #DC2626;
            padding: 18px 20px;
            margin: 25px 0;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .insight-title {
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
            font-size: 14px;
            letter-spacing: -0.2px;
        }

        .insight-text {
            color: #4b5563;
            font-size: 13.5px;
            line-height: 1.7;
        }

        @media print {
            body {
                padding: 15px;
            }
            .no-print {
                display: none !important;
            }
            .table-wrapper {
                border: 1px solid #cccccc;
            }
            tbody tr:nth-child(even) {
                background-color: #fafafa;
            }
            .summary-card {
                page-break-inside: avoid;
            }
            .section {
                page-break-inside: avoid;
            }
        }

        @page {
            margin: 15mm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Professional Header -->
        <div class="report-header">
            <div class="company-name">
                <h1>Restaurant Accounting</h1>
                <p class="report-subtitle">Financial Management System</p>
            </div>

            <div class="report-title-section">
                <h2>Financial Summary Report</h2>
                <p class="report-period">Period: {{ $date_from }} to {{ $date_to }} | Type: {{ strtoupper($period) }}</p>
            </div>
        </div>

        <!-- Current Date & Time Display -->
        <div class="generation-info">
            <strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}
        </div>


        <!-- Info Cards -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Currency</div>
                <div class="info-value">{{ $activeCurrency->name }} ({{ $activeCurrency->code }})</div>
            </div>
            <div class="info-card">
                <div class="info-label">Report Type</div>
                <div class="info-value">{{ ucfirst($period) }} Analysis</div>
            </div>
            <div class="info-card">
                <div class="info-label">Report ID</div>
                <div class="info-value">SUM-{{ date('YmdHis') }}</div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Executive Summary</h3>
            </div>
            
            <div class="executive-summary">
                <div class="summary-card income">
                    <div class="summary-label">Total Income</div>
                    <div class="summary-amount">{{ formatCurrency($total_income) }}</div>
                </div>
                
                <div class="summary-card expense">
                    <div class="summary-label">Total Expense</div>
                    <div class="summary-amount">{{ formatCurrency($total_expense) }}</div>
                </div>
                
                <div class="summary-card net">
                    <div class="summary-label">Net Amount</div>
                    <div class="summary-amount" style="color: {{ ($total_income - $total_expense) >= 0 ? '#27ae60' : '#EA222A' }}">
                        {{ formatCurrency($total_income - $total_expense) }}
                    </div>
                </div>
            </div>

            @php
                $profitMargin = $total_income > 0 ? (($total_income - $total_expense) / $total_income * 100) : 0;
            @endphp
            
            <div class="insight-box">
                <div class="insight-title">Financial Insight</div>
                <div class="insight-text">
                    @if($profitMargin > 0)
                        Profit Margin: <strong>{{ number_format($profitMargin, 2) }}%</strong> - 
                        Your restaurant is operating profitably with a positive net income.
                    @elseif($profitMargin == 0)
                        Break-even Status - Income and expenses are balanced.
                    @else
                        Loss Margin: <strong>{{ number_format(abs($profitMargin), 2) }}%</strong> - 
                        Expenses exceed income. Consider reviewing cost management strategies.
                    @endif
                </div>
            </div>
        </div>

        <!-- Category Analysis -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Category-wise Analysis</h3>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Total Income</th>
                            <th class="text-right">Total Expense</th>
                            <th class="text-right">Net Amount</th>
                            <th class="text-right">Transactions</th>
                            <th class="text-right">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalTransactions = collect($category_wise)->sum('count');
                        @endphp
                        @foreach($category_wise as $category => $data)
                        <tr>
                            <td><span class="category-name">{{ $category }}</span></td>
                            <td class="text-right">
                                @if($data['total_income'] > 0)
                                    <span class="amount-income">{{ formatCurrency($data['total_income']) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($data['total_expense'] > 0)
                                    <span class="amount-expense">{{ formatCurrency($data['total_expense']) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @php $netAmount = $data['total_income'] - $data['total_expense']; @endphp
                                <span style="color: {{ $netAmount >= 0 ? '#27ae60' : '#e74c3c' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($netAmount) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="count-badge">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-right">
                                <strong>{{ $totalTransactions > 0 ? number_format(($data['count'] / $totalTransactions) * 100, 1) : 0 }}%</strong>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="totals-row">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-right"><span class="amount-income">{{ formatCurrency($total_income) }}</span></td>
                            <td class="text-right"><span class="amount-expense">{{ formatCurrency($total_expense) }}</span></td>
                            <td class="text-right">
                                <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#27ae60' : '#e74c3c' }}; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($total_income - $total_expense) }}
                                </span>
                            </td>
                            <td class="text-right"><span class="count-badge">{{ $totalTransactions }}</span></td>
                            <td class="text-right"><strong>100%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Payment Method Analysis -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Payment Method Analysis</h3>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th class="text-right">Total Income</th>
                            <th class="text-right">Total Expense</th>
                            <th class="text-right">Net Amount</th>
                            <th class="text-right">Transactions</th>
                            <th class="text-right">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalPaymentTransactions = $payment_method_wise->sum('count');
                        @endphp
                        @foreach($payment_method_wise as $method => $data)
                        <tr>
                            <td><span class="category-name">{{ $method }}</span></td>
                            <td class="text-right">
                                @if($data['total_income'] > 0)
                                    <span class="amount-income">{{ formatCurrency($data['total_income']) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($data['total_expense'] > 0)
                                    <span class="amount-expense">{{ formatCurrency($data['total_expense']) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @php $netAmount = $data['total_income'] - $data['total_expense']; @endphp
                                <span style="color: {{ $netAmount >= 0 ? '#27ae60' : '#e74c3c' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($netAmount) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="count-badge">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-right">
                                <strong>{{ $totalPaymentTransactions > 0 ? number_format(($data['count'] / $totalPaymentTransactions) * 100, 1) : 0 }}%</strong>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="totals-row">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-right"><span class="amount-income">{{ formatCurrency($total_income) }}</span></td>
                            <td class="text-right"><span class="amount-expense">{{ formatCurrency($total_expense) }}</span></td>
                            <td class="text-right">
                                <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#27ae60' : '#e74c3c' }}; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($total_income - $total_expense) }}
                                </span>
                            </td>
                            <td class="text-right"><span class="count-badge">{{ $totalPaymentTransactions }}</span></td>
                            <td class="text-right"><strong>100%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
            <button class="btn btn-secondary" onclick="window.close()">Close</button>
        </div>

        <!-- Footer -->
        <div class="report-footer">
            <p class="footer-note">This is a computer-generated report and does not require a signature.</p>
            <p><strong>Restaurant Accounting System</strong> | Confidential Financial Document</p>
            <p>© {{ date('Y') }} All Rights Reserved</p>
        </div>
    </div>
</body>
</html>