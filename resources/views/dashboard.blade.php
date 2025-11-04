@extends('layouts.app')

@section('title', 'Budget Dashboard')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
      --bg-primary: #f8fafc;
      --bg-secondary: #ffffff;
      --bg-tertiary: #f1f5f9;
      --text-primary: #0f172a;
      --text-secondary: #64748b;
      --accent-blue: #3b82f6;
      --accent-purple: #8b5cf6;
      --accent-pink: #ec4899;
      --accent-green: #10b981;
      --accent-orange: #f59e0b;
      --accent-red: #ef4444;
      --accent-cyan: #06b6d4;
      --accent-indigo: #6366f1;
      --border: #e2e8f0;
      --shadow: 0 1px 3px rgba(0,0,0,0.1);
      --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
      line-height: 1.6;
    }
    
    .dashboard-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }
    
    /* Header */
    .page-header {
      text-align: center;
      margin-bottom: 3rem;
    }
    
    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      margin-bottom: 0.5rem;
      background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .page-subtitle {
      color: var(--text-secondary);
      font-size: 1rem;
    }
    
    /* Filter Bar */
    .filter-bar {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      display: flex;
      gap: 1rem;
      align-items: end;
      box-shadow: var(--shadow);
    }
    
    .filter-group {
      flex: 1;
    }
    
    .filter-label {
      display: block;
      color: var(--text-secondary);
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
    
    .filter-input {
      width: 100%;
      background: var(--bg-tertiary);
      border: 1px solid var(--border);
      color: var(--text-primary);
      padding: 0.75rem 1rem;
      border-radius: 10px;
      font-size: 0.95rem;
      transition: all 0.2s;
    }
    
    .filter-input:focus {
      outline: none;
      border-color: var(--accent-blue);
      background: rgba(59, 130, 246, 0.05);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-filter {
      background: var(--accent-blue);
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-filter:hover {
      background: #2563eb;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    /* Quick Stats Grid */
    .quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.5rem;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--color-start), var(--color-end));
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }
    
    .stat-card:hover::before {
      opacity: 1;
    }
    
    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    
    .stat-label {
      color: var(--text-secondary);
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      color: white;
    }
    
    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .stat-change {
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
      color: var(--text-secondary);
    }
    
    .change-positive { color: var(--accent-green); }
    .change-negative { color: var(--accent-red); }
    
    /* Insights Section */
    .insights-panel {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow);
    }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .insight-item {
      background: var(--bg-tertiary);
      padding: 1rem 1.25rem;
      border-radius: 12px;
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: all 0.2s;
      border: 1px solid var(--border);
    }
    
    .insight-item:hover {
      background: #e2e8f0;
      transform: translateX(4px);
    }
    
    .insight-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    
    .insight-text {
      flex: 1;
      font-size: 0.95rem;
      color: var(--text-primary);
    }
    
    .insight-value {
      font-weight: 600;
      color: var(--accent-blue);
    }
    
    /* Chart Cards */
    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .chart-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: var(--shadow);
    }
    
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .chart-title {
      font-size: 1.125rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .chart-container {
      background: var(--bg-tertiary);
      border-radius: 12px;
      padding: 1.5rem;
      height: 350px;
    }
    
    /* Spending Breakdown */
    .spending-list {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    
    .spending-item {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .spending-label {
      min-width: 140px;
      font-size: 0.9rem;
      color: var(--text-secondary);
      font-weight: 500;
    }
    
    .spending-bar {
      flex: 1;
      height: 40px;
      background: var(--bg-tertiary);
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      border: 1px solid var(--border);
    }
    
    .spending-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--color-start), var(--color-end));
      display: flex;
      align-items: center;
      padding: 0 1rem;
      font-size: 0.85rem;
      font-weight: 600;
      transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      color: white;
    }
    
    .spending-amount {
      min-width: 100px;
      text-align: right;
      font-weight: 600;
      font-size: 0.95rem;
    }
    
    /* Day of Week Cards */
    .dow-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .dow-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.25rem;
      text-align: center;
      transition: all 0.2s;
      box-shadow: var(--shadow);
    }
    
    .dow-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    
    .dow-name {
      font-size: 0.875rem;
      color: var(--text-secondary);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .dow-amount {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
    }
    
    .dow-count {
      font-size: 0.75rem;
      color: var(--text-secondary);
      margin-top: 0.25rem;
    }
    
    /* Alert Box */
    .alert-warning {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 16px;
      padding: 1.25rem;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: var(--shadow);
    }
    
    .alert-icon {
      width: 48px;
      height: 48px;
      background: var(--accent-red);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      flex-shrink: 0;
      color: white;
    }
    
    .alert-content strong {
      display: block;
      margin-bottom: 0.25rem;
      font-size: 1.05rem;
      color: var(--text-primary);
    }
    
    .alert-content {
      color: var(--text-secondary);
      font-size: 0.95rem;
    }
    
    /* Transactions Table */
    .transactions-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: var(--shadow);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .chart-grid {
        grid-template-columns: 1fr;
      }
      
      .filter-bar {
        flex-direction: column;
      }
    
      .dow-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      }
    }
    </style>

