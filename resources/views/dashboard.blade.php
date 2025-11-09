@extends('layouts.app')

@section('title', 'AI Budget Dashboard')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        /* Light Backgrounds - Softer tones */
        --bg-primary: #f0f4f8;
        --bg-card: #ffffff;
        
        /* Text Colors */
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        
        /* New Vibrant Color Scheme */
        --accent-primary: #6366f1;      /* Indigo */
        --accent-secondary: #ec4899;    /* Pink */
        --accent-success: #14b8a6;      /* Teal */
        --accent-danger: #f43f5e;       /* Rose */
        --accent-warning: #f59e0b;      /* Amber */
        --accent-info: #0ea5e9;         /* Sky */
        --accent-purple: #a855f7;       /* Purple */
        --accent-emerald: #10b981;      /* Emerald */
        
        /* UI Elements */
        --border: #e2e8f0;
        --shadow: 0 1px 3px rgba(0,0,0,0.08);
        --shadow-lg: 0 10px 25px rgba(0,0,0,0.12);
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-primary);
        color: var(--text-primary);
        line-height: 1.6;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }

    .page-header {
        background: linear-gradient(135deg, #6366f1, #ec4899);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
        color: white;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .page-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }

    /* Month Filter */
    .filter-bar {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        display: flex;
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        flex: 1;
    }

    .filter-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .filter-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
    }

    .btn-filter {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: transform 0.2s;
        border-top: 3px solid;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-card.expense { border-color: var(--accent-danger); }
    .stat-card.income { border-color: var(--accent-success); }
    .stat-card.budget { border-color: var(--accent-primary); }
    .stat-card.savings { border-color: var(--accent-warning); }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
    }

    .change-positive { color: var(--accent-success); }
    .change-negative { color: var(--accent-danger); }

    /* Alert */
    .alert-warning {
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.1), rgba(239, 68, 68, 0.05));
        border: 1px solid rgba(244, 63, 94, 0.3);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .alert-icon {
        width: 48px;
        height: 48px;
        background: var(--accent-danger);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    /* AI INSIGHTS PANEL */
    .ai-panel {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.05), rgba(236, 72, 153, 0.05));
        border: 2px solid rgba(168, 85, 247, 0.2);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .ai-badge {
        background: linear-gradient(135deg, #a855f7, #ec4899);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Predictions Grid */
    .predictions-section {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .predictions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .prediction-item {
        background: var(--bg-primary);
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
    }

    .prediction-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .prediction-value {
        font-size: 1.5rem;
        font-weight: 800;
    }

    .prediction-confidence {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    /* Recommendations */
    .recommendation-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid;
        transition: transform 0.2s;
        box-shadow: var(--shadow);
    }

    .recommendation-card:hover {
        transform: translateX(4px);
    }

    .recommendation-card.priority-high { border-color: var(--accent-danger); }
    .recommendation-card.priority-medium { border-color: var(--accent-warning); }
    .recommendation-card.priority-low { border-color: var(--accent-primary); }

    .rec-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 0.75rem;
    }

    .rec-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
    }

    .priority-high { background: var(--accent-danger); }
    .priority-medium { background: var(--accent-warning); }
    .priority-low { background: var(--accent-primary); }

    .rec-description {
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 0.75rem;
    }

    .rec-footer {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .rec-savings {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(20, 184, 166, 0.1);
        color: var(--accent-success);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 700;
    }

    .rec-difficulty {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-primary);
        color: var(--text-secondary);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .specific-items-list {
        background: var(--bg-primary);
        padding: 0.75rem;
        border-radius: 8px;
        margin-top: 0.75rem;
    }

    .specific-items-list ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .specific-items-list li {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }

    /* Insights List */
    .insights-section {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .insight-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-primary);
        border-radius: 10px;
        margin-bottom: 0.75rem;
    }

    .insight-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Pattern Insights */
    .pattern-insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .pattern-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
    }

    .pattern-card h5 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pattern-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: var(--bg-primary);
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .pattern-item-info {
        flex: 1;
    }

    .pattern-item-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .pattern-item-detail {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .pattern-item-value {
        font-weight: 700;
        color: var(--accent-purple);
    }

    /* Behavioral Insights */
    .behavioral-section {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .behavioral-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .behavioral-column {
        background: var(--bg-primary);
        padding: 1.5rem;
        border-radius: 12px;
    }

    .behavioral-column h6 {
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .behavioral-column ul {
        list-style: none;
        padding: 0;
    }

    .behavioral-column li {
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.875rem;
    }

    .behavioral-column li:last-child {
        border-bottom: none;
    }

    /* Charts */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
    }

    .chart-title {
        font-size: 1.125rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .chart-container {
        height: 300px;
    }

    /* Loading State */
    .ai-loading {
        text-align: center;
        padding: 3rem;
    }

    .spinner {
        animation: spin 1s linear infinite;
        font-size: 3rem;
        color: var(--accent-purple);
        margin-bottom: 1rem;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper {
        font-family: 'Inter', sans-serif;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 0.5rem;
        font-family: 'Inter', sans-serif;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #6366f1, #a855f7) !important;
        color: white !important;
        border: none !important;
    }

    table.dataTable thead th {
        background: var(--bg-primary) !important;
        color: var(--text-secondary) !important;
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        text-transform: uppercase !important;
        padding: 1rem !important;
    }

    table.dataTable tbody tr {
        transition: background 0.2s;
    }

    table.dataTable tbody tr:hover {
        background: var(--bg-primary) !important;
    }

    table.dataTable tbody td {
        padding: 1rem !important;
    }

    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-bar {
            flex-direction: column;
        }

        .pattern-insights-grid {
            grid-template-columns: 1fr;
        }

        .behavioral-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-brain"></i> AI Budget Dashboard
        </h1>
        <p class="page-subtitle">Powered by Gemini AI - Deep transaction analysis for smarter spending</p>
    </div>

    <!-- Budget Alert -->
    @if($remainingBudget < 0)
    <div class="alert-warning">
        <div class="alert-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <strong>Budget Exceeded!</strong>
            <div style="color: var(--text-secondary);">You've exceeded your monthly budget by M{{ number_format(abs($remainingBudget), 2) }}. Check AI recommendations below.</div>
        </div>
    </div>
    @endif

    <!-- Month Filter -->
    <form action="{{ route('dashboard.index') }}" method="GET" class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">
                <i class="fas fa-calendar-alt"></i> Select Period
            </label>
            <input type="month" name="month" value="{{ $selectedMonth }}" class="filter-input">
        </div>
        <button type="submit" class="btn-filter">
            <i class="fas fa-sync-alt"></i> Update Analysis
        </button>
    </form>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card expense">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value">M{{ number_format($totalExpenses, 2) }}</div>
            <div class="stat-change {{ $expensesPercentageChange >= 0 ? 'change-negative' : 'change-positive' }}">
                <i class="fas fa-arrow-{{ $expensesPercentageChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($expensesPercentageChange), 1) }}% vs last month
            </div>
        </div>

        <div class="stat-card income">
            <div class="stat-label">Total Income</div>
            <div class="stat-value">M{{ number_format($totalIncome, 2) }}</div>
            <div class="stat-change {{ $incomePercentageChange >= 0 ? 'change-positive' : 'change-negative' }}">
                <i class="fas fa-arrow-{{ $incomePercentageChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($incomePercentageChange), 1) }}% vs last month
            </div>
        </div>

        <div class="stat-card budget">
            <div class="stat-label">Budget Status</div>
            <div class="stat-value">M{{ number_format(abs($remainingBudget), 2) }}</div>
            <div class="stat-change {{ $remainingBudget >= 0 ? 'change-positive' : 'change-negative' }}">
                <i class="fas fa-{{ $remainingBudget >= 0 ? 'check-circle' : 'exclamation-circle' }}"></i>
                {{ $remainingBudget >= 0 ? 'Remaining' : 'Over budget' }}
            </div>
        </div>

        <div class="stat-card savings">
            <div class="stat-label">Net Savings</div>
            <div class="stat-value">M{{ number_format($netSavings, 2) }}</div>
            <div class="stat-change {{ $netSavings >= 0 ? 'change-positive' : 'change-negative' }}">
                <i class="fas fa-{{ $netSavings >= 0 ? 'piggy-bank' : 'wallet' }}"></i>
                {{ $netSavings >= 0 ? 'Saving' : 'Deficit' }}
            </div>
        </div>
    </div>

    <!-- AI INSIGHTS PANEL (keeping existing structure but with new colors) -->
    @if(isset($aiInsights) && !empty($aiInsights['predictions']))
    <div class="ai-panel">
        <h3 class="section-title">
            <i class="fas fa-sparkles" style="color: var(--accent-purple);"></i>
            AI Financial Intelligence
            <span class="ai-badge">
                <i class="fas fa-robot"></i> Gemini 2.0 Flash
            </span>
        </h3>

        <!-- AI Predictions -->
        @if(isset($aiInsights['predictions']))
        <div class="predictions-section">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-crystal-ball" style="color: var(--accent-purple);"></i>
                Smart Predictions
            </h4>
            
            <div class="predictions-grid">
                @if(isset($aiInsights['predictions']['monthEndTotal']))
                <div class="prediction-item">
                    <div class="prediction-label">Month-End Forecast</div>
                    <div class="prediction-value" style="color: {{ ($aiInsights['predictions']['budgetStatus'] ?? '') == 'over_budget' ? 'var(--accent-danger)' : 'var(--accent-success)' }};">
                        M{{ number_format($aiInsights['predictions']['monthEndTotal'], 2) }}
                    </div>
                    @if(isset($aiInsights['predictions']['confidence']))
                    <div class="prediction-confidence">
                        {{ $aiInsights['predictions']['confidence'] }}% confidence
                    </div>
                    @endif
                </div>
                @endif

                @if(isset($aiInsights['predictions']['budgetStatus']))
                <div class="prediction-item">
                    <div class="prediction-label">Budget Status</div>
                    <div class="prediction-value" style="font-size: 1.25rem; color: {{ $aiInsights['predictions']['budgetStatus'] == 'over_budget' ? 'var(--accent-danger)' : ($aiInsights['predictions']['budgetStatus'] == 'within_budget' ? 'var(--accent-primary)' : 'var(--accent-success)') }};">
                        {{ ucwords(str_replace('_', ' ', $aiInsights['predictions']['budgetStatus'])) }}
                    </div>
                </div>
                @endif

                @if(isset($aiInsights['predictions']['expectedVariance']) && abs($aiInsights['predictions']['expectedVariance']) > 0)
                <div class="prediction-item">
                    <div class="prediction-label">Expected Variance</div>
                    <div class="prediction-value" style="color: {{ $aiInsights['predictions']['expectedVariance'] < 0 ? 'var(--accent-success)' : 'var(--accent-danger)' }};">
                        {{ $aiInsights['predictions']['expectedVariance'] > 0 ? '+' : '' }}M{{ number_format($aiInsights['predictions']['expectedVariance'], 2) }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Spending Pattern Insights -->
        @if(isset($aiInsights['spendingPatternInsights']) && !empty($aiInsights['spendingPatternInsights']['topSpendingItems']))
        <div class="pattern-insights-grid">
            <!-- Top Spending Items -->
            <div class="pattern-card">
                <h5>
                    <i class="fas fa-chart-bar" style="color: var(--accent-purple);"></i>
                    Top Spending Items
                </h5>
                @foreach(array_slice($aiInsights['spendingPatternInsights']['topSpendingItems'], 0, 5) as $item)
                <div class="pattern-item">
                    <div class="pattern-item-info">
                        <div class="pattern-item-title">{{ $item['item'] ?? 'Unknown' }}</div>
                        <div class="pattern-item-detail">{{ $item['percentage'] ?? 0 }}% of total</div>
                    </div>
                    <div class="pattern-item-value">M{{ number_format($item['total'] ?? 0, 2) }}</div>
                </div>
                @endforeach
            </div>

            <!-- Recurring Expenses -->
            @if(isset($aiInsights['spendingPatternInsights']['recurringExpenses']) && !empty($aiInsights['spendingPatternInsights']['recurringExpenses']))
            <div class="pattern-card">
                <h5>
                    <i class="fas fa-repeat" style="color: var(--accent-info);"></i>
                    Recurring Expenses
                </h5>
                @foreach(array_slice($aiInsights['spendingPatternInsights']['recurringExpenses'], 0, 5) as $recurring)
                <div class="pattern-item">
                    <div class="pattern-item-info">
                        <div class="pattern-item-title">{{ $recurring['item'] ?? 'Unknown' }}</div>
                        <div class="pattern-item-detail">{{ ucfirst($recurring['frequency'] ?? 'unknown') }}</div>
                    </div>
                    <div class="pattern-item-value">M{{ number_format($recurring['amount'] ?? 0, 2) }}</div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Cost Per Day -->
            @if(isset($aiInsights['spendingPatternInsights']['costPerDay']) && $aiInsights['spendingPatternInsights']['costPerDay'] > 0)
            <div class="pattern-card">
                <h5>
                    <i class="fas fa-calendar-day" style="color: var(--accent-info);"></i>
                    Daily Spending Rate
                </h5>
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-info);">
                        M{{ number_format($aiInsights['spendingPatternInsights']['costPerDay'], 2) }}
                    </div>
                    <div style="color: var(--text-muted); margin-top: 0.5rem;">per day average</div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- AI Recommendations -->
        @if(isset($aiInsights['recommendations']) && count($aiInsights['recommendations']) > 0)
        <div style="margin-bottom: 1.5rem;">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-lightbulb" style="color: var(--accent-warning);"></i>
                AI Recommendations ({{ count($aiInsights['recommendations']) }})
            </h4>
            
            @foreach($aiInsights['recommendations'] as $rec)
            <div class="recommendation-card priority-{{ $rec['priority'] ?? 'low' }}">
                <div class="rec-header">
                    <div class="rec-title">{{ $rec['title'] ?? 'Recommendation' }}</div>
                    <span class="priority-badge priority-{{ $rec['priority'] ?? 'low' }}">
                        {{ $rec['priority'] ?? 'low' }} priority
                    </span>
                </div>
                <div class="rec-description">
                    {{ $rec['description'] ?? 'No description available' }}
                </div>
                
                @if(isset($rec['specificItems']) && !empty($rec['specificItems']))
                <div class="specific-items-list">
                    <strong style="font-size: 0.875rem;">Specific items to address:</strong>
                    <ul>
                        @foreach($rec['specificItems'] as $specificItem)
                        <li>{{ $specificItem }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="rec-footer">
                    @if(isset($rec['potentialSavings']) && $rec['potentialSavings'] > 0)
                    <span class="rec-savings">
                        <i class="fas fa-piggy-bank"></i>
                        Save M{{ number_format($rec['potentialSavings'], 2) }}
                    </span>
                    @endif
                    
                    @if(isset($rec['implementationDifficulty']))
                    <span class="rec-difficulty">
                        <i class="fas fa-{{ $rec['implementationDifficulty'] == 'easy' ? 'check' : ($rec['implementationDifficulty'] == 'moderate' ? 'adjust' : 'exclamation') }}"></i>
                        {{ ucfirst($rec['implementationDifficulty']) }} to implement
                    </span>
                    @endif
                    
                    @if(isset($rec['category']) && $rec['category'] != 'general')
                    <span style="font-size: 0.875rem; color: var(--text-muted);">
                        <i class="fas fa-tag"></i> {{ ucfirst($rec['category']) }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Key Insights -->
        @if(isset($aiInsights['insights']) && count($aiInsights['insights']) > 0)
        <div class="insights-section">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-chart-line" style="color: var(--accent-primary);"></i>
                Key Insights
            </h4>
            @foreach($aiInsights['insights'] as $insight)
            <div class="insight-item">
                <div class="insight-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--accent-primary);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div style="flex: 1;">{{ $insight }}</div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Behavioral Insights -->
        @if(isset($aiInsights['behavioralInsights']) && is_array($aiInsights['behavioralInsights']))
        <div class="behavioral-section">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-brain" style="color: var(--accent-purple);"></i>
                Behavioral Analysis
            </h4>
            <div class="behavioral-grid">
                @if(isset($aiInsights['behavioralInsights']['spendingTriggers']) && !empty($aiInsights['behavioralInsights']['spendingTriggers']))
                <div class="behavioral-column">
                    <h6>
                        <i class="fas fa-bolt" style="color: var(--accent-warning);"></i>
                        Spending Triggers
                    </h6>
                    <ul>
                        @foreach($aiInsights['behavioralInsights']['spendingTriggers'] as $trigger)
                        <li>{{ $trigger }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(isset($aiInsights['behavioralInsights']['improvementOpportunities']) && !empty($aiInsights['behavioralInsights']['improvementOpportunities']))
                <div class="behavioral-column">
                    <h6>
                        <i class="fas fa-arrow-up" style="color: var(--accent-primary);"></i>
                        Opportunities
                    </h6>
                    <ul>
                        @foreach($aiInsights['behavioralInsights']['improvementOpportunities'] as $opportunity)
                        <li>{{ $opportunity }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(isset($aiInsights['behavioralInsights']['strengths']) && !empty($aiInsights['behavioralInsights']['strengths']))
                <div class="behavioral-column">
                    <h6>
                        <i class="fas fa-star" style="color: var(--accent-success);"></i>
                        Your Strengths
                    </h6>
                    <ul>
                        @foreach($aiInsights['behavioralInsights']['strengths'] as $strength)
                        <li>{{ $strength }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Next Month Forecast -->
        @if(isset($aiInsights['nextMonthForecast']['expectedSpending']) && $aiInsights['nextMonthForecast']['expectedSpending'] > 0)
        <div class="predictions-section">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-calendar-days" style="color: var(--accent-purple);"></i>
                Next Month Forecast
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;">
                <div style="text-align: center; background: var(--bg-primary); padding: 1.5rem; border-radius: 12px;">
                    <div style="font-size: 0.875rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.5rem;">Expected Spending</div>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--accent-purple);">
                        M{{ number_format($aiInsights['nextMonthForecast']['expectedSpending'], 2) }}
                    </div>
                    @if(isset($aiInsights['nextMonthForecast']['confidence']))
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                        {{ $aiInsights['nextMonthForecast']['confidence'] }}% confidence
                    </div>
                    @endif
                </div>
                <div>
                    @if(isset($aiInsights['nextMonthForecast']['reasoning']))
                    <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i> AI Reasoning
                        </div>
                        <div style="line-height: 1.6;">
                            {{ $aiInsights['nextMonthForecast']['reasoning'] }}
                        </div>
                    </div>
                    @endif
                    
                    @if(isset($aiInsights['nextMonthForecast']['riskFactors']) && !empty($aiInsights['nextMonthForecast']['riskFactors']))
                    <div style="background: rgba(244, 63, 94, 0.1); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--accent-danger);">
                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--accent-danger); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i> Risk Factors
                        </div>
                        <ul style="margin: 0; padding-left: 1.25rem; color: var(--text-secondary); font-size: 0.875rem;">
                            @foreach($aiInsights['nextMonthForecast']['riskFactors'] as $risk)
                            <li>{{ $risk }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Unusual Spikes -->
        @if(isset($aiInsights['spendingPatternInsights']['unusualSpikes']) && count($aiInsights['spendingPatternInsights']['unusualSpikes']) > 0)
        <div class="insights-section">
            <h4 style="font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-chart-line" style="color: var(--accent-danger);"></i>
                Unusual Spending Detected
            </h4>
            @foreach($aiInsights['spendingPatternInsights']['unusualSpikes'] as $spike)
            <div class="insight-item">
                <div class="insight-icon" style="background: rgba(244, 63, 94, 0.1); color: var(--accent-danger);">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div style="flex: 1;">
                    <strong>{{ $spike['item'] ?? 'Unknown' }}</strong>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">
                        {{ $spike['reason'] ?? 'Unusual spending detected' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @else
    <div class="ai-panel">
        <div class="ai-loading">
            <div class="spinner">
                <i class="fas fa-spinner"></i>
            </div>
            <h3>AI is analyzing your financial data...</h3>
            <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                Analyzing {{ isset($allTransactions) ? count($allTransactions) : 0 }} transactions across all categories
            </p>
        </div>
    </div>
    @endif

    <!-- Charts -->
    <div class="chart-card" style="margin-bottom: 2rem;">
        <div class="chart-title">
            <i class="fas fa-chart-line" style="color: var(--accent-primary);"></i>
            Daily Spending Trend
        </div>
        <div class="chart-container">
            <canvas id="dailyTrendChart"></canvas>
        </div>
    </div>

    <!-- Category Budget vs Spent -->
    <div class="chart-card" style="margin-bottom: 2rem;">
        <h3 class="chart-title">
            <i class="fas fa-chart-bar" style="color: var(--accent-purple);"></i>
            Budget vs Actual Spending by Category
        </h3>
        
        @if(isset($categoryBreakdown) && count($categoryBreakdown) > 0)
            @foreach($categoryBreakdown as $category)
            @php
                $spentPercentage = $category['budget'] > 0 ? ($category['expense'] / $category['budget']) * 100 : 0;
                $isOverBudget = $category['expense'] > $category['budget'] && $category['budget'] > 0;
                $barColor = $isOverBudget ? 'var(--accent-danger)' : ($spentPercentage > 80 ? 'var(--accent-warning)' : 'var(--accent-success)');
            @endphp
            
            <div style="margin-bottom: 2rem;">
                <!-- Category Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">{{ $category['name'] }}</h4>
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            @if($category['budget'] > 0)
                                <span style="color: {{ $barColor }}; font-weight: 600;">
                                    {{ number_format(min($spentPercentage, 100), 1) }}% used
                                </span>
                                @if($isOverBudget)
                                    <span style="color: var(--accent-danger); font-weight: 600; margin-left: 0.5rem;">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        M{{ number_format($category['expense'] - $category['budget'], 2) }} over budget
                                    </span>
                                @endif
                            @else
                                <span style="color: var(--text-muted);">No budget set</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.25rem; font-weight: 800; color: {{ $barColor }};">
                            M{{ number_format($category['expense'], 2) }}
                        </div>
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            of M{{ number_format($category['budget'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div style="background: var(--bg-primary); border-radius: 10px; height: 40px; overflow: hidden; position: relative;">
                    <div style="
                        background: linear-gradient(90deg, {{ $barColor }}, {{ $barColor }}dd);
                        height: 100%;
                        width: {{ $category['budget'] > 0 ? min(($category['expense'] / $category['budget']) * 100, 100) : 0 }}%;
                        border-radius: 10px;
                        transition: width 0.5s ease;
                        display: flex;
                        align-items: center;
                        padding: 0 1rem;
                        color: white;
                        font-weight: 700;
                        font-size: 0.875rem;
                    ">
                        @if($spentPercentage > 10)
                            M{{ number_format($category['expense'], 2) }}
                        @endif
                    </div>
                    
                    @if($spentPercentage <= 10 && $category['expense'] > 0)
                        <div style="
                            position: absolute;
                            left: 1rem;
                            top: 50%;
                            transform: translateY(-50%);
                            color: var(--text-secondary);
                            font-weight: 700;
                            font-size: 0.875rem;
                        ">
                            M{{ number_format($category['expense'], 2) }}
                        </div>
                    @endif
                </div>

                <!-- Additional Stats -->
                <div style="display: flex; gap: 2rem; margin-top: 0.75rem; font-size: 0.875rem;">
                    <div>
                        <span style="color: var(--text-muted);">Remaining:</span>
                        <span style="font-weight: 600; color: {{ $isOverBudget ? 'var(--accent-danger)' : 'var(--accent-success)' }};">
                            M{{ number_format(max($category['budget'] - $category['expense'], 0), 2) }}
                        </span>
                    </div>
                    @if($category['average_amount'] > 0)
                    <div>
                        <span style="color: var(--text-muted);">vs Average:</span>
                        <span style="font-weight: 600; color: {{ $category['vs_average'] > 0 ? 'var(--accent-danger)' : 'var(--accent-success)' }};">
                            {{ $category['vs_average'] > 0 ? '+' : '' }}{{ number_format($category['vs_average'], 1) }}%
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i class="fas fa-chart-bar" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                <p>No spending data available for this period</p>
            </div>
        @endif
    </div>

    <!-- Transactions Table WITH DATATABLES -->
    <div class="chart-card" style="margin-bottom: 2rem;">
        <h3 class="chart-title">
            <i class="fas fa-list" style="color: var(--accent-secondary);"></i>
            Recent Transactions
            @if(isset($allTransactions))
            <span style="font-size: 0.875rem; color: var(--text-muted); font-weight: normal;">
                ({{ count($allTransactions) }} total)
            </span>
            @endif
        </h3>
        <div style="overflow-x: auto;">
            <table id="transactionsTable" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($allTransactions) && count($allTransactions) > 0)
                        @foreach($allTransactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}</td>
                            <td>{{ $transaction->description }}</td>
                            <td>
                                <span style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; {{ $transaction->type == 'Income' ? 'background: rgba(20, 184, 166, 0.1); color: var(--accent-success);' : 'background: rgba(244, 63, 94, 0.1); color: var(--accent-danger);' }}">
                                    {{ $transaction->type }}
                                </span>
                            </td>
                            <td>{{ $transaction->category->name ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">M{{ number_format($transaction->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Initialize DataTables
$(document).ready(function() {
    $('#transactionsTable').DataTable({
        responsive: true,
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ transactions",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "No transactions found",
            infoFiltered: "(filtered from _MAX_ total)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});

// Chart.js configuration with new colors
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: { 
                font: { size: 13, weight: '600', family: 'Inter' },
                padding: 15
            }
        }
    }
};

// Daily Trend Chart
@if(isset($dailySpending) && count($dailySpending) > 0)
@php
    $dailyLabels = $dailySpending->keys()->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('M d');
    });
    $dailyValues = $dailySpending->values();
@endphp

new Chart(document.getElementById('dailyTrendChart'), {
    type: 'line',
    data: {
        labels: @json($dailyLabels),
        datasets: [{
            label: 'Daily Spending',
            data: @json($dailyValues),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { 
                    callback: (value) => 'M' + value,
                    font: { family: 'Inter' }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: { family: 'Inter' }
                }
            }
        }
    }
});
@endif
</script>
@endsection