@extends('layouts.app')

@section('title', 'Budget Dashboard')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
      --bg-primary: #f8fafc;
      --bg-secondary: #ffffff;
      --bg-tertiary: #f1f5f9;
      --bg-card: #ffffff;
      --text-primary: #0f172a;
      --text-secondary: #475569;
      --text-muted: #94a3b8;
      --accent-blue: #3b82f6;
      --accent-purple: #a78bfa;
      --accent-pink: #ec4899;
      --accent-green: #10b981;
      --accent-orange: #f59e0b;
      --accent-red: #ef4444;
      --accent-cyan: #06b6d4;
      --accent-indigo: #6366f1;
      --border: #e2e8f0;
      --glow-blue: rgba(59, 130, 246, 0.2);
      --glow-purple: rgba(167, 139, 250, 0.2);
      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      line-height: 1.6;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Animated Background */
    body::before {
      content: '';
      position: fixed;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: 
        radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(167, 139, 250, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.03) 0%, transparent 50%);
      animation: backgroundShift 20s ease infinite;
      z-index: 0;
      pointer-events: none;
    }
    
    @keyframes backgroundShift {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(-5%, -5%); }
    }
    
    .dashboard-container {
      max-width: 1600px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
      position: relative;
      z-index: 1;
    }
    
    /* Header with Glassmorphism */
    .page-header {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 3rem 2.5rem;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-lg);
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple), var(--accent-pink));
    }
    
    .page-title {
      font-size: 3rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      margin-bottom: 0.75rem;
      background: linear-gradient(135deg, #0f172a 0%, #475569 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
    }
    
    .page-subtitle {
      color: var(--text-secondary);
      font-size: 1.125rem;
      font-weight: 400;
    }
    
    /* Filter Bar */
    .filter-bar {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      display: flex;
      gap: 1rem;
      align-items: end;
      box-shadow: var(--shadow-md);
      transition: all 0.3s ease;
    }
    
    .filter-bar:hover {
      border-color: rgba(59, 130, 246, 0.3);
      box-shadow: 0 0 30px var(--glow-blue);
    }
    
    .filter-group {
      flex: 1;
    }
    
    .filter-label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text-secondary);
      font-size: 0.875rem;
      margin-bottom: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .filter-input {
      width: 100%;
      background: var(--bg-tertiary);
      border: 1px solid var(--border);
      color: var(--text-primary);
      padding: 0.875rem 1.25rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .filter-input:focus {
      outline: none;
      border-color: var(--accent-blue);
      background: rgba(59, 130, 246, 0.05);
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .btn-filter {
      background: linear-gradient(135deg, var(--accent-blue), var(--accent-indigo));
      color: white;
      border: none;
      padding: 0.875rem 2.5rem;
      border-radius: 12px;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .btn-filter::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-filter:hover::before {
      left: 100%;
    }
    
    .btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }
    
    /* Quick Stats Grid */
    .quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 2rem;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--color-start), var(--color-end));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.4s ease;
    }
    
    .stat-card:hover::before {
      transform: scaleX(1);
    }
    
    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
      border-color: rgba(255, 255, 255, 0.1);
    }
    
    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1.5rem;
    }
    
    .stat-label {
      color: var(--text-secondary);
      font-size: 0.875rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }
    
    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 0.75rem;
      letter-spacing: -0.02em;
    }
    
    .stat-change {
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--text-secondary);
      font-weight: 600;
    }
    
    .change-positive { color: var(--accent-green); }
    .change-negative { color: var(--accent-red); }
    
    /* AI Recommendations Section */
    .recommendations-panel {
      background: linear-gradient(135deg, rgba(167, 139, 250, 0.1), rgba(59, 130, 246, 0.1));
      border: 1px solid rgba(167, 139, 250, 0.2);
      border-radius: 24px;
      padding: 2.5rem;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
      box-shadow: 0 0 40px rgba(167, 139, 250, 0.1);
    }
    
    .recommendations-panel::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      position: relative;
      z-index: 1;
    }
    
    .ai-badge {
      background: linear-gradient(135deg, var(--accent-purple), var(--accent-pink));
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      box-shadow: 0 4px 12px rgba(167, 139, 250, 0.4);
    }
    
    .recommendation-card {
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid var(--border);
      padding: 1.5rem;
      border-radius: 16px;
      margin-bottom: 1rem;
      display: flex;
      gap: 1.5rem;
      transition: all 0.3s ease;
      position: relative;
      z-index: 1;
      box-shadow: var(--shadow-sm);
    }
    
    .recommendation-card:hover {
      transform: translateX(8px);
      border-color: rgba(167, 139, 250, 0.4);
      background: rgba(255, 255, 255, 0.9);
      box-shadow: 0 8px 24px rgba(167, 139, 250, 0.15);
    }
    
    .rec-icon {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 1.75rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .rec-content {
      flex: 1;
    }
    
    .rec-title {
      font-size: 1.125rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: var(--text-primary);
    }
    
    .rec-description {
      color: var(--text-secondary);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 0.75rem;
    }
    
    .rec-impact {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(16, 185, 129, 0.1);
      color: var(--accent-green);
      padding: 0.375rem 0.875rem;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 700;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    /* Insights Panel */
    .insights-panel {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2.5rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-md);
    }
    
    .insight-item {
      background: var(--bg-tertiary);
      padding: 1.25rem 1.5rem;
      border-radius: 16px;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 1.25rem;
      transition: all 0.3s ease;
      border: 1px solid var(--border);
    }
    
    .insight-item:hover {
      background: #e2e8f0;
      transform: translateX(8px);
      border-color: rgba(59, 130, 246, 0.3);
    }
    
    .insight-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 1.25rem;
    }
    
    .insight-text {
      flex: 1;
      font-size: 1rem;
      color: var(--text-primary);
      font-weight: 500;
    }
    
    .insight-value {
      font-weight: 700;
      color: var(--accent-blue);
    }
    
    /* Chart Cards */
    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }
    
    .chart-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2rem;
      box-shadow: var(--shadow-md);
      transition: all 0.3s ease;
    }
    
    .chart-card:hover {
      border-color: rgba(59, 130, 246, 0.3);
      box-shadow: 0 0 40px rgba(59, 130, 246, 0.15);
    }
    
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    
    .chart-title {
      font-size: 1.25rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.875rem;
    }
    
    .chart-container {
      background: rgba(241, 245, 249, 0.5);
      border-radius: 16px;
      padding: 1.5rem;
      height: 350px;
    }
    
    /* Spending Breakdown */
    .spending-list {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }
    
    .spending-item {
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }
    
    .spending-label {
      min-width: 160px;
      font-size: 0.95rem;
      color: var(--text-secondary);
      font-weight: 600;
    }
    
    .spending-bar {
      flex: 1;
      height: 48px;
      background: var(--bg-tertiary);
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      border: 1px solid var(--border);
    }
    
    .spending-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--color-start), var(--color-end));
      display: flex;
      align-items: center;
      padding: 0 1.25rem;
      font-size: 0.875rem;
      font-weight: 700;
      transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .spending-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
      to { left: 100%; }
    }
    
    .spending-amount {
      min-width: 120px;
      text-align: right;
      font-weight: 700;
      font-size: 1.125rem;
      color: var(--text-primary);
    }
    
    /* Day of Week Cards */
    .dow-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1rem;
    }
    
    .dow-card {
      background: var(--bg-tertiary);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-sm);
    }
    
    .dow-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(59, 130, 246, 0.3);
      background: rgba(249, 250, 251, 1);
    }
    
    .dow-name {
      font-size: 0.875rem;
      color: var(--text-secondary);
      font-weight: 700;
      margin-bottom: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .dow-amount {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-primary);
      margin-bottom: 0.25rem;
    }
    
    .dow-count {
      font-size: 0.75rem;
      color: var(--text-muted);
      font-weight: 500;
    }
    
    /* Alert Box */
    .alert-warning {
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.1));
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 20px;
      padding: 1.75rem;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      box-shadow: 0 8px 24px rgba(239, 68, 68, 0.2);
    }
    
    .alert-icon {
      width: 56px;
      height: 56px;
      background: var(--accent-red);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      flex-shrink: 0;
      color: white;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    .alert-content strong {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 1.125rem;
      color: var(--text-primary);
      font-weight: 700;
    }
    
    .alert-content {
      color: var(--text-secondary);
      font-size: 1rem;
      font-weight: 500;
    }
    
    /* Transactions Table */
    .transactions-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 2rem;
      box-shadow: var(--shadow-md);
    }
    
    /* DataTables Light Theme */
    .dataTables_wrapper {
      color: var(--text-primary);
    }
    
    table.dataTable {
      background: transparent;
      color: var(--text-primary);
      border-collapse: separate;
      border-spacing: 0;
    }
    
    table.dataTable thead th {
      background: var(--bg-tertiary);
      color: var(--text-secondary);
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      border: none;
      padding: 1rem 1.5rem;
    }
    
    table.dataTable thead th:first-child {
      border-top-left-radius: 12px;
    }
    
    table.dataTable thead th:last-child {
      border-top-right-radius: 12px;
    }
    
    table.dataTable tbody td {
      background: var(--bg-card);
      border: none;
      border-bottom: 1px solid var(--border);
      padding: 1rem 1.5rem;
      color: var(--text-primary);
      font-weight: 500;
    }
    
    table.dataTable tbody tr:hover td {
      background: var(--bg-tertiary);
    }
    
    .dataTables_filter input,
    .dataTables_length select {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      color: var(--text-primary);
      padding: 0.5rem 1rem;
      border-radius: 8px;
    }
    
    .dataTables_info,
    .dataTables_paginate {
      color: var(--text-secondary);
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
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      }
      
      .page-title {
        font-size: 2rem;
      }
      
      .spending-label {
        min-width: 100px;
        font-size: 0.85rem;
      }
      
      .spending-amount {
        min-width: 80px;
        font-size: 0.95rem;
      }
    }
    </style>

