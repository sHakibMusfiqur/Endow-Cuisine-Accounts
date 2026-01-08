<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Restaurant Accounting</h1>
        <h2>Summary Report</h2>
        <p>Period: {{ $date_from }} to {{ $date_to }}</p>
        <p>Report Type: {{ ucfirst($period) }}</p>
        <p><strong>Currency:</strong> {{ $activeCurrency->name }} ({{ $activeCurrency->code }})</p>
    </div>

    <div class="section">
        <h3>Overall Summary</h3>
        <div class="summary-card">
            <p><strong>Total Income:</strong> <span style="color: green;">{{ formatCurrency($total_income) }}</span></p>
            <p><strong>Total Expense:</strong> <span style="color: red;">{{ formatCurrency($total_expense) }}</span></p>
            <p><strong>Net Amount:</strong> <span style="color: {{ ($total_income - $total_expense) >= 0 ? 'green' : 'red' }}">
                {{ formatCurrency($total_income - $total_expense) }}
            </span></p>
        </div>
    </div>

    <div class="section">
        <h3>Category-wise Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Total Income</th>
                    <th class="text-right">Total Expense</th>
                    <th class="text-right">Transaction Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category_wise as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td class="text-right" style="color: green;">{{ formatCurrency($data['total_income']) }}</td>
                    <td class="text-right" style="color: red;">{{ formatCurrency($data['total_expense']) }}</td>
                    <td class="text-right">{{ $data['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Payment Method-wise Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-right">Total Income</th>
                    <th class="text-right">Total Expense</th>
                    <th class="text-right">Transaction Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_method_wise as $method => $data)
                <tr>
                    <td>{{ $method }}</td>
                    <td class="text-right" style="color: green;">{{ formatCurrency($data['total_income']) }}</td>
                    <td class="text-right" style="color: red;">{{ formatCurrency($data['total_expense']) }}</td>
                    <td class="text-right">{{ $data['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()">Print Report</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
        <p>Restaurant Accounting System</p>
    </div>
</body>
</html>
