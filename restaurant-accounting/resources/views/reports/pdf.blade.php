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


        /* Professional Table - Accounting Grade */
        .table-wrapper {
            overflow-x: auto;
            margin: 28px 0;
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

        tbody tr:hover {
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

        .text-center {
            text-align: center;
        }

        /* Amount Styling */
        .amount-positive {
            color: #059669;
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            font-size: 13px;
        }

        .amount-negative {
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

        .balance-cell {
            font-weight: 700;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            background: linear-gradient(to right, #f9fafb 0%, #f3f4f6 100%);
            font-size: 13.5px;
        }

        /* Financial Summary Section */
        .financial-summary {
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border: 2px solid #e5e7eb;
            padding: 28px;
            margin: 35px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .summary-title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #DC2626;
            letter-spacing: -0.3px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .summary-item {
            background: white;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .summary-item.income {
            border-top: 4px solid #059669;
        }

        .summary-item.expense {
            border-top: 4px solid #DC2626;
        }

        .summary-item.net {
            border-top: 4px solid #111827;
        }

        .summary-item .label {
            font-size: 11.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .summary-item .value {
            font-size: 22px;
            font-weight: 800;
            font-family: 'SF Mono', 'Monaco', 'Consolas', 'Courier New', monospace;
            letter-spacing: -0.5px;
        }

        .summary-item.income .value {
            color: #059669;
        }

        .summary-item.expense .value {
            color: #DC2626;
        }

        .summary-item.net .value {
            color: #111827;
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

        /* Action Buttons */
        .action-buttons {
            text-align: center;
            margin: 30px 0;
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

        /* Category Badge */
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            font-size: 11px;
            font-weight: 600;
            border-radius: 4px;
            border: 1px solid #93c5fd;
        }

        .payment-badge {
            display: inline-block;
            padding: 4px 10px;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
            font-size: 11px;
            font-weight: 600;
            border-radius: 4px;
            border: 1px solid #a5b4fc;
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
                <h2>Transaction Report</h2>
                <p class="report-period">Period: {{ $date_from }} to {{ $date_to }}</p>
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
                <div class="info-label">Total Transactions</div>
                <div class="info-value">{{ $transactions->count() }} Records</div>
            </div>
            <div class="info-card">
                <div class="info-label">Report ID</div>
                <div class="info-value">TXN-{{ date('YmdHis') }}</div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 15%;">Category</th>
                        <th style="width: 13%;">Payment</th>
                        <th class="text-right" style="width: 12%;">Income</th>
                        <th class="text-right" style="width: 12%;">Expense</th>
                        <th class="text-right" style="width: 13%;">Balance</th>
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
                        <td class="text-right balance-cell">{{ formatCurrency($transaction->balance) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="financial-summary">
            <div class="summary-title">Financial Summary</div>
            <div class="summary-grid">
                <div class="summary-item income">
                    <div class="label">Total Income</div>
                    <div class="value">{{ formatCurrency($total_income) }}</div>
                </div>
                <div class="summary-item expense">
                    <div class="label">Total Expense</div>
                    <div class="value">{{ formatCurrency($total_expense) }}</div>
                </div>
                <div class="summary-item net">
                    <div class="label">Net Amount</div>
                    <div class="value" style="color: {{ $net_amount >= 0 ? '#27ae60' : '#e74c3c' }}">
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
            <p class="footer-note">This is a computer-generated report and does not require a signature.</p>
            <p><strong>Restaurant Accounting System</strong> | Confidential Financial Document</p>
            <p>© {{ date('Y') }} All Rights Reserved</p>
        </div>
    </div>
</body>
</html>