<div class="dashboard-container">
  <!-- Header -->
  <div class="page-header">
    <h1 class="page-title">Financial Command Center</h1>
    <p class="page-subtitle">AI-powered insights to optimize your spending</p>
  </div>

  <!-- Alert for Overspending -->
  @if($remainingBudget < 0)
  <div class="alert-warning">
    <div class="alert-icon">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="alert-content">
      <strong>Budget Exceeded</strong>
      You've exceeded your monthly budget by M{{ number_format(abs($remainingBudget), 2) }}. Review AI recommendations below.
    </div>
  </div>
  @endif

  <!-- Filter -->
  <form action="{{ route('dashboard.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
      <label class="filter-label">
        <i class="fas fa-calendar-alt"></i> Time Period
      </label>
      <input type="month" name="month" value="{{ old('month', $selectedMonth) }}" class="filter-input">
    </div>
    <button type="submit" class="btn-filter">
      <i class="fas fa-sync-alt"></i> Refresh Data
    </button>
  </form>

  <!-- Quick Stats -->
  <div class="quick-stats">
    <div class="stat-card" style="--color-start: #ef4444; --color-end: #dc2626;">
      <div class="stat-header">
        <span class="stat-label">Total Spent</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
          <i class="fas fa-arrow-trend-down"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format($totalExpenses, 2) }}</div>
      <div class="stat-change {{ $expensesPercentageChange >= 0 ? 'change-negative' : 'change-positive' }}">
        <i class="fas fa-arrow-{{ $expensesPercentageChange >= 0 ? 'up' : 'down' }}"></i>
        {{ number_format(abs($expensesPercentageChange), 1) }}% from last month
      </div>
    </div>

    @php
      $topExpense = $topExpenses->first();
    @endphp
    <div class="stat-card" style="--color-start: #a78bfa; --color-end: #7c3aed;">
      <div class="stat-header">
        <span class="stat-label">Top Expense</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #a78bfa, #7c3aed);">
          <i class="fas fa-crown"></i>
        </div>
      </div>
      <div class="stat-value" style="font-size: 1.5rem;">
        {{ $topExpense['description'] ?? 'N/A' }}
      </div>
      <div class="stat-change">
        M{{ number_format($topExpense['total_amount'] ?? 0, 2) }} spent here
      </div>
    </div>

    <div class="stat-card" style="--color-start: #10b981; --color-end: #059669;">
      <div class="stat-header">
        <span class="stat-label">Budget Status</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
          <i class="fas fa-{{ $remainingBudget >= 0 ? 'check-circle' : 'exclamation-circle' }}"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format(abs($remainingBudget), 2) }}</div>
      <div class="stat-change {{ $remainingBudget >= 0 ? 'change-positive' : 'change-negative' }}">
        <i class="fas fa-{{ $remainingBudget >= 0 ? 'thumbs-up' : 'warning' }}"></i>
        {{ $remainingBudget >= 0 ? 'Remaining' : 'Over budget' }}
      </div>
    </div>

    <div class="stat-card" style="--color-start: #06b6d4; --color-end: #0891b2;">
      <div class="stat-header">
        <span class="stat-label">Daily Average</span>
        <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
          <i class="fas fa-calendar-day"></i>
        </div>
      </div>
      <div class="stat-value">M{{ number_format($totalExpenses / max(date('d'), 1), 2) }}</div>
      <div class="stat-change">
        Average daily spending
      </div>
    </div>
  </div>

  <!-- AI Recommendations Section -->
  <div class="recommendations-panel">
    <h3 class="section-title">
      <i class="fas fa-brain" style="color: var(--accent-purple);"></i>
      AI-Powered Recommendations
      <span class="ai-badge">Smart Insights</span>
    </h3>
    
    @php
      // Calculate spending patterns for recommendations
      $avgDailySpend = $totalExpenses / max(date('d'), 1);
      $projectedMonthlySpend = $avgDailySpend * 30;
      $daysInMonth = date('t');
      $daysRemaining = $daysInMonth - date('d');
      $budgetPerDay = $daysRemaining > 0 ? $remainingBudget / $daysRemaining : 0;
      
      // Identify high spending categories
      $highestCategory = $topExpenses->first();
      $secondHighestCategory = $topExpenses->skip(1)->first();
      
      // Calculate potential savings
      $potentialSavings = 0;
      if($highestCategory && isset($highestCategory['total_amount'])) {
        $potentialSavings = $highestCategory['total_amount'] * 0.15; // 15% reduction target
      }
    @endphp

    @if($remainingBudget < 0)
    <!-- Over budget scenario -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Immediate Action Required</div>
        <div class="rec-description">
          You've exceeded your budget by M{{ number_format(abs($remainingBudget), 2) }}. Focus on essential spending only for the remainder of the month. Consider postponing non-essential purchases.
        </div>
        <span class="rec-impact">
          <i class="fas fa-shield-alt"></i>
          Critical Priority
        </span>
      </div>
    </div>
    @elseif($remainingBudget > 0 && $budgetPerDay < $avgDailySpend * 1.2)
    <!-- On track but tight -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <i class="fas fa-chart-line"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Stay On Track</div>
        <div class="rec-description">
          Your budget is tight with {{ $daysRemaining }} days remaining. Limit daily spending to M{{ number_format($budgetPerDay, 2) }} to finish the month comfortably within budget.
        </div>
        <span class="rec-impact">
          <i class="fas fa-bullseye"></i>
          Target: M{{ number_format($budgetPerDay, 2) }}/day
        </span>
      </div>
    </div>
    @else
    <!-- Doing well -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
        <i class="fas fa-trophy"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Excellent Budget Management!</div>
        <div class="rec-description">
          You're on track with M{{ number_format($remainingBudget, 2) }} remaining. You can spend up to M{{ number_format($budgetPerDay, 2) }} per day for the rest of the month and stay within budget.
        </div>
        <span class="rec-impact">
          <i class="fas fa-check-circle"></i>
          Great Progress
        </span>
      </div>
    </div>
    @endif

    @if($highestCategory && isset($highestCategory['total_amount']))
    <!-- Category-specific recommendation -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #a78bfa, #7c3aed);">
        <i class="fas fa-lightbulb"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Optimize Your Biggest Expense</div>
        <div class="rec-description">
          {{ $highestCategory['description'] }} is your largest spending category at M{{ number_format($highestCategory['total_amount'], 2) }}. 
          Reducing this by just 15% could save you M{{ number_format($potentialSavings, 2) }} this month.
        </div>
        <span class="rec-impact">
          <i class="fas fa-piggy-bank"></i>
          Save up to M{{ number_format($potentialSavings, 2) }}
        </span>
      </div>
    </div>
    @endif

    @if($expensesPercentageChange > 20)
    <!-- Spending increase warning -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">
        <i class="fas fa-arrow-trend-up"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Spending Trend Alert</div>
        <div class="rec-description">
          Your spending increased by {{ number_format($expensesPercentageChange, 1) }}% compared to last month. Review recent purchases to identify and eliminate unnecessary expenses.
        </div>
        <span class="rec-impact">
          <i class="fas fa-search"></i>
          Review Needed
        </span>
      </div>
    </div>
    @elseif($expensesPercentageChange < -10)
    <!-- Positive trend -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
        <i class="fas fa-trending-down"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Great Spending Reduction!</div>
        <div class="rec-description">
          You've reduced spending by {{ number_format(abs($expensesPercentageChange), 1) }}% from last month. Keep up these habits to build long-term financial health.
        </div>
        <span class="rec-impact">
          <i class="fas fa-star"></i>
          Excellent Progress
        </span>
      </div>
    </div>
    @endif

    @if($secondHighestCategory && isset($secondHighestCategory['total_amount']))
    <!-- Secondary category insight -->
    <div class="recommendation-card">
      <div class="rec-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
        <i class="fas fa-chart-pie"></i>
      </div>
      <div class="rec-content">
        <div class="rec-title">Monitor {{ $secondHighestCategory['description'] }}</div>
        <div class="rec-description">
          This is your second-largest expense at M{{ number_format($secondHighestCategory['total_amount'], 2) }}. Look for alternatives or discounts to optimize this spending category.
        </div>
        <span class="rec-impact">
          <i class="fas fa-percentage"></i>
          Optimization Target
        </span>
      </div>
    </div>
    @endif
  </div>

  <!-- Key Insights -->
  <div class="insights-panel">
    <h3 class="section-title">
      <i class="fas fa-chart-mixed" style="color: var(--accent-cyan);"></i>
      Quick Insights
    </h3>
    
    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(59, 130, 246, 0.15);">
        <i class="fas fa-receipt" style="color: var(--accent-blue);"></i>
      </div>
      <div class="insight-text">
        Total of <span class="insight-value">{{ $recentTransactions->count() }}</span> transactions totaling <span class="insight-value">M{{ number_format($totalExpenses, 2) }}</span>
      </div>
    </div>

    @if ($topExpenses->isNotEmpty())
    @php
      $topExpense = $topExpenses->first();
    @endphp
    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(167, 139, 250, 0.15);">
        <i class="fas fa-star" style="color: var(--accent-purple);"></i>
      </div>
      <div class="insight-text">
        Largest expense category: 
        <span class="insight-value">{{ $topExpense['description'] }}</span> 
        at M{{ number_format($topExpense['total_amount'], 2) }}
      </div>
    </div>
    @endif

    <div class="insight-item">
      <div class="insight-icon" style="background: rgba(16, 185, 129, 0.15);">
        <i class="fas fa-calendar-day" style="color: var(--accent-green);"></i>
      </div>
      <div class="insight-text">
        Daily average spending: <span class="insight-value">M{{ number_format($totalExpenses / max(date('d'), 1), 2) }}</span>
      </div>
    </div>

    @if($projectedMonthlySpend > $monthlyBudget)
    <div class="insight-item" style="border-color: rgba(239, 68, 68, 0.3);">
      <div class="insight-icon" style="background: rgba(239, 68, 68, 0.15);">
        <i class="fas fa-exclamation-triangle" style="color: var(--accent-red);"></i>
      </div>
      <div class="insight-text">
        Projected month-end total: <span class="insight-value" style="color: var(--accent-red);">M{{ number_format($projectedMonthlySpend, 2) }}</span> (exceeds budget)
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
        Weekly Spending Pattern
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
          <div class="dow-count">{{ $dayData['count'] }} txns</div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- Charts -->
  <div class="chart-grid">
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
          Category Overview
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
          Budget Distribution
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
        Category Breakdown
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
            ['#a78bfa', '#7c3aed'],
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
  <div class="transactions-card">
    <div class="chart-header" style="margin-bottom: 1.5rem;">
      <h3 class="chart-title">
        <i class="fas fa-list" style="color: var(--accent-orange);"></i>
        Transaction History
      </h3>
    </div>
    <table id="transactionsTable" class="table table-bordered table-striped table-hover align-middle">
      <thead>
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
// Chart configurations with light theme
const chartDefaults = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { 
        color: '#475569', 
        font: { size: 13, weight: '600', family: 'Inter' },
        padding: 15
      }
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.98)',
      titleColor: '#0f172a',
      bodyColor: '#475569',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: 16,
      displayColors: true,
      cornerRadius: 12,
      titleFont: { size: 14, weight: '700', family: 'Inter' },
      bodyFont: { size: 13, weight: '500', family: 'Inter' }
    }
  }
};