<div class="dashboard-container">
  <!-- Header -->
  <div class="page-header">
    <h1 class="page-title">Your Money Detective</h1>
    <p class="page-subtitle">Track your spending and master your budget</p>
  </div>

  <!-- Alert for Overspending -->
  @if($remainingBudget < 0)
  <div class="alert-warning">
    <div class="alert-icon">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="alert-content">
      <strong>Budget Alert!</strong>
      You've exceeded your monthly budget by M{{ number_format(abs($remainingBudget), 2) }}
    </div>
  </div>
  @endif

  <!-- Filter -->
  <form action="{{ route('dashboard.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
      <label class="filter-label">
        <i class="fas fa-calendar-alt"></i> Select Month
      </label>
      <input type="month" name="month" value="{{ old('month', $selectedMonth) }}" class="filter-input">
    </div>
    <button type="submit" class="btn-filter">
      <i class="fas fa-sync-alt"></i> Update
    </button>
  </form>

  <!-- Quick Stats -->
  <div class="quick-stats">
    <div class="stat-card" style="--color-start: #ef4444; --color-end: #dc2626;">
      <div class="stat-header">
        <span class="stat-label">Total Spent</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
          <i class="fas fa-arrow-down"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format($totalExpenses, 2) }}</div>
      <div class="stat-change {{ $expensesPercentageChange >= 0 ? 'change-negative' : 'change-positive' }}">
        <i class="fas fa-arrow-{{ $expensesPercentageChange >= 0 ? 'up' : 'down' }}"></i>
        {{ number_format(abs($expensesPercentageChange), 1) }}% vs last month
      </div>
    </div>

    @php
      $topExpense = $topExpenses->first();
    @endphp
    <div class="stat-card" style="--color-start: #8b5cf6; --color-end: #7c3aed;">
      <div class="stat-header">
        <span class="stat-label">Biggest Expense</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
          <i class="fas fa-chart-pie"></i>
        </div>
      </div>
      <div class="stat-value" style="font-size: 1.25rem;">
        {{ $topExpense['description'] ?? 'N/A' }}
      </div>
      <div class="stat-change">
        M{{ number_format($topExpense['total_amount'] ?? 0, 2) }}
      </div>
    </div>

    <div class="stat-card" style="--color-start: #10b981; --color-end: #059669;">
      <div class="stat-header">
        <span class="stat-label">Remaining Budget</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
          <i class="fas fa-wallet"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format($remainingBudget, 2) }}</div>
      <div class="stat-change {{ $remainingBudget >= 0 ? 'change-positive' : 'change-negative' }}">
        <i class="fas fa-{{ $remainingBudget >= 0 ? 'check-circle' : 'exclamation-triangle' }}"></i>
        {{ $remainingBudget >= 0 ? 'On track' : 'Over budget' }}
      </div>
    </div>

    <div class="stat-card" style="--color-start: #3b82f6; --color-end: #2563eb;">
      <div class="stat-header">
        <span class="stat-label">Daily Average</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
          <i class="fas fa-calendar-day"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format($totalExpenses / max(date('d'), 1), 2) }}</div>
      <div class="stat-change">
        Per day this month
      </div>
    </div>
  </div>

  <!-- Key Insights -->
  <div class="insights-panel">
    <h3 class="section-title">
      <i class="fas fa-lightbulb" style="color: var(--accent-orange);"></i>
      Key Insights
    </h3>
    
    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(59, 130, 246, 0.1);">
        <i class="fas fa-receipt" style="color: var(--accent-blue);"></i>
      </div>
      <div class="insight-text">
        You spent <span class="insight-value">M{{ number_format($totalExpenses, 2) }}</span> over <span class="insight-value">{{ $recentTransactions->count() }}</span> transactions
      </div>
    </div>

    @if ($topExpenses->isNotEmpty())
    @php
      $topExpense = $topExpenses->first();
    @endphp
    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(139, 92, 246, 0.1);">
        <i class="fas fa-star" style="color: var(--accent-purple);"></i>
      </div>
      <div class="insight-text">
        Your biggest expense was 
        <span class="insight-value">{{ $topExpense['description'] }}</span> 
        (M{{ number_format($topExpense['total_amount'], 2) }})
      </div>
    </div>
    @else
    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(239, 68, 68, 0.1);">
        <i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>
      </div>
      <div class="insight-text">
        No expenses recorded yet.
      </div>
    </div>
    @endif

    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(16, 185, 129, 0.1);">
        <i class="fas fa-chart-line" style="color: var(--accent-green);"></i>
      </div>
      <div class="insight-text">
        You spend an average of <span class="insight-value">M{{ number_format($totalExpenses / max(date('d'), 1), 2) }}</span> per day
      </div>
    </div>

    @if($remainingBudget < 0)
    <div class="insight-item" style="border-color: rgba(239, 68, 68, 0.3);">
      <div class="insight-icon" style="background: rgba(239, 68, 68, 0.1);">
        <i class="fas fa-exclamation-triangle" style="color: var(--accent-red);"></i>
      </div>
      <div class="insight-text">
        You need to reduce spending by <span class="insight-value" style="color: var(--accent-red);">M{{ number_format(abs($remainingBudget), 2) }}</span> to stay on budget
      </div>
    </div>
    @endif
  </div>

    <!-- Day of Week Spending -->
    @php
    $dayOfWeekSpending = $recentTransactions->where('type', 'Expense')->groupBy(function($transaction) {
      return \Carbon\Carbon::parse($transaction->date)->format('l');
    })->map(function($transactions) {
      return [
        'total' => $transactions->sum('amount'),
        'count' => $transactions->count(),
        'average' => $transactions->avg('amount')
      ];
    });
    
    $daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  @endphp

  <div class="chart-card" style="margin-bottom: 2rem;">
    <div class="chart-header">
      <h3 class="chart-title">
        <i class="fas fa-calendar-week" style="color: var(--accent-indigo);"></i>
        Spending by Day of Week
      </h3>
    </div>
    <div class="dow-grid">
      @foreach($daysOrder as $day)
        @php
          $dayData = $dayOfWeekSpending->get($day, ['total' => 0, 'count' => 0]);
        @endphp
        <div class="dow-card">
          <div class="dow-name">{{ substr($day, 0, 3) }}</div>
          <div class="dow-amount">M{{ number_format($dayData['total'], 0) }}</div>
          <div class="dow-count">{{ $dayData['count'] }} transactions</div>
        </div>
      @endforeach
    </div>
  </div>



  <!-- Charts -->
  <div class="chart-grid">
      <!-- Charts -->
      <div class="chart-card">
        <div class="chart-header">
          <h3 class="chart-title">
            <i class="fas fa-chart-line" style="color: var(--accent-cyan);"></i>
            Daily Spending Trend
          </h3>
        </div>
        <div class="chart-container">
          <canvas id="dailyTrendChart"></canvas>
        </div>
      </div>
    <div class="chart-card">
      <div class="chart-header">
        <h3 class="chart-title">
          <i class="fas fa-chart-bar" style="color: var(--accent-blue);"></i>
          Expense Overview
        </h3>
      </div>
      <div class="chart-container">
        <canvas id="expenseChart"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <div class="chart-header">
        <h3 class="chart-title">
          <i class="fas fa-chart-pie" style="color: var(--accent-purple);"></i>
          Budget Status
        </h3>
      </div>
      <div class="chart-container">
        <canvas id="budgetRingChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Spending Breakdown -->
  <div class="chart-card" style="margin-bottom: 2rem;">
    <div class="chart-header">
      <h3 class="chart-title">
        <i class="fas fa-layer-group" style="color: var(--accent-green);"></i>
        Where Your Money Goes
      </h3>
    </div>
    <div class="spending-list">
      @foreach($labels as $index => $label)
        @php
          $amount = $data[$index] ?? 0;
          $percentage = $totalExpenses > 0 ? ($amount / $totalExpenses) * 100 : 0;
          $colors = [
            ['#ef4444', '#dc2626'],
            ['#3b82f6', '#2563eb'],
            ['#10b981', '#059669'],
            ['#f59e0b', '#d97706'],
            ['#8b5cf6', '#7c3aed'],
            ['#ec4899', '#db2777']
          ];
          $colorSet = $colors[$index % count($colors)];
        @endphp
        <div class="spending-item">
          <div class="spending-label">{{ $label }}</div>
          <div class="spending-bar">
            <div class="spending-fill" style="width: {{ $percentage }}%; --color-start: {{ $colorSet[0] }}; --color-end: {{ $colorSet[1] }}; background: linear-gradient(90deg, {{ $colorSet[0] }}, {{ $colorSet[1] }});">
              {{ number_format($percentage, 1) }}%
            </div>
          </div>
          <div class="spending-amount">M{{ number_format($amount, 2) }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- Top Spending Destinations -->
  <div class="chart-card" style="margin-bottom: 2rem;">
    <div class="chart-header">
      <h3 class="chart-title">
        <i class="fas fa-fire" style="color: var(--accent-red);"></i>
        Top Spending Destinations
      </h3>
    </div>
    <div class="spending-list">
      @foreach($topExpenses->take(10) as $index => $expense)
        @php
          $percentage = $totalExpenses > 0 ? ($expense['total_amount'] / $totalExpenses) * 100 : 0;
        @endphp
        <div class="spending-item">
          <div class="spending-label">{{ $expense['description'] }}</div>
          <div class="spending-bar">
            <div class="spending-fill" style="width: {{ $percentage }}%; --color-start: #ef4444; --color-end: #dc2626; background: linear-gradient(90deg, #ef4444, #dc2626);">
              {{ number_format($percentage, 1) }}%
            </div>
          </div>
          <div class="spending-amount">M{{ number_format($expense['total_amount'], 2) }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- Transactions Table -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Transactions</h5>
    </div>
    <div class="card-body">
        <table id="transactionsTable" class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount (M)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentTransactions as $transaction)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>
                            <span class="badge {{ $transaction->type === 'Income' ? 'bg-success' : 'bg-danger' }}">
                                {{ $transaction->type }}
                            </span>
                        </td>
                        <td>{{ $transaction->category->name ?? 'N/A' }}</td>
                        <td>{{ number_format($transaction->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  </div>

</div>

@endsection

@section('scripts')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Export Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'All Transactions',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'csvHtml5',
                title: 'All Transactions',
                className: 'btn btn-primary btn-sm'
            },
            {
                extend: 'print',
                className: 'btn btn-secondary btn-sm'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
    });
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
// Chart configurations
const chartDefaults = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { color: '#64748b', font: { size: 12, weight: '500' } }
    },
    tooltip: {
      backgroundColor: '#ffffff',
      titleColor: '#0f172a',
      bodyColor: '#64748b',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: 12,
      displayColors: true
    }
  }
};

