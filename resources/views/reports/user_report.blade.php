<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report - {{ $user->name }}</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Monthly Budget & Expenses Report</h2>
    <p><strong>User:</strong> {{ $user->name }}</p>
    <p><strong>Month:</strong> {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</p>

    <h3>Budget</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount (M)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $budget)
                <tr>
                    <td>{{ $budget->category->name }}</td>
                    <td>{{ number_format($budget->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Expenses</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Amount (M)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->category->name }}</td>
                    <td>{{ $expense->description }}</td>

                    <td>{{ number_format($expense->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Summary</h3>
    <table>
        <tr>
            <th>Total Budget</th>
            <td>M{{ number_format($totalBudget, 2) }}</td>
        </tr>
        <tr>
            <th>Total Expenses</th>
            <td>M{{ number_format($totalExpenses, 2) }}</td>
        </tr>
        <tr>
            <th>Remaining Balance</th>
            <td>M{{ number_format($totalBudget - $totalExpenses, 2) }}</td>
        </tr>
    </table>

</body>
</html>
