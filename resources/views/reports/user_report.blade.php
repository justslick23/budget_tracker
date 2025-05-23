<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monthly Report - {{ $user->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .header {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            padding: 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="0.5" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .user-info div {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .content {
            padding: 40px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 30px;
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            border-radius: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        thead {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #4a5568;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .amount {
            font-weight: 600;
            color: #2d3748;
        }

        .budget-amount {
            color: #6a82fb;
            font-weight: 700;
        }

        .expense-amount {
            color: #fc5c7d;
            font-weight: 700;
        }

        .summary-table {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
        }

        .summary-table th {
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            color: white;
            font-size: 1rem;
        }

        .summary-table td {
            font-size: 1.1rem;
            font-weight: 700;
            padding: 20px;
        }

        .total-budget {
            color: #6a82fb;
        }

        .total-expenses {
            color: #fc5c7d;
        }

        .remaining-balance {
            color: #10b981;
        }

        .remaining-balance.negative {
            color: #ef4444;
        }

        .category-tag {
            display: inline-block;
            background: linear-gradient(135deg, #6a82fb 0%, #fc5c7d 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .description {
            color: #64748b;
            font-style: italic;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.budget {
            border-left-color: #6a82fb;
        }

        .stat-card.expenses {
            border-left-color: #fc5c7d;
        }

        .stat-card.balance {
            border-left-color: #10b981;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .footer {
            background: #f8fafc;
            padding: 30px 40px;
            text-align: center;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            margin-bottom: 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .report-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .stat-card:hover,
            tbody tr:hover {
                transform: none;
                background: transparent;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

</head>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <div class="header">
            <h1>Monthly Budget & Expenses Report</h1>
            <div class="user-info">
                <div><strong>User:</strong> {{ $user->name }}</div>
                <div><strong>Period:</strong> {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</div>
            </div>
        </div>

        <div class="content">
            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card budget">
                    <div class="stat-value total-budget">M{{ number_format($totalBudget, 2) }}</div>
                    <div class="stat-label">Total Budget</div>
                </div>
                <div class="stat-card expenses">
                    <div class="stat-value total-expenses">M{{ number_format($totalExpenses, 2) }}</div>
                    <div class="stat-label">Total Expenses</div>
                </div>
                <div class="stat-card balance">
                    <div class="stat-value {{ ($totalBudget - $totalExpenses) < 0 ? 'remaining-balance negative' : 'remaining-balance' }}">
                        M{{ number_format($totalBudget - $totalExpenses, 2) }}
                    </div>
                    <div class="stat-label">Remaining Balance</div>
                </div>
            </div>

            <!-- Budget Section -->
            <div class="section">
                <h3 class="section-title">Budget Allocation</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Allocated Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $budget)
                            <tr>
                                <td>
                                    <span class="category-tag">{{ $budget->category->name }}</span>
                                </td>
                                <td class="budget-amount">M{{ number_format($budget->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Expenses Section -->
            <div class="section">
                <h3 class="section-title">Expense Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            <tr>
                                <td>
                                    <span class="category-tag">{{ $expense->category->name }}</span>
                                </td>
                                <td class="description">{{ $expense->description }}</td>
                                <td class="expense-amount">M{{ number_format($expense->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Section -->
            <div class="section">
                <h3 class="section-title">Financial Summary</h3>
                <table class="summary-table">
                    <tbody>
                        <tr>
                            <th>Total Budgeted Amount</th>
                            <td class="total-budget">M{{ number_format($totalBudget, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Expenses Incurred</th>
                            <td class="total-expenses">M{{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Remaining Balance</th>
                            <td class="{{ ($totalBudget - $totalExpenses) < 0 ? 'remaining-balance negative' : 'remaining-balance' }}">
                                M{{ number_format($totalBudget - $totalExpenses, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('l, F j, Y \a\t g:i A') }}</p>
            <p>This report provides a comprehensive overview of your monthly financial activity.</p>
        </div>
    </div>
</body>
</html>