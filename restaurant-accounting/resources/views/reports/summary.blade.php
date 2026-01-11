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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            line-height: 1.6;
            background: #f3f4f6;
            padding: 30px 20px;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px 60px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        /* CLEAN HEADER - No background, just text + divider */
        .report-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .report-subtitle {
            font-size: 13px;
            color: #9ca3af;
            font-weight: 400;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 6px;
        }

        /* Generated Time Section - clean with thin red accent */
        .meta-section {
            margin: 25px 0;
            padding: 16px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .generated-time {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .generated-time strong {
            color: #111827;
            font-weight: 600;
        }

        .report-period {
            font-size: 13px;
            color: #6b7280;
        }

        .report-period strong {
            color: #111827;
            font-weight: 600;
        }

        /* Info Row - Minimal cards with left accent */
        .info-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .info-item {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-left: 2px solid #dc2626;
            padding: 14px 18px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .info-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }

        /* Summary Cards - Modern Accounting Style */
        .summary-section {
            margin: 35px 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .summary-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-left: 2px solid #dc2626;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .summary-card-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .summary-card-amount {
            font-size: 26px;
            font-weight: 700;
            font-family: 'Courier New', 'Consolas', monospace;
            color: #111827;
            letter-spacing: -0.5px;
        }

        /* Table Design - Enterprise Standard */
        .table-section {
            margin: 35px 0;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 18px;
            border: 1px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #f9fafb;
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        th.text-right {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        tbody tr:hover:not(.total-row) {
            background-color: #f9fafb;
        }

        td {
            padding: 12px 16px;
            font-size: 13px;
            color: #374151;
        }

        td.text-right {
            text-align: right;
        }

        .category-name {
            font-weight: 500;
            color: #111827;
        }

        .amount-income {
            color: #059669;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .amount-expense {
            color: #dc2626;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .amount-neutral {
            color: #d1d5db;
        }

        .count-badge {
            display: inline-block;
            background: #f3f4f6;
            color: #374151;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        tbody tr.total-row {
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            font-weight: 600;
        }

        tbody tr.total-row td {
            padding: 14px 16px;
            color: #111827;
        }

        /* Insight Box */
        .insight-box {
            background: #fafafa;
            border-left: 2px solid #dc2626;
            padding: 16px 20px;
            margin: 25px 0;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
        }

        .insight-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 6px;
        }

        /* Action Buttons */
        .action-buttons {
            margin: 40px 0 30px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #111827;
            color: white;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-secondary {
            background: white;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        /* Footer - Minimal */
        .report-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }

        .footer-note {
            margin-bottom: 4px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .container {
                box-shadow: none;
                padding: 30px;
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
        <!-- Clean Header -->
        <div class="report-header">
            <div class="company-name">Restaurant Accounting</div>
            <div class="report-subtitle">Financial Management System</div>
            <div class="report-title">Financial Summary Report</div>
        </div>

        <!-- Meta Section with Local Timezone -->
        <div class="meta-section">
            <div class="generated-time">
                <strong>Generated on:</strong> {{ $generated_at->format('d M Y, h:i A') }} (Local Time)
            </div>
            <div class="report-period">
                <strong>Period:</strong> {{ $date_from }} → {{ $date_to }}
            </div>
        </div>

        <!-- Info Row -->
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Currency</div>
                <div class="info-value">{{ $activeCurrency->code }} ({{ $activeCurrency->symbol }})</div>
            </div>
            <div class="info-item">
                <div class="info-label">Report ID</div>
                <div class="info-value">SUM-{{ date('YmdHis') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Records</div>
                <div class="info-value">{{ collect($category_wise)->sum('count') }} transactions</div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="section-title">Executive Summary</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-label">Total Income</div>
                    <div class="summary-card-amount amount-income">{{ formatCurrency($total_income) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-label">Total Expense</div>
                    <div class="summary-card-amount amount-expense">{{ formatCurrency($total_expense) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-label">Net Amount</div>
                    <div class="summary-card-amount" style="color: {{ ($total_income - $total_expense) >= 0 ? '#059669' : '#dc2626' }}">
                        {{ formatCurrency($total_income - $total_expense) }}
                    </div>
                </div>
            </div>

            @php
                $profitMargin = $total_income > 0 ? (($total_income - $total_expense) / $total_income * 100) : 0;
            @endphp
            
            <div class="insight-box">
                <div class="insight-title">Financial Insight</div>
                <div>
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

        <!-- Category Analysis Table -->
        <div class="table-section">
            <div class="section-title">Category-wise Analysis</div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Income</th>
                            <th class="text-right">Expense</th>
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
                                <span style="color: {{ $netAmount >= 0 ? '#059669' : '#dc2626' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($netAmount) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="count-badge">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-right">
                                {{ $totalTransactions > 0 ? number_format(($data['count'] / $totalTransactions) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="text-right"><span class="amount-income">{{ formatCurrency($total_income) }}</span></td>
                            <td class="text-right"><span class="amount-expense">{{ formatCurrency($total_expense) }}</span></td>
                            <td class="text-right">
                                <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#059669' : '#dc2626' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($total_income - $total_expense) }}
                                </span>
                            </td>
                            <td class="text-right"><span class="count-badge">{{ $totalTransactions }}</span></td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Method Analysis Table -->
        <div class="table-section">
            <div class="section-title">Payment Method Analysis</div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th class="text-right">Income</th>
                            <th class="text-right">Expense</th>
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
                                <span style="color: {{ $netAmount >= 0 ? '#059669' : '#dc2626' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($netAmount) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="count-badge">{{ $data['count'] }}</span>
                            </td>
                            <td class="text-right">
                                {{ $totalPaymentTransactions > 0 ? number_format(($data['count'] / $totalPaymentTransactions) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="text-right"><span class="amount-income">{{ formatCurrency($total_income) }}</span></td>
                            <td class="text-right"><span class="amount-expense">{{ formatCurrency($total_expense) }}</span></td>
                            <td class="text-right">
                                <span style="color: {{ ($total_income - $total_expense) >= 0 ? '#059669' : '#dc2626' }}; font-weight: 600; font-family: 'Courier New', monospace;">
                                    {{ formatCurrency($total_income - $total_expense) }}
                                </span>
                            </td>
                            <td class="text-right"><span class="count-badge">{{ $totalPaymentTransactions }}</span></td>
                            <td class="text-right">100%</td>
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
            <p class="footer-note">Generated by Restaurant Accounting System</p>
            <p>Page 1 of 1</p>
        </div>
    </div>
</body>
</html>
