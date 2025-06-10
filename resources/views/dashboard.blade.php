
@extends('layouts.app')

@section('title', 'Budget Tracker Dashboard')

@section('content')

<style>
    /* Modern Budget Tracker Dashboard CSS */
/* Modern Budget Tracker Dashboard CSS */

/* Root Variables */
:root {
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  
  --glass-bg: rgba(255, 255, 255, 0.25);
  --glass-border: rgba(255, 255, 255, 0.18);
  --text-primary: #2d3748;
  --text-secondary: #718096;
  --shadow-light: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
  --shadow-hover: 0 15px 35px rgba(31, 38, 135, 0.2);
  
  --border-radius: 20px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Override body styles for your template */
.content-wrapper {
  background-attachment: fixed;
  min-height: 100vh;
  position: relative;
}

/* Animated Background Particles */
.content-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.2) 2px, transparent 0),
    radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 1px, transparent 0);
  background-size: 50px 50px;
  animation: float 20s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}

/* Ensure content is above background */
.content {
  position: relative;
  z-index: 1;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

/* Font Awesome Icon Fixes */
.fas, .fa {
  font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
  font-weight: 900 !important;
  font-style: normal !important;
  display: inline-block !important;
}

.far {
  font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
  font-weight: 400 !important;
}

/* Ensure icons are visible */
.dashboard-title i,
.stats-icon i,
.card-title i,
.form-label i,
.btn i,
.badge i,
.stats-change i {
  opacity: 1 !important;
  visibility: visible !important;
}

/* Container adjustment for your template */
.container-fluid {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Dashboard Header */
.dashboard-header {
  text-align: center;
  margin-bottom: 3rem;
  animation: slideDown 0.8s ease-out;
}

.dashboard-title {
  font-size: 3rem;
  font-weight: 700;
  background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.8) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.dashboard-title i {
  -webkit-text-fill-color: rgba(255, 255, 255, 0.9);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* Stats Cards */
.stats-card {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-light);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  animation: slideUp 0.6s ease-out;
  animation-fill-mode: both;
}

.stats-card:nth-child(1) { animation-delay: 0.1s; }
.stats-card:nth-child(2) { animation-delay: 0.2s; }
.stats-card:nth-child(3) { animation-delay: 0.3s; }
.stats-card:nth-child(4) { animation-delay: 0.4s; }

.stats-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s;
}

.stats-card:hover {
  transform: translateY(-10px);
  box-shadow: var(--shadow-hover);
}

.stats-card:hover::before {
  left: 100%;
}

.stats-icon {
  width: 60px;
  height: 60px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  font-size: 1.5rem;
  color: white;
  transition: var(--transition);
}

.stats-icon.income {
  background: var(--success-gradient);
}

.stats-icon.expense {
  background: var(--danger-gradient);
}

.stats-icon.budget {
  background: var(--primary-gradient);
}

.stats-icon.remaining {
  background: var(--warning-gradient);
}

.stats-card:hover .stats-icon {
  transform: rotate(360deg) scale(1.1);
}

.stats-label {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.stats-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.stats-change {
  font-size: 0.85rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stats-change.positive {
  color: #48bb78;
}

.stats-change.negative {
  color: #f56565;
}

/* Glass Cards */
.glass-card {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-light);
  transition: var(--transition);
  animation: slideUp 0.8s ease-out;
  overflow: hidden;
}

.glass-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-hover);
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-title i {
  color: rgba(255, 255, 255, 0.8);
}

/* Filter Section */
.filter-section {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-light);
  animation: slideUp 0.6s ease-out;
}

.form-label {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.form-control {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  color: var(--text-primary);
  font-weight: 500;
  transition: var(--transition);
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
  outline: none;
}

.btn-primary {
  background: var(--primary-gradient);
  border: none;
  border-radius: 12px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  color: white;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.btn-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover::before {
  left: 100%;
}

/* Tables */
.table-responsive {
  padding: 1.5rem;
}

.table {
  color: var(--text-primary);
  border-collapse: separate;
  border-spacing: 0;
}

.table thead th {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  padding: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  position: sticky;
  top: 0;
}

.table tbody tr {
  transition: var(--transition);
}

.table tbody tr:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: scale(1.02);
}

.table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  vertical-align: middle;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-success {
  background: var(--success-gradient);
  color: white;
}

.badge-danger {
  background: var(--danger-gradient);
  color: white;
}

.badge-primary {
  background: var(--primary-gradient);
  color: white;
}

.chart-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #2d3748;
        }

        .data-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .expense-amount {
            color: #fc5c7d;
            font-weight: 600;
        }

        .budget-amount {
            color: #6a82fb;
            font-weight: 600;
        }