// 1. Expense Chart
new Chart(document.getElementById('expenseChart'), {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [
      {
        label: 'Expenses',
        data: @json($data),
        backgroundColor: '#ef4444',
        borderRadius: 8,
        borderSkipped: false
      },
      {
        label: 'Budget',
        data: @json($budgetsData),
        backgroundColor: '#3b82f6',
        borderRadius: 8,
        borderSkipped: false
      }
    ]
  },
  options: {
    ...chartDefaults,
    scales: {
      x: { 
        grid: { display: false, color: '#e2e8f0' },
        ticks: { color: '#64748b', font: { weight: '500' } }
      },
      y: {
        beginAtZero: true,
        grid: { color: '#e2e8f0' },
        ticks: { 
          color: '#64748b',
          font: { weight: '500' },
          callback: (value) => 'M' + value 
        }
      }
    }
  }
});

// 2. Budget Ring Chart
new Chart(document.getElementById('budgetRingChart'), {
  type: 'doughnut',
  data: {
    labels: ['Spent', 'Remaining'],
    datasets: [{
      data: [{{ $totalExpenses }}, {{ max(0, $monthlyBudget - $totalExpenses) }}],
      backgroundColor: ['#ef4444', '#3b82f6'],
      borderWidth: 0
    }]
  },
  options: {
    ...chartDefaults,
    cutout: '70%',
    plugins: {
      ...chartDefaults.plugins,
      tooltip: {
        ...chartDefaults.plugins.tooltip,
        callbacks: {
          label: (context) => context.label + ': M' + context.parsed.toFixed(2)
        }
      }
    }
  }
});
</script>
@endsection