<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report - Restaurant Accounting</title>
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
            padding: 12px 14px;
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

        tbody tr:hover {
            background-color: #f9fafb;
        }

        td {
            padding: 10px 14px;
            font-size: 13px;
            color: #374151;
        }

        td.text-right {
            text-align: right;
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

        .balance-cell {
            font-weight: 600;
            font-family: 'Courier New', monospace;
            background: #fafafa;
        }

        .category-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
        }

        .payment-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
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
            <div class="report-title">Transaction Report</div>
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
                <div class="info-value">TXN-{{ date('YmdHis') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Records</div>
                <div class="info-value">{{ $transactions->count() }} transactions</div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="table-section">
            <div class="section-title">Transaction Details</div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Date</th>
                            <th style="width: 25%;">Description</th>
                            <th style="width: 14%;">Category</th>
                            <th style="width: 13%;">Payment</th>
                            <th class="text-right" style="width: 12%;">Income</th>
                            <th class="text-right" style="width: 12%;">Expense</th>
                            <th class="text-right" style="width: 14%;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td style="white-space: nowrap;">{{ $transaction->date->format('d/m/Y') }}</td>
                            <td>{{ strip_tags($transaction->description) }}</td>
                            <td><span class="category-badge">{{ $transaction->category->name }}</span></td>
                            <td><span class="payment-badge">{{ $transaction->paymentMethod->name }}</span></td>
                            <td class="text-right">
                                @if($transaction->income > 0)
                                    <span class="amount-income">{{ formatCurrency($transaction->income) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($transaction->expense > 0)
                                    <span class="amount-expense">{{ formatCurrency($transaction->expense) }}</span>
                                @else
                                    <span class="amount-neutral">—</span>
                                @endif
                            </td>
                            <td class="text-right balance-cell">{{ formatCurrency($transaction->balance) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="section-title">Financial Summary</div>
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
                    <div class="summary-card-amount" style="color: {{ $net_amount >= 0 ? '#059669' : '#dc2626' }}">
                        {{ formatCurrency($net_amount) }}
                    </div>
                </div>
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
