<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <title>Monthly Report - {{ $user->name }}</title>
    <style>
        /* Universal Box Sizing */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #2d3748; /* Dark text for a professional look */
            background-color: #f8fafc; /* Very light background */
            min-height: 100vh;
            padding: 25px; /* Padding around the main container */
        }

        /* Report Container */
        .report-container {
            max-width: 800px; /* Fixed width for PDF consistency */
            margin: 0 auto; /* Center the container */
            background: white; /* White background for the report content */
            border-radius: 12px; /* Slightly softer rounded corners */
            overflow: hidden; /* Ensures content stays within rounded corners */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08); /* Subtle shadow for depth */
        }

        /* Header Section */
        .header {
            background-color: #2c5282; /* Deep professional blue */
            padding: 40px; /* Ample padding */
            text-align: center;
            color: white; /* White text for contrast */
            position: relative;
            border-bottom: 6px solid rgba(0, 0, 0, 0.1); /* Subtle dark border at the bottom */
        }

        /* Remove the grain effect as it caused PDF generation issues */
        .header::before {
            content: none;
        }

        .header h1 {
            font-size: 2.5rem; /* Large and prominent title */
            font-weight: 700; /* Bold font weight */
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1); /* Subtle text shadow */
        }

        /* User Information in Header */
        .user-info {
            display: flex;
            justify-content: space-between; /* Distribute items horizontally */
            align-items: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
            font-size: 0.95rem; /* Slightly smaller font for info */
        }

        .user-info div {
            background: rgba(255, 255, 255, 0.1); /* Very subtle translucent background */
            padding: 8px 18px;
            border-radius: 20px; /* Pill shape */
            border: 1px solid rgba(255, 255, 255, 0.15); /* Light border */
        }

        /* Main Content Area */
        .content {
            padding: 35px 40px; /* Consistent padding */
        }

        /* Section Spacing */
        .section {
            margin-bottom: 40px; /* Space between sections */
        }

        /* Section Titles */
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748; /* Dark text for titles */
            display: flex;
            align-items: center;
            gap: 12px; /* Space between bar and text */
        }

        /* Vertical accent bar for section titles */
        .section-title::before {
            content: '';
            width: 4px;
            height: 30px;
            background-color: #3182ce; /* A slightly lighter blue accent */
            border-radius: 2px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse; /* Remove double borders */
            background: white; /* White background for tables */
            border-radius: 10px; /* Rounded table corners */
            overflow: hidden; /* Ensures rounded corners are visible */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Subtle table shadow */
            margin-bottom: 20px;
        }

        thead {
            background-color: #e2e8f0; /* Light grey for table header background */
        }

        th {
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            color: #4a5568; /* Darker grey for header text */
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1; /* Light border below header */
        }

        td {
            padding: 14px 20px;
            border-bottom: 1px solid #edf2f7; /* Very light border between rows */
            color: #4a5568; /* Standard text color for table cells */
        }

        /* No hover effects for PDF */

        /* Amount Styling */
        .amount {
            font-weight: 600;
            color: #2d3748;
        }

        .budget-amount {
            color: #38a169; /* Green for budget */
            font-weight: 700;
        }

        .expense-amount {
            color: #e53e3e; /* Red for expenses */
            font-weight: 700;
        }

        /* Summary Table Specifics */
        .summary-table {
            background: #f1f5f9; /* Slightly darker light grey for summary table */
            border: 1px solid #cbd5e1; /* Light border around summary table */
        }

        .summary-table th {
            background-color: #2c5282; /* Deep blue for summary header */
            color: white;
            font-size: 1rem;
            padding: 18px 20px;
            border-bottom: none; /* No bottom border for summary header */
        }

        .summary-table td {
            font-size: 1.1rem;
            font-weight: 700;
            padding: 20px;
            color: #2d3748; /* Dark text for summary values */
        }

        /* Summary Amount Colors */
        .total-budget {
            color: #38a169; /* Green */
        }

        .total-expenses {
            color: #e53e3e; /* Red */
        }

        .remaining-balance {
            color: #3182ce; /* Blue for positive balance */
        }

        .remaining-balance.negative {
            color: #e53e3e; /* Red for negative balance */
        }

        /* Category Tag */
        .category-tag {
            display: inline-block;
            background-color: #ebf8ff; /* Very light blue background */
            color: #2b6cb0; /* Darker blue text */
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Description Text */
        .description {
            color: #64748b; /* Softer grey for description */
            font-style: italic;
        }

        /* Stats Grid Layout */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Responsive grid for cards */
            gap: 20px; /* Space between cards */
            margin-bottom: 30px;
        }

        /* Stat Card Styling */
        .stat-card {
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); /* Subtle shadow */
            border: 1px solid; /* Full border for the card */
        }

        .stat-card.budget {
            background-color: #f0fff4; /* Very light green background */
            border-color: #38a169; /* Green border */
        }

        .stat-card.expenses {
            background-color: #fff5f5; /* Very light red background */
            border-color: #e53e3e; /* Red border */
        }

        .stat-card.balance {
            background-color: #ebf8ff; /* Very light blue background */
            border-color: #3182ce; /* Blue border */
        }

        .stat-value {
            font-size: 2.1rem; /* Large value font size */
            font-weight: 700;
            margin-bottom: 8px;
            color: #2d3748; /* Dark text for values */
        }

        .stat-label {
            color: #64748b; /* Softer grey for labels */
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        /* Footer Section */
        .footer {
            background: #e2e8f0; /* Light grey footer background */
            padding: 30px 40px;
            text-align: center;
            color: #64748b; /* Softer grey text */
            border-top: 1px solid #cbd5e1; /* Light border at the top */
            font-size: 0.9rem;
        }

        /* Print-specific adjustments */
        @media print {
            body {
                background: white; /* Ensure white background for actual print */
                padding: 0;
                color: #2d3748; /* Dark text for readability on paper */
            }

            .report-container {
                box-shadow: none; /* Remove shadows for print */
                border-radius: 0;
                border: none; /* No border for the main container in print */
            }

            .header {
                background-color: #2c5282 !important; /* Ensure blue background prints */
                -webkit-print-color-adjust: exact; /* Crucial for background colors to print */
                color: white !important;
                border-bottom-color: rgba(0, 0, 0, 0.05) !important;
            }

            .user-info div {
                background: none !important; /* No background for print */
                border: none !important;
                color: white !important; /* Keep white text */
            }

            .content {
                padding: 25px 30px; /* Slightly reduced padding for print */
                background: white; /* White background for content in print */
            }

            .section-title {
                color: #2d3748 !important; /* Dark text for titles in print */
            }

            .section-title::before {
                background-color: #3182ce !important;
                -webkit-print-color-adjust: exact;
            }

            table {
                box-shadow: none; /* No table shadows in print */
                border: 1px solid #e2e8f0; /* Light border for tables in print */
                background: white; /* White background for tables in print */
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

            .budget-amount, .expense-amount, .total-budget, .total-expenses, .remaining-balance, .remaining-balance.negative {
                -webkit-print-color-adjust: exact; /* Ensure these colors print */
            }

            .summary-table {
                background: #f8fafc !important; /* Light background for print */
                border-color: #cbd5e1 !important;
            }

            .summary-table th {
                background-color: #2c5282 !important;
                -webkit-print-color-adjust: exact;
                color: white !important;
            }

            .summary-table td {
                color: #2d3748 !important; /* Dark text for summary amounts */
            }

            .category-tag {
                background: #ebf8ff !important; /* Light background for print */
                color: #2b6cb0 !important; /* Dark blue text */
                -webkit-print-color-adjust: exact;
                border-color: #bee3f8 !important;
            }

            .stats-grid {
                page-break-inside: avoid; /* Keep stats together */
            }

            .stat-card {
                box-shadow: none; /* No shadow for print */
                border-left-width: 3px; /* Slightly thinner border for print */
                background: white !important; /* White background for cards in print */
                border-color: #e2e8f0 !important; /* Default light border for print */
            }
            .stat-card.budget {
                border-color: #38a169 !important; /* Green border for print */
                background-color: #f0fff4 !important; /* Light green background for print */
            }
            .stat-card.expenses {
                border-color: #e53e3e !important; /* Red border for print */
                background-color: #fff5f5 !important; /* Light red background for print */
            }
            .stat-card.balance {
                border-color: #3182ce !important; /* Blue border for print */
                background-color: #ebf8ff !important; /* Light blue background for print */
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
