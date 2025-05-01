@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><strong>Budget Tracker</strong> Dashboard</h1>


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
                    <p class="mt-2 mb-0 text-sm text-success">
                        @if ($incomePercentageChange >= 0)
                            +{{ number_format($incomePercentageChange, 2) }}% since last month
                        @else
                            {{ number_format($incomePercentageChange, 2) }}% since last month
                        @endif
                    </p>
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
                    <p class="mt-2 mb-0 text-sm text-danger">
                        @if ($expensesPercentageChange >= 0)
                            +{{ number_format($expensesPercentageChange, 2) }}% since last month
                        @else
                            {{ number_format($expensesPercentageChange, 2) }}% since last month
                        @endif
                    </p>
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
                    <p class="mt-2 mb-0 text-sm text-info">
                        @if ($budgetPercentageChange >= 0)
                            +{{ number_format($budgetPercentageChange, 2) }}% since last month
                        @else
                            {{ number_format($budgetPercentageChange, 2) }}% since last month
                        @endif
                    </p>
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




    <form action="{{ route('dashboard.index') }}" method="GET">
    <div class="form-group">
        <label for="month">Select Month:</label>
        <input type="month" id="month" name="month" value="{{ old('month', $selectedMonth) }}" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

    <!-- Weekly Breakdown Section -->
 

    <!-- Charts Section -->
     <br><br>

     <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Weekly Breakdown ({{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Week</th>
                                    <th>Total Expenses (M)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weeklyBreakdown as $week)
                                    <tr>
                                        <td>{{ $week['week_range'] }}</td>
                                        <td>M{{ number_format($week['total_expense'], 2) }}</td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
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
                <canvas id="budgetRingChart" height="80" width="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expense vs Budget Overview</h6>
                    <br>
                    <select id="filter"  class = "form-control" onchange="updateChart()">
    <option value="6">Last 6 Months</option>
    <option value="12" selected>Last 12 Months</option>
</select>

                </div>
                <div class="card-body">
                <canvas id="budgetExpenseChart"></canvas>
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
                    <table class="table table-hover" id="transactionsTable" style="width: 100%; table-layout: fixed;">
    <thead class="thead-light">
        <tr>
            <th>Description</th>
            <th>Transaction Type</th>
            <th>Amount</th>
            <th>Category</th>
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
</td>            <td>M{{ number_format(abs($transaction->amount), 2) }}</td>
            <td>
    <span class="badge badge-primary">
        {{ $transaction->category->name ?? 'Uncategorized' }}
    </span>
</td>            <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}</td>
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
    const ctxRing = document.getElementById('budgetRingChart').getContext('2d');
    const budgetRingChart = new Chart(ctxRing, {
        type: 'doughnut', // Change to doughnut for a ring chart
        data: {
            labels: ['Spent', 'Remaining'], // Two sections: spent and remaining
            datasets: [{
                label: 'Budget vs Expenses',
                data: [{{ $totalExpenses }}, {{ $monthlyBudget - $totalExpenses }}], // Expenses and remaining budget
                backgroundColor: ['#FF6384', '#36A2EB'], // Red for expenses, Blue for remaining
                hoverOffset: 4 // Slightly increase the distance when hovered
            }]
        },
        options: {
            responsive: true,
            cutoutPercentage: 70, // Adjust to control the thickness of the ring (increase for a thicker ring)
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw + ' LSL'; // Add currency label
                        }
                    }
                }
            }
        }
    });
</script>

<script>
    // Expense Chart
   // Expense and Budget Comparison Chart
const ctxExpense = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctxExpense, {
    type: 'bar',
    data: {
        labels: @json($labels), // Categories
        datasets: [
            {
                label: 'Expenses',
                data: @json($data), // Expenses dataset
                backgroundColor: 'rgba(255, 99, 132, 0.2)', // Light red for expenses
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            },
            {
                label: 'Budget',
                data: @json($budgetsData), // Budgets dataset
                backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue for budget
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }
        ]
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


</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<script>
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
                { "width": "30%", "targets": 0 }, // Adjust column width
                { "width": "15%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "20%", "targets": 3 },
                { "width": "20%", "targets": 4 }
            ]
        });
    });
</script>

<script>
   document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('budgetExpenseChart').getContext('2d');

    const chartData = {
        labels: @json($months),
        datasets: [
            {
                label: 'Total Budget',
                data: @json($monthlyBudgets),
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4 // Smooth curve effect
            },
            {
                label: 'Total Expenses',
                data: @json($monthlyExpenses),
                borderColor: 'red',
                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4 // Smooth curve effect
            }
        ]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

</script>

<script>
    function updateChart() {
    let filter = document.getElementById("filter").value;
    window.location.href = `?filter=${filter}`; // Reload with new filter
}

</script>

@endsection