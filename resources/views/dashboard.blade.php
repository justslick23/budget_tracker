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
</div>
@endsection

@section('scripts')
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
    // Budget Status Chart
    const ctxBudget = document.getElementById('budgetStatusChart').getContext('2d');
    const budgetStatusChart = new Chart(ctxBudget, {
        type: 'pie',
        data: {
            labels: ['Remaining', 'Spent'],
            datasets: [{
                data: [{{ $remainingBudget }}, {{ $totalExpenses }}],
                backgroundColor: ['#36A2EB', '#FF6384'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            lengthMenu: [5, 10, 25, 50], // Customize the number of rows displayed per page
            columnDefs: [
                { orderable: false, targets: [0, 2] } // Disable ordering on specific columns (e.g., Description, Amount)
            ],
            language: {
                search: "Search by keyword:",
                lengthMenu: "Show _MENU_ entries per page",
                zeroRecords: "No transactions found",
                info: "Showing page _PAGE_ of _PAGES_",
                infoEmpty: "No entries available",
                infoFiltered: "(filtered from _MAX_ total entries)"
            }
        });
    });
</script>
@endsection