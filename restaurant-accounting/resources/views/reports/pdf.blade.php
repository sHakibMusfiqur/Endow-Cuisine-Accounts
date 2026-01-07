<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .totals {
            margin-top: 20px;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: green;
        }
        .text-danger {
            color: red;
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
        <h2>Transaction Report</h2>
        <p>Period: {{ $date_from }} to {{ $date_to }}</p>
    </div>

    <div class="info">
        <p><strong>Generated on:</strong> {{ date('F d, Y H:i:s') }}</p>
        <p><strong>Total Transactions:</strong> {{ $transactions->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th>Payment Method</th>
                <th class="text-right">Income</th>
                <th class="text-right">Expense</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('Y-m-d') }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ $transaction->category->name }}</td>
                <td>{{ $transaction->paymentMethod->name }}</td>
                <td class="text-right text-success">
                    {{ $transaction->income > 0 ? '₹'.number_format($transaction->income, 2) : '-' }}
                </td>
                <td class="text-right text-danger">
                    {{ $transaction->expense > 0 ? '₹'.number_format($transaction->expense, 2) : '-' }}
                </td>
                <td class="text-right">₹{{ number_format($transaction->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total Income: <span class="text-success">₹{{ number_format($total_income, 2) }}</span></p>
        <p>Total Expense: <span class="text-danger">₹{{ number_format($total_expense, 2) }}</span></p>
        <p>Net Amount: <span style="color: {{ $net_amount >= 0 ? 'green' : 'red' }}">₹{{ number_format($net_amount, 2) }}</span></p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()">Print PDF</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
