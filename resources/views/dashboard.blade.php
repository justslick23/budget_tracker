@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')
<style>
    /* Root CSS Variables for better consistency and easier theming */
   :root {
    /* Colors */
    --primary-gradient-start: #00B4DB; /* Cool teal blue */
    --primary-gradient-end: #0083B0;   /* Deep ocean blue */

    --income-color: #2ECC71;  /* Emerald green */
    --expense-color: #E74C3C; /* Soft red */
    --budget-color: #3498DB;  /* Bright sky blue */
    --remaining-color: #9B59B6; /* Vivid purple */

    /* Lightened versions for gradients */
    --income-color-light: #58D68D;
    --expense-color-light: #EC7063;
    --budget-color-light: #5DADE2;
    --remaining-color-light: #BB8FCE;

    /* Glassmorphism properties */
    --glass-bg: rgba(255, 255, 255, 0.9);
    --glass-border: 1px solid rgba(255, 255, 255, 0.3);
    --glass-shadow-light: 0 10px 30px rgba(0, 0, 0, 0.05);
    --glass-shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.15);
    --dashboard-header-bg: rgba(255, 255, 255, 0.2);
    --dashboard-header-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);

    /* Typography colors */
    --text-dark: #1A1F36;
    --text-muted: #6C757D;
    --text-light-muted: #A0AEC0;

    /* Border & Divider colors */
    --border-divider: rgba(0, 0, 0, 0.08);
    --table-border-bottom: rgba(0, 0, 0, 0.05);

    /* Spacing */
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
}

    body {
  
        background: linear-gradient(135deg, #e0f2f7 0%, #c4e0f0 100%); /* Softer, light background */
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .container-fluid {
        flex-grow: 1;
    }

    .dashboard-header {
        background: var(--dashboard-header-bg);
        backdrop-filter: blur(25px); /* Stronger blur */
        border-radius: 20px;
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        border: var(--glass-border);
        box-shadow: var(--dashboard-header-shadow); /* Added subtle shadow */
        animation: slideInDown 0.8s ease-out;
    }

    .dashboard-title {
        color: var(--text-dark); /* Changed to dark for better contrast on light glass */
        font-weight: 800;
        font-size: 2.8rem; /* Slightly larger */
        text-align: center;
        text-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Softer text shadow */
        margin: 0;
        letter-spacing: -0.5px; /* Tighter letter spacing */
    }

    .stats-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: var(--spacing-lg);
        border: var(--glass-border);
        box-shadow: var(--glass-shadow-light);
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        animation: slideInUp 0.8s ease-out;
        animation-fill-mode: both;
    }

    .stats-card:hover {
        transform: translateY(-10px) scale(1.01); /* Slightly less scale */
        box-shadow: var(--glass-shadow-hover);
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px; /* Slightly thicker line */
        background: linear-gradient(90deg, var(--accent-color), var(--accent-color-light));
        border-radius: 20px 20px 0 0;
    }

    .stats-card.income::before { --accent-color: var(--income-color); --accent-color-light: var(--income-color-light); }
    .stats-card.expense::before { --accent-color: var(--expense-color); --accent-color-light: var(--expense-color-light); }
    .stats-card.budget::before { --accent-color: var(--budget-color); --accent-color-light: var(--budget-color-light); }
    .stats-card.remaining::before { --accent-color: var(--remaining-color); --accent-color-light: var(--remaining-color-light); }

    .stats-icon {
        width: 65px; /* Slightly larger icon */
        height: 65px;
        border-radius: 18px; /* Slightly more rounded */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem; /* Larger icon font */
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
        z-index: 1; /* Ensure icon is above pseudo-element */
    }

    .stats-icon::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, var(--icon-bg), var(--icon-bg-light));
        opacity: 0.12; /* Slightly more visible background */
        border-radius: inherit; /* Inherit border radius */
    }

    .stats-icon.income { --icon-bg: var(--income-color); --icon-bg-light: var(--income-color-light); color: var(--income-color); }
    .stats-icon.expense { --icon-bg: var(--expense-color); --icon-bg-light: var(--expense-color-light); color: var(--expense-color); }
    .stats-icon.budget { --icon-bg: var(--budget-color); --icon-bg-light: var(--budget-color-light); color: var(--budget-color); }
    .stats-icon.remaining { --icon-bg: var(--remaining-color); --icon-bg-light: var(--remaining-color-light); color: var(--remaining-color); }

    .stats-label {
        font-size: 0.9rem; /* Slightly larger */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px; /* More letter spacing */
        color: var(--text-muted);
        margin-bottom: 0.6rem;
    }

    .stats-value {
        font-size: 2.2rem; /* Larger value */
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 0.75rem; /* More space */
    }

    .stats-change {
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.35rem; /* Slightly more gap */
    }

    .stats-change.positive { color: var(--income-color); }
    .stats-change.negative { color: var(--expense-color); }
    .stats-change i { font-size: 0.95rem; } /* Slightly larger icon */


    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: var(--spacing-lg);
        border: var(--glass-border);
        box-shadow: var(--glass-shadow-light);
        animation: slideInUp 0.8s ease-out;
        animation-fill-mode: both;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-8px); /* Slightly more lift */
        box-shadow: var(--glass-shadow-hover);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-divider); /* More visible divider */
    }

    .card-title {
        font-size: 1.35rem; /* Slightly larger */
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.75rem; /* More space between icon and text */
    }
    .card-title i {
        color: var(--primary-gradient-start); /* Consistent icon color */
    }

    .filter-section {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 18px; /* Slightly less rounded than cards */
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        border: var(--glass-border);
        box-shadow: var(--glass-shadow-light);
        animation: slideInUp 0.6s ease-out;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 1px solid rgba(0, 0, 0, 0.15); /* Slightly more defined border */
        border-radius: 12px; /* Softer corners */
        padding: 0.85rem 1.1rem; /* More generous padding */
        font-weight: 500;
        transition: all 0.3s ease;
        background: white;
        color: var(--text-dark);
    }

    .form-control:focus {
        border-color: var(--primary-gradient-start);
        box-shadow: 0 0 0 4px rgba(106, 130, 251, 0.2); /* Stronger focus shadow */
        outline: none;
    }

    .btn {
        border-radius: 12px; /* Matches form controls */
        padding: 0.85rem 2.2rem; /* More generous padding */
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1.05rem; /* Slightly larger text */
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-gradient-start) 0%, var(--primary-gradient-end) 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15); /* Initial shadow */
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(106, 130, 251, 0.4); /* Stronger, colored shadow */
    }

    .table {
        border-radius: 15px;
        overflow: hidden;
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05); /* Softer shadow for tables */
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary-gradient-start) 0%, var(--primary-gradient-end) 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1.1rem 1rem; /* More padding */
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: background-color 0.2s ease; /* Smooth hover for background */
    }

    .table tbody tr:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.02); /* Very subtle alternating row color */
    }

    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.06); /* Slightly darker on hover */
        transform: none; /* No transform on table rows for stability */
        box-shadow: none;
    }

    .table tbody td {
        padding: 1rem;
        border: none;
        border-bottom: 1px solid var(--table-border-bottom);
        color: var(--text-dark); /* Ensure text color is dark */
    }
    .table tbody tr:last-child td {
        border-bottom: none; /* No border on last row */
    }

    .badge {
        padding: 0.6rem 1.1rem; /* More generous padding */
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.7rem; /* Slightly smaller for compactness */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex; /* For icon alignment */
        align-items: center;
        gap: 0.3rem; /* Space between icon and text */
    }

    .badge-success {
        background: linear-gradient(135deg, var(--income-color), var(--income-color-light));
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, var(--expense-color), var(--expense-color-light));
        color: white;
    }

    .badge-primary {
        background: linear-gradient(135deg, var(--budget-color), var(--budget-color-light));
        color: white;
    }

    /* Animations */
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chart-container {
        position: relative;
        width: 100%;
        min-height: 380px; /* Increased height for charts */
        margin: 1rem 0;
        display: flex; /* To center content like loading spinner */
        justify-content: center;
        align-items: center;
    }

    .loading-spinner {
        display: inline-block;
        width: 25px; /* Larger spinner */
        height: 25px;
        border: 4px solid rgba(0, 0, 0, 0.1); /* Lighter, more subtle border */
        border-radius: 50%;
        border-top-color: var(--primary-gradient-start); /* Themed spinner color */
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Staggered animation delays for stats cards */
    .stats-card:nth-child(1) { animation-delay: 0.1s; }
    .stats-card:nth-child(2) { animation-delay: 0.2s; }
    .stats-card:nth-child(3) { animation-delay: 0.3s; }
    .stats-card:nth-child(4) { animation-delay: 0.4s; }

    /* Staggered animation delays for glass cards */
    /* Note: Adjust these if you add/remove cards */
    .filter-section { animation-delay: 0.5s; }
    .glass-card:nth-of-type(1) { animation-delay: 0.6s; } /* Weekly Breakdown */
    .glass-card:nth-of-type(2) { animation-delay: 0.7s; } /* Top 5 Recurring */
    .glass-card:nth-of-type(3) { animation-delay: 0.8s; } /* Expense Overview Chart */
    .glass-card:nth-of-type(4) { animation-delay: 0.9s; } /* Budget Status Chart */
    .glass-card:nth-of-type(5) { animation-delay: 1.0s; } /* NEW: Expense Categories Breakdown Table */
    .glass-card:nth-of-type(6) { animation-delay: 1.1s; } /* Budget vs Expense Chart */
    .glass-card:nth-of-type(7) { animation-delay: 1.2s; } /* Recent Transactions */

</style>


<div class="container-fluid py-4"> <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-chart-line me-3"></i>
            Budget Tracker Dashboard
        </h1>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="stats-card income">
                <div class="stats-icon income">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stats-label">Total Income</div>
                <div class="stats-value">M{{ number_format($totalIncome, 2) }}</div>
                <div class="stats-change {{ $incomePercentageChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-{{ $incomePercentageChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format($incomePercentageChange) }}% since last month
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="stats-card expense">
                <div class="stats-icon expense">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stats-label">Total Expenses</div>
                <div class="stats-value">M{{ number_format($totalExpenses, 2) }}</div>
                <div class="stats-change {{ $expensesPercentageChange >= 0 ? 'negative' : 'positive' }}">
                    <i class="fas fa-{{ $expensesPercentageChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format($expensesPercentageChange) }}% since last month
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="stats-card budget">
                <div class="stats-icon budget">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-label">Monthly Budget</div>
                <div class="stats-value">M{{ number_format($monthlyBudget, 2) }}</div>
                <div class="stats-change {{ $budgetPercentageChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-{{ $budgetPercentageChange >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format($budgetPercentageChange) }}% since last month
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="stats-card remaining">
                <div class="stats-icon remaining">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="stats-label">Remaining Budget</div>
                <div class="stats-value">M{{ number_format($remainingBudget, 2) }}</div>
                <div class="stats-change {{ $remainingBudget >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-{{ $remainingBudget >= 0 ? 'check-circle' : 'exclamation-triangle' }}"></i>
                    {{ $remainingBudget < 0 ? 'Overspent' : 'Remaining' }} this month
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <form action="{{ route('dashboard.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="month" class="form-label">
                    <i class="fas fa-filter me-2"></i>Select Month
                </label>
                <input type="month" id="month" name="month" value="{{ old('month', $selectedMonth) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filter Data
                </button>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-week"></i>
                        Weekly Breakdown ({{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }})
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Total Expenses (M)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weeklyBreakdown as $week)
                                <tr>
                                    <td>{{ $week['week_range'] }}</td>
                                    <td><strong>M{{ number_format($week['total_expense'], 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No weekly expenses found for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-repeat"></i>
                        Top 5 Recurring Expenses
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topExpenses as $expense)
                                <tr>
                                    <td>{{ $expense['description'] }}</td>
                                    <td><strong>M{{ number_format($expense['total_amount'], 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-light-muted mb-3"></i>
                                        <p class="text-muted">No recurring expenses found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Expense Overview
                    </h5>
                </div>
                <div class="chart-container">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Budget Status
                    </h5>
                </div>
                <div class="chart-container">
                    <canvas id="budgetRingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Expense vs Budget Overview
                    </h5>
                    <select id="filter" class="form-control" style="width: auto; min-width: 150px;" onchange="updateChart()">
                        <option value="6" {{ request('filter', 12) == 6 ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="12" {{ request('filter', 12) == 12 ? 'selected' : '' }}>Last 12 Months</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="budgetExpenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-list"></i>
                        Recent Transactions
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Category</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->description ?? $transaction->source }}</td>
                                    <td>
                                        @if ($transaction->type == 'Expense')
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-down"></i>Expense
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-up"></i>Income
                                            </span>
                                        @endif
                                    </td>
                                    <td><strong>M{{ number_format(abs($transaction->amount), 2) }}</strong></td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $transaction->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No recent transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Budget Ring Chart
        const ctxRing = document.getElementById('budgetRingChart').getContext('2d');
        const budgetRingChart = new Chart(ctxRing, {
            type: 'doughnut',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    label: 'Budget vs Expenses',
                    data: [{{ $totalExpenses }}, {{ max(0, $monthlyBudget - $totalExpenses) }}], // Ensure remaining is not negative
                    backgroundColor: ['#FC5C7D', '#6A82FB'], // Pink for spent, blue for remaining
                    borderColor: 'rgba(255, 255, 255, 0.8)', // White border for separation
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allows flexible height
                cutout: '75%', // Modern property for cutoutPercentage
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14,
                                family: 'Inter, sans-serif'
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 16, weight: 'bold', family: 'Inter, sans-serif' },
                        bodyFont: { size: 14, family: 'Inter, sans-serif' },
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem) {
                                let value = parseFloat(tooltipItem.raw).toFixed(2);
                                return tooltipItem.label + ': M' + value;
                            }
                        }
                    }
                }
            }
        });

        // Expense Overview Chart (Bar Chart)
        const ctxExpense = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(ctxExpense, {
            type: 'bar',
            data: {
                labels: @json($labels), // Categories
                datasets: [
                    {
                        label: 'Expenses',
                        data: @json($data), // Expenses dataset
                        backgroundColor: 'rgba(252, 92, 125, 0.8)', /* Matching primary-gradient-end for expenses */
                        borderColor: 'rgba(252, 92, 125, 1)',
                        borderWidth: 1,
                        borderRadius: 8, /* Rounded bars */
                        hoverBackgroundColor: 'rgba(252, 92, 125, 1)'
                    },
                    {
                        label: 'Budget',
                        data: @json($budgetsData), // Budgets dataset
                        backgroundColor: 'rgba(106, 130, 251, 0.6)', /* Matching primary-gradient-start for budget */
                        borderColor: 'rgba(106, 130, 251, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        hoverBackgroundColor: 'rgba(106, 130, 251, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, family: 'Inter, sans-serif' },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 16, weight: 'bold', family: 'Inter, sans-serif' },
                        bodyFont: { size: 14, family: 'Inter, sans-serif' },
                        padding: 12,
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': M' + parseFloat(tooltipItem.raw).toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false // Hide vertical grid lines
                        },
                        ticks: {
                            font: { size: 12, family: 'Inter, sans-serif' },
                            color: '#555'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.08)' // Lighter horizontal grid lines
                        },
                        ticks: {
                            font: { size: 12, family: 'Inter, sans-serif' },
                            color: '#555',
                            callback: function(value) {
                                return 'M' + value; // Add M currency
                            }
                        }
                    }
                }
            }
        });

        // Budget vs Expense Chart (Line Chart)
        const budgetExpenseCanvas = document.getElementById('budgetExpenseChart');

        // --- DEBUGGING LOGS ---
        console.log('Attempting to initialize budgetExpenseChart...');
        console.log('Canvas element:', budgetExpenseCanvas);
        console.log('Months data:', @json($months));
        console.log('Monthly Budgets data:', @json($monthlyBudgets));
        console.log('Monthly Expenses data:', @json($monthlyExpenses));
        // --- END DEBUGGING LOGS ---

        if (budgetExpenseCanvas) { // Only proceed if canvas element is found
            const ctxBudgetExpense = budgetExpenseCanvas.getContext('2d');
            const budgetExpenseChart = new Chart(ctxBudgetExpense, {
                type: 'line',
                data: {
                    labels: @json($months),
                    datasets: [
                        {
                            label: 'Total Budget',
                            data: @json($monthlyBudgets),
                            borderColor: '#6A82FB', /* Blue line for budget */
                            backgroundColor: 'rgba(106, 130, 251, 0.2)', /* Lighter fill */
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4, // Smooth curve
                            pointRadius: 5, /* Larger points */
                            pointBackgroundColor: '#6A82FB',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Total Expenses',
                            data: @json($monthlyExpenses),
                            borderColor: '#FC5C7D', /* Pink line for expenses */
                            backgroundColor: 'rgba(252, 92, 125, 0.2)', /* Lighter fill */
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#FC5C7D',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: { size: 14, family: 'Inter, sans-serif' },
                                color: '#333'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleFont: { size: 16, weight: 'bold', family: 'Inter, sans-serif' },
                            bodyFont: { size: 14, family: 'Inter, sans-serif' },
                            padding: 12,
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.dataset.label + ': M' + parseFloat(tooltipItem.raw).toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 12, family: 'Inter, sans-serif' },
                                color: '#555'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)'
                            },
                            ticks: {
                                font: { size: 12, family: 'Inter, sans-serif' },
                                color: '#555',
                                callback: function(value) {
                                    return 'M' + value;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error("Error: Canvas element with ID 'budgetExpenseChart' not found.");
        }
    });

    // DataTables Initialization
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthChange": true,
            "autoWidth": false,
            "responsive": true,
            "columnDefs": [
                { "width": "25%", "targets": 0 },
                { "width": "15%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "20%", "targets": 3 },
                { "width": "15%", "targets": 4 }
            ],
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "search": "Search:",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });

    // Function to update the chart based on the filter
    function updateChart() {
        let filter = document.getElementById("filter").value;
        window.location.href = `?filter=${filter}`; // Reload with new filter
    }
</script>
@endsection