// 1. Daily Trend Chart
@php
  $dailyData = $recentTransactions->where('type', 'Expense')
    ->groupBy(function($transaction) {
      return \Carbon\Carbon::parse($transaction->date)->format('Y-m-d');
    })
    ->map(function($transactions) {
      return $transactions->sum('amount');
    })
    ->sortKeys();
@endphp

new Chart(document.getElementById('dailyTrendChart'), {
  type: 'line',
  data: {
    labels: @json($dailyData->keys()->map(function($date) {
      return \Carbon\Carbon::parse($date)->format('M d');
    })),
    datasets: [{
      label: 'Daily Spending',
      data: @json($dailyData->values()),
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: '#3b82f6',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2
    }]
  },
  options: {
    ...chartDefaults,
    scales: {
      x: { 
        grid: { display: false },
        ticks: { 
          color: '#475569', 
          font: { weight: '600', family: 'Inter', size: 11 }
        }
      },
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(148, 163, 184, 0.15)' },
        ticks: { 
          color: '#475569',
          font: { weight: '600', family: 'Inter', size: 11 },
          callback: (value) => 'M' + value 
        }
      }
    }
  }
});

// 2. Expense Chart
new Chart(document.getElementById('expenseChart'), {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [
      {
        label: 'Expenses',
        data: @json($data),
        backgroundColor: '#ef4444',
        borderRadius: 10,
        borderSkipped: false,
        hoverBackgroundColor: '#dc2626'
      },
      {
        label: 'Budget',
        data: @json($budgetsData),
        backgroundColor: '#3b82f6',
        borderRadius: 10,
        borderSkipped: false,
        hoverBackgroundColor: '#2563eb'
      }
    ]
  },
  options: {
    ...chartDefaults,
    scales: {
      x: { 
        grid: { display: false },
        ticks: { 
          color: '#475569', 
          font: { weight: '600', family: 'Inter', size: 12 }
        }
      },
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(148, 163, 184, 0.15)' },
        ticks: { 
          color: '#475569',
          font: { weight: '600', family: 'Inter', size: 12 },
          callback: (value) => 'M' + value 
        }
      }
    }
  }
});

// 3. Budget Ring Chart
new Chart(document.getElementById('budgetRingChart'), {
  type: 'doughnut',
  data: {
    labels: ['Spent', 'Remaining'],
    datasets: [{
      data: [{{ $totalExpenses }}, {{ max(0, $monthlyBudget - $totalExpenses) }}],
      backgroundColor: ['#ef4444', '#3b82f6'],
      borderWidth: 0,
      hoverOffset: 10
    }]
  },
  options: {
    ...chartDefaults,
    cutout: '75%',
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