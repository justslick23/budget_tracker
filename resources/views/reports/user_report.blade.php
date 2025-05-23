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
            color: #e2e8f0; /* Lighter text for dark background */
            background-color: #1a202c; /* Dark charcoal */
            min-height: 100vh;
            padding: 25px;
        }

        .report-container {
            max-width: 800px;
            margin: 0 auto;
            background: #2d3748; /* Darker main container */
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3); /* Stronger shadow for static view */
        }

        .header {
            background-color: #eb3300; /* Solid color matching one of your gradient ends */
            padding: 45px;
            text-align: center;
            color: white;
            position: relative;
            border-bottom: 8px solid rgba(0, 0, 0, 0.2);
        }

        .header::before {
            content: none; /* Explicitly remove any potential fallback */
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 800; /* Extra bold */
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            position: relative;
            z-index: 1;
            font-size: 1rem;
        }

        .user-info div {
            background: rgba(255, 255, 255, 0.1); /* Very subtle background */
            padding: 10px 22px;
            border-radius: 30px;
            /* backdrop-filter will behave differently across PDF renderers,
               but the background color provides a good fallback */
            border: 1px solid rgba(255, 255, 255, 0.15); /* Light border */
        }

        .content {
            padding: 40px;
        }

        .section {
            margin-bottom: 45px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #edf2f7; /* Lighter title color */
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .section-title::before {
            content: '';
            width: 5px;
            height: 35px;
            background: linear-gradient(135deg, #f68c34 0%, #eb3300 100%); /* Matching header gradient */
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #2d3748; /* Darker table background */
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 25px;
        }

        thead {
            background: #4a5568; /* Darker header */
        }

        th {
            padding: 20px 25px;
            text-align: left;
            font-weight: 700;
            color: #edf2f7; /* White text */
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 3px solid #2d3748; /* Thicker border */
        }

        td {
            padding: 18px 25px;
            border-bottom: 1px solid #4a5568; /* Darker border */
            color: #cbd5e1; /* Lighter text */
        }

        /* No specific tbody tr:hover as it's for static PDF */

        .amount {
            font-weight: 700;
            color: #edf2f7;
        }

        .budget-amount {
            color: #38a169; /* Green for budget */
            font-weight: 700;
        }

        .expense-amount {
            color: #e53e3e; /* Red for expenses */
            font-weight: 700;
        }

        .summary-table {
            background: #1a202c; /* Even darker for summary */
            border: 2px solid #4a5568; /* Stronger border */
        }

        .summary-table th {
            background: linear-gradient(135deg, #f68c34 0%, #eb3300 100%); /* Matching header gradient */
            color: white;
            font-size: 1.1rem;
            padding: 22px;
            border-bottom: none;
        }

        .summary-table td {
            font-size: 1.25rem;
            font-weight: 800;
            padding: 25px;
            color: white;
            border-bottom: none;
        }

        .total-budget {
            color: #48bb78; /* Brighter green */
        }

        .total-expenses {
            color: #f56565; /* Brighter red */
        }

        .remaining-balance {
            color: #68d391; /* Lighter green for positive */
        }

        .remaining-balance.negative {
            color: #fc8181; /* Lighter red for negative */
        }

        .category-tag {
            display: inline-block; /* Ensure it behaves well in tables */
            background: #4a5568; /* Dark grey tag */
            color: #cbd5e1; /* Lighter text */
            padding: 5px 14px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #64748b;
        }

        .description {
            color: #a0aec0; /* Softer text */
            font-style: italic;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #2d3748; /* Darker card background */
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid; /* Thicker border */
            /* No transform or transition for static PDF */
        }

        .stat-card.budget {
            border-left-color: #48bb78; /* Green */
        }

        .stat-card.expenses {
            border-left-color: #e53e3e; /* Red */
        }

        .stat-card.balance {
            border-left-color: #68d391; /* Lighter Green */
        }

        .stat-value {
            font-size: 2.3rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: white;
        }

        .stat-label {
            color: #a0aec0;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.7px;
        }

        .footer {
            background: #1a202c; /* Dark footer */
            padding: 35px 40px;
            text-align: center;
            color: #a0aec0;
            border-top: 2px solid #4a5568; /* Stronger border */
            font-size: 0.95rem;
        }

        /* Print-specific adjustments */
        @media print {
            body {
                background: white; /* White background for print */
                padding: 0;
                color: #2d3748; /* Dark text for print */
            }

            .report-container {
                box-shadow: none; /* No shadows in print */
                border-radius: 0;
                border: 1px solid #e2e8f0; /* Light border for structure */
            }

            .header {
                background: linear-gradient(135deg, #f68c34 0%, #eb3300 100%) !important; /* Ensure gradient prints */
                -webkit-print-color-adjust: exact; /* Crucial for background colors to print */
                color: white !important;
                border-bottom-color: rgba(0, 0, 0, 0.1) !important;
            }

            .header::before {
                background: none; /* Remove grain for cleaner print */
            }

            .user-info div {
                background: none !important; /* No background for print */
                border: none !important;
                color: white !important; /* Keep white text */
            }

            .content {
                padding: 30px;
                background: white; /* White background for content in print */
            }

            .section-title {
                color: #2d3748 !important; /* Dark text for titles in print */
            }

            .section-title::before {
                background: linear-gradient(135deg, #f68c34 0%, #eb3300 100%) !important;
                -webkit-print-color-adjust: exact;
            }

            table {
                box-shadow: none; /* No table shadows */
                border: 1px solid #e2e8f0; /* Light border for tables */
                background: white; /* White background for tables */
            }

            thead {
                background: #f1f5f9 !important; /* Light header for print */
                -webkit-print-color-adjust: exact;
            }

            th {
                color: #2d3748 !important; /* Dark text for table headers */
                border-bottom-color: #cbd5e1 !important;
            }

            td {
                color: #4a5568 !important; /* Dark text for table cells */
                border-bottom-color: #f1f5f9 !important;
            }

            .budget-amount, .expense-amount {
                -webkit-print-color-adjust: exact; /* Ensure these colors print */
            }

            .summary-table {
                background: #f8fafc !important; /* Light background for print */
                border-color: #cbd5e1 !important;
            }

            .summary-table th {
                background: linear-gradient(135deg, #f68c34 0%, #eb3300 100%) !important;
                -webkit-print-color-adjust: exact;
                color: white !important;
            }

            .summary-table td {
                color: #2d3748 !important; /* Dark text for summary amounts */
            }

            .total-budget, .total-expenses, .remaining-balance, .remaining-balance.negative {
                -webkit-print-color-adjust: exact; /* Ensure these colors print */
            }

            .category-tag {
                background: #e0e7ff !important; /* Light background for print */
                color: #4f46e5 !important; /* Dark blue text */
                -webkit-print-color-adjust: exact;
                border-color: #c7d2fe !important;
            }

            .stats-grid {
                page-break-inside: avoid; /* Keep stats together */
            }

            .stat-card {
                box-shadow: none; /* No shadow for print */
                border-left-width: 3px; /* Slightly thinner border for print */
                background: white !important; /* White background for cards in print */
            }

            .stat-value {
                color: #2d3748 !important; /* Dark text for stat values in print */
            }

            .stat-label {
                color: #64748b !important; /* Dark text for stat labels in print */
            }

            .footer {
                background: #f1f5f9 !important; /* Light footer for print */
                color: #64748b !important;
                border-top-color: #e2e8f0 !important;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="report-container">
        <div class="header">
            <h1>Monthly Budget & Expenses Report</h1>
            <div class="user-info">
                <div><strong>User:</strong> {{ $user->name }}</div>
                <div><strong>Period:</strong> {{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</div>
            </div>
        </div>

        <div class="content">
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

        <div class="footer">
            <p><strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('l, F j, Y \a\t g:i A') }}</p>
            <p>This report provides a comprehensive overview of your monthly financial activity.</p>
        </div>
    </div>
</body>
</html>