/* Animations */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* DataTables Styling */
.dataTables_wrapper {
  color: var(--text-primary);
}

.dataTables_filter input {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  padding: 0.5rem;
  color: var(--text-primary);
  margin-left: 0.5rem;
}

.dataTables_length select {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  padding: 0.25rem;
  color: var(--text-primary);
}

.dataTables_paginate .paginate_button {
  background: rgba(255, 255, 255, 0.2) !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
  border-radius: 8px !important;
  color: var(--text-primary) !important;
  margin: 0 2px;
  transition: var(--transition);
}

.dataTables_paginate .paginate_button:hover {
  background: rgba(255, 255, 255, 0.3) !important;
  transform: translateY(-2px);
}

.dataTables_paginate .paginate_button.current {
  background: var(--primary-gradient) !important;
  color: white !important;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container-fluid {
    padding: 1rem;
  }
  
  .dashboard-title {
    font-size: 2rem;
  }
  
  .stats-card {
    padding: 1.5rem;
  }
  
  .stats-value {
    font-size: 1.5rem;
  }
  
  .card-header {
    padding: 1rem;
    flex-direction: column;
    gap: 1rem;
  }
  
  .chart-container {
    height: 300px;
    padding: 1rem;
  }
}

/* Hover Effects for Interactive Elements */
.stats-card, .glass-card, .btn-primary, .badge {
  cursor: pointer;
}

/* Loading Animation */
.loading {
  position: relative;
  overflow: hidden;
}

.loading::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% { left: -100%; }
  100% { left: 100%; }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

/* Focus States for Accessibility */
.form-control:focus,
.btn-primary:focus,
.stats-card:focus {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
}
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
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stats-label">Total Income</div>
                <div class="stats-value">M{{ number_format($totalIncome, 2) }}</div>
                <div class="stats-change {{ $incomePercentageChange >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-{{ $incomePercentageChange >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                    {{ number_format($incomePercentageChange) }}% since last month
                </div>
            </div>
        </div>
    
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="stats-card expense">
                <div class="stats-icon expense">
                    <i class="fas fa-receipt"></i>
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
                    <i class="fas fa-chart-pie"></i>
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
                    <i class="fas fa-wallet"></i>
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
        <div class="col-12">
            <div class="glass-card">
                <!-- Card Header -->
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt"></i> Summary
                    </h5>
                </div>
    
                <!-- Table Container -->
                <div class="table-container" style="overflow-x: auto;">
                    <table class="data-table table table-striped">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Average</th>
                                <th>vs Average</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            <!-- Table rows will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
                        Top 5 Expenses
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
                                        <p class="text-muted">No top expenses found.</p>
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
                    <div class="chart-wrapper">
                        <canvas id="expenseChart"></canvas>
                    </div>
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
                <div class="chart-container" style="height: 400px;">
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

        const labels = @json($labels); // Categories
        const expenseData = @json($data); // Expenses dataset
        const budgetData = @json($budgetsData); // Budgets dataset

        // Create the chart with your exact configuration
        const ctxExpense = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(ctxExpense, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
            {
                label: 'Expenses',
                data: expenseData,
                backgroundColor: 'rgba(252, 92, 125, 0.8)',
                borderColor: 'rgba(252, 92, 125, 1)',
                borderWidth: 1,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(252, 92, 125, 1)'
            },
            {
                label: 'Budget',
                data: budgetData,
                backgroundColor: 'rgba(106, 130, 251, 0.6)',
                borderColor: 'rgba(106, 130, 251, 1)',
                borderWidth: 1,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(106, 130, 251, 0.8)'
            },
            {
                label: '6-Month Average',
                data: @json($averagesData), // Add this line
                type: 'line',
                borderColor: 'rgba(34, 197, 94, 0.8)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                fill: false
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

        // Populate the table
        function populateTable() {
    const tableBody = document.getElementById('dataTableBody');
    const averageData = @json($averagesData);
    
    labels.forEach((label, index) => {
        const expense = expenseData[index];
        const budget = budgetData[index];
        const average = averageData[index];
        const difference = budget - expense;
        const vsAverage = average > 0 ? ((expense - average) / average * 100).toFixed(1) : 0;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${label}</td>
            <td class="average-amount text-muted">M${average.toFixed(2)}</td>
           
            <td style="color: ${vsAverage >= 0 ? '#ef4444' : '#10b981'}; font-weight: 600;">
                ${vsAverage > 0 ? '+' : ''}${vsAverage}%
            </td>
        `;
        tableBody.appendChild(row);
    });
}

        // Initialize table
        populateTable();

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