@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><strong>Budget Tracker</strong> Dashboard</h1>

    <!-- Month Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form action="{{ route('dashboard.filter') }}" method="GET" class="form-inline">
                <div class="form-group">
                    <label for="month" class="mr-2">Select Month:</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ request('month', \Carbon\Carbon::now()->format('Y-m')) }}">
                </div>
                <button type="submit" class="btn btn-primary ml-3">Filter</button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Total Income Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
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
                            <i class="fas fa-credit-card fa-2x text-danger"></i>
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
                            <i class="fas fa-calendar-alt fa-2x text-info"></i>
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
                            <i class="fas fa-money-bill-wave fa-2x text-purple"></i>
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

   <!-- Predictions Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Predicted Budget and Expenses for Next Month</h6>
            </div>
            <div class="card-body">
                @if(!empty($predictionsArray))
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Predicted Amount (M)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($predictionsArray as $category => $amount)
                            <tr>
                                <td>{{ ucfirst($category) }}</td>
                                <td>M{{ number_format((float)$amount, 2) }}</td>
                            </tr>
                            @endforeach
                          
                        </tbody>
                    </table>
                @else
                    <p>No predictions available.</p>
                @endif
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
                                        <td>{{ $transaction->type }}</td>
                                        <td>
                                            @if ($transaction->type == 'Expense')
                                                <span class="text-danger">
                                                    M{{ number_format(abs($transaction->amount), 2) }} 
                                                    <i class="fas fa-arrow-down"></i>
                                                </span>
                                            @else
                                                <span class="text-success">
                                                    M{{ number_format($transaction->amount, 2) }} 
                                                    <i class="fas fa-arrow-up"></i>
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
</div>
@endsection

@section('scripts')
<script>
    // Expense Chart
    const ctxExpense = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(ctxExpense, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Expenses',
                data: @json($data),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Budget Status Chart
    const ctxBudget = document.getElementById('budgetStatusChart').getContext('2d');
    const budgetStatusChart = new Chart(ctxBudget, {
        type: 'pie',
        data: {
            labels: ['Spent', 'Remaining'],
            datasets: [{
                label: 'Budget Status',
                data: [ {{ $totalExpenses }}, {{ $monthlyBudget - $totalExpenses }}],
                backgroundColor: ['#4fd1c5', '#f6ad55'],
                borderColor: ['#4fd1c5', '#f6ad55'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + ' M' + tooltipItem.raw;
                        }
                    }
                }
            }
        }
    });

    // DataTables Initialization
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [[3, "desc"]],
            language: {
                emptyTable: "No transactions available"
            }
        });
    });
</script>
@endsection
