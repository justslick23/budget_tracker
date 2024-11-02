@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><strong>Budget Tracker</strong> Dashboard</h1>

    <!-- Month Filter -->
 

    <div class="row">
        <!-- Total Income Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="ti-money fa-2x text-success"></i>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Income</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">M{{ number_format($totalIncome, 2) }}</div>
                            <p class="mt-2 mb-0 text-sm text-success">+{{ number_format($incomePercentageChange, 2) }}% since last month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="ti-credit-card fa-2x text-danger"></i>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Expenses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">M{{ number_format($totalExpenses, 2) }}</div>
                            <p class="mt-2 mb-0 text-sm text-danger">-{{ number_format($expensesPercentageChange, 2) }}% since last month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Budget Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="ti-calendar fa-2x text-info"></i>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monthly Budget</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">M{{ number_format($monthlyBudget, 2) }}</div>
                            <p class="mt-2 mb-0 text-sm text-info">-5% since last month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Budget Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-purple shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="ti-wallet fa-2x text-purple"></i>
                        </div>
                        <div class="col">
                            <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">Remaining Budget</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">M{{ number_format($remainingBudget, 2) }}</div>
                            <p class="mt-2 mb-0 text-sm text-purple">{{ $remainingBudget < 0 ? 'Overspent' : 'Remaining' }} this month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expense Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Budget Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="budgetStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="transactionsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Transaction Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->description ?? $transaction->source }}</td>
                                        <td>
                                            @if ($transaction->type == 'Expense')
                                                <span class="badge badge-danger">Expense</span>
                                            @else
                                                <span class="badge badge-success">Income</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transaction->type == 'Expense')
                                                <span class="text-danger">
                                                    M{{ number_format(abs($transaction->amount), 2) }} 
                                                    <i class="ti-arrow-down"></i>
                                                </span>
                                            @else
                                                <span class="text-success">
                                                    M{{ number_format($transaction->amount, 2) }} 
                                                    <i class="ti-arrow-up"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report Section -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Report for {{ $year }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Income</th>
                                    <th>Expenses</th>
                                    <th>Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($months as $index => $month)
                                    <tr>
                                        <td>{{ $month }}</td>
                                        <td>M{{ number_format($incomeData[$index], 2) }}</td>
                                        <td>M{{ number_format($expensesData[$index], 2) }}</td>
                                        <td>M{{ number_format($budgetsData[$index], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Expense Chart
// Budget vs Expense Chart
const ctxBudgetExpense = document.getElementById('budgetExpenseChart').getContext('2d');
const budgetExpenseChart = new Chart(ctxBudgetExpense, {
    type: 'bar',
    data: {
        labels: @json($labels), // Category names
        datasets: [
            {
                label: 'Budget',
                data: @json($budgetsData), // Budget amounts
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
            },
            {
                label: 'Expenses',
                data: @json($expensesData), // Expense amounts
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
            },
        ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Amount',
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Categories',
                }
            }
        }
    }
});


    // Budget Status Chart
    const ctxBudget = document.getElementById('budgetStatusChart').getContext('2d');
    const budgetStatusChart = new Chart(ctxBudget, {
        type: 'pie',
        data: {
            labels: ['Expenses', 'Remaining Budget'],
            datasets: [{
                data: [{{ $totalExpenses }}, {{ $remainingBudget }}],
                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>
@endsection
