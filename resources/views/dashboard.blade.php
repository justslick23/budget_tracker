@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Budget Tracker</strong> Dashboard</h1>

        <div class="row">
        <form action="{{ route('dashboard.filter') }}" method="GET" class="mb-4">
    <label for="month">Select Month:</label>
    <input type="month" id="month" name="month" class="form-control" value="{{ request('month', \Carbon\Carbon::now()->format('Y-m')) }}">
    <button type="submit" class="btn btn-primary mt-2">Filter</button>
</form>

    <!-- Total Income Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow border-0 rounded">
            <div class="card-body d-flex flex-column" style="background-color: #28a745; color: white;">
                <h5 class="card-title">Total Income</h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-dollar-sign text-success" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 class="ml-10 mb-0" style="margin-left: 4%; color: white;"><strong>M{{ number_format($totalIncome, 2) }}</strong></h2>
                </div>
                <p class="text-white-50">+{{ number_format($incomePercentageChange, 2) }}% since last month</p>
            </div>
        </div>
    </div>

    <!-- Total Expenses Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow border-0 rounded">
            <div class="card-body d-flex flex-column" style="background-color: #d50032; color: white;">
                <h5 class="card-title">Total Expenses</h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-credit-card text-danger" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 class="ml-4 mb-0" style="margin-left: 4%; color: white;"><strong>M{{ number_format($totalExpenses, 2) }}</strong></h2>
                </div>
                <p class="text-white-50">-{{ number_format($expensesPercentageChange, 2) }}% since last month</p>
            </div>
        </div>
    </div>

  

    <!-- Monthly Budget Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow border-0 rounded">
            <div class="card-body d-flex flex-column" style="background-color: #0077cc; color: white;">
                <h5 class="card-title">Monthly Budget</h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fas fa-calendar-alt text-info" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 class="ml-4 mb-0" style="margin-left: 4%; color: white;"><strong>M{{ number_format($monthlyBudget, 2) }}</strong></h2>
                </div>
                <p class="text-white-50">-5% since last month</p>
            </div>
        </div>
    </div>

    <!-- Remaining Budget Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100 shadow border-0 rounded">
            <div class="card-body d-flex flex-column" style="background-color: #6f42c1; color: white;"> <!-- Purple background -->
                <h5 class="card-title">Remaining Budget</h5>
                <div class="d-flex align-items-center mb-2">
                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;"> <!-- Smaller icon container -->
                    <i class="fas fa-money-bill-wave text-purple" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 class="ml-4 mb-0" style="margin-left: 4%; color: white;"><strong>M{{ number_format($remainingBudget, 2) }}</strong></h2> <!-- Shifted amount -->
                </div>
                <p class="text-white-50">{{ $remainingBudget < 0 ? 'Overspent' : 'Remaining' }} this month</p>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s ease;
        border-radius: 20px !important;
    }

    .card:hover {
        transform: translateY(-5px);
    }
</style>

        <div class="row">
            <div class="col-12 col-lg-7 mb-4">
                <div class="card flex-fill w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Expense Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5 mb-4">
                <div class="card flex-fill w-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Budget Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="budgetStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card flex-fill">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                    <table class="table table-hover my-0" id="transactionsTable">
    <thead>
        <tr>
            <th>Description</th>
            <th>Transaction Type</th> <!-- New Column for Transaction Type -->
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($recentTransactions as $transaction)
            <tr>
                <td>{{ $transaction->description ?? $transaction->source }}</td>
                <td>{{ $transaction->type }}</td> <!-- Display Income or Expense -->
                <td>
                    @if ($transaction->type == 'Expense')
                        <span class="text-danger">
                            M{{ number_format(abs($transaction->amount), 2) }} 
                            <i class="fas fa-arrow-down"></i> <!-- Down arrow for expense -->
                        </span>
                    @else
                        <span class="text-success">
                            M{{ number_format($transaction->amount, 2) }} 
                            <i class="fas fa-arrow-up"></i> <!-- Up arrow for income -->
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
</main>
@endsection


@section('scripts')
<script>
    // Initialize the Expense Overview Chart
     // Initialize the Expense Overview Chart
     const ctxExpense = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(ctxExpense, {
        type: 'bar',
        data: {
            labels: [
                @foreach($labels as $label)
                    '{{ $label }}',
                @endforeach
            ],
            datasets: [{
                label: 'Expenses',
                data: [
                    @foreach($data as $amount)
                        {{ $amount }},
                    @endforeach
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
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

    // Initialize the Budget Status Chart
    const ctxBudget = document.getElementById('budgetStatusChart').getContext('2d');
    const budgetStatusChart = new Chart(ctxBudget, {
        type: 'pie',
        data: {
            labels: ['Spent', 'Remaining'],
            datasets: [{
                label: 'Budget Status',
                data: [ {{ $totalExpenses }}, {{ $monthlyBudget - $totalExpenses }}],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + ' M' + tooltipItem.raw ; // Format tooltip to show dollar amount
                        }
                    }
                }
            }
        }
    });
</script>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            // You can customize the DataTable options here
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[3, "desc"]], // Order by date column
            "language": {
                "emptyTable": "No transactions available"
            }
        });
    });
</script>
@endsection
