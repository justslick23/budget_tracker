@extends('layouts.app')
@section('title', 'Financial Intelligence Dashboard')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    color: #1a1a1a;
    line-height: 1.6;
    min-height: 100vh;
}

.container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
    animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header Section */
.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 24px;
    margin-bottom: 30px;
    color: white;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 15s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.1) rotate(180deg); }
}

.header-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header h1 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 10px;
}

.period-selector {
    display: flex;
    gap: 12px;
    align-items: center;
}

.period-selector select,
.period-selector button {
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 12px;
    color: #667eea;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    transition: all 0.3s ease;
}

.period-selector button {
    background: white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.period-selector button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

/* Health Score Card */
.health-score-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    padding: 40px;
    margin-bottom: 30px;
    color: white;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 40px;
    align-items: center;
}

.score-circle {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
}

.score-value {
    font-size: 56px;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.score-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #667eea;
    font-weight: 600;
}

.health-info h2 {
    font-size: 32px;
    margin-bottom: 12px;
    text-transform: capitalize;
}

.health-info p {
    font-size: 16px;
    opacity: 0.95;
    line-height: 1.6;
    margin-bottom: 20px;
}

.trend-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

/* Alert Cards */
.alerts-section {
    margin-bottom: 30px;
}

.alert {
    background: white;
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 12px;
    display: flex;
    align-items: start;
    gap: 16px;
    border-left: 4px solid;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.alert:hover {
    transform: translateX(4px);
    box-shadow: 0 6px 30px rgba(0,0,0,0.08);
}

.alert-urgent { border-color: #ef4444; background: linear-gradient(to right, rgba(239, 68, 68, 0.05), white); }
.alert-warning { border-color: #f59e0b; background: linear-gradient(to right, rgba(245, 158, 11, 0.05), white); }
.alert-info { border-color: #3b82f6; background: linear-gradient(to right, rgba(59, 130, 246, 0.05), white); }
.alert-success { border-color: #10b981; background: linear-gradient(to right, rgba(16, 185, 129, 0.05), white); }

.alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.alert-urgent .alert-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.alert-warning .alert-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.alert-info .alert-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.alert-success .alert-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }

.alert-content {
    flex: 1;
}

.alert-content strong {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    font-size: 15px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 150px;
    height: 150px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
}

.stat-trend.up { color: #10b981; }
.stat-trend.down { color: #ef4444; }

.stat-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin-top: 8px;
}

/* Card Component */
.card {
    background: white;
    padding: 32px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    margin-bottom: 30px;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.card-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.card-subtitle {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 20px;
    line-height: 1.6;
}

/* Forecast Cards */
.forecast-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.forecast-card {
    background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
    padding: 24px;
    border-radius: 16px;
    text-align: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.forecast-card:hover {
    border-color: #667eea;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
}

.forecast-month {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.forecast-amount {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}

.forecast-confidence {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

/* Category Progress */
.category-item {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.category-item:hover {
    background: #f3f4f6;
    transform: translateX(4px);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.category-name {
    font-weight: 600;
    font-size: 15px;
    color: #1a1a1a;
}

.category-percent {
    font-weight: 700;
    font-size: 15px;
    color: #667eea;
}

.progress-bar {
    height: 10px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 12px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: width 0.6s ease;
}

.progress-fill.warning {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
}

.progress-fill.danger {
    background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
}

.category-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #6b7280;
}

.variance-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.variance-badge.positive {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.variance-badge.negative {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

/* Recommendations */
.recommendation-item {
    background: linear-gradient(135deg, #f9fafb 0%, white 100%);
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 12px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
}

.recommendation-item:hover {
    border-color: #667eea;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.rec-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.rec-title {
    font-weight: 700;
    font-size: 16px;
    color: #1a1a1a;
    flex: 1;
}

.priority-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge.high {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.priority-badge.medium {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.priority-badge.low {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.rec-description {
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.rec-impact {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

/* Chart Container */
.chart-container {
    height: 400px;
    margin-top: 20px;
    position: relative;
}

/* Transactions Table */
.transactions-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}

.transactions-table thead th {
    background: #f9fafb;
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    font-weight: 600;
    border: none;
}

.transactions-table tbody tr {
    background: white;
    transition: all 0.3s ease;
}

.transactions-table tbody tr:hover {
    background: #f9fafb;
    transform: scale(1.01);
}

.transactions-table tbody td {
    padding: 16px;
    border-top: 1px solid #f3f4f6;
    border-bottom: 1px solid #f3f4f6;
}

.transactions-table tbody td:first-child {
    border-left: 1px solid #f3f4f6;
    border-radius: 12px 0 0 12px;
}

.transactions-table tbody td:last-child {
    border-right: 1px solid #f3f4f6;
    border-radius: 0 12px 12px 0;
}

.transaction-date {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

.transaction-desc {
    font-weight: 600;
    color: #1a1a1a;
}

.transaction-category {
    display: inline-block;
    padding: 4px 10px;
    background: #f3f4f6;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
}

.transaction-amount {
    font-weight: 700;
    text-align: right;
}

.transaction-amount.income {
    color: #10b981;
}

.transaction-amount.expense {
    color: #1a1a1a;
}

/* Interactive Elements */
.interactive-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.spending-heatmap {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.heatmap-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-top: 16px;
}

.heatmap-day {
    aspect-ratio: 1;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.heatmap-day:hover {
    transform: scale(1.1);
    z-index: 10;
}

.heatmap-day-label {
    color: #6b7280;
    margin-bottom: 2px;
}

.heatmap-day-amount {
    font-size: 10px;
    color: #374151;
}

/* Responsive */
@media (max-width: 768px) {
    .header h1 {
        font-size: 28px;
    }
    
    .health-score-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .score-circle {
        margin: 0 auto;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .interactive-section {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container">
    <!-- Header with Period Selector -->
    <div class="header">
        <div class="header-content">
            <div>
                <h1>
                    <span>💰</span>
                    Financial Intelligence
                </h1>
                <p>AI-powered insights from {{ count($allTransactions ?? []) }} transactions</p>
                <p style="font-size: 14px; opacity: 0.9; margin-top: 8px;">
                    📅 Period: {{ \Carbon\Carbon::parse($selectedMonth)->subMonth()->format('M d, Y') }} (26th) 
                    to {{ \Carbon\Carbon::parse($selectedMonth)->format('M d, Y') }} (25th)
                </p>
                <div class="ai-badge">
                    <span>✨</span>
                    <span>Powered by Gemini AI</span>
                </div>
            </div>
            <form method="GET" action="{{ route('dashboard.index') }}" class="period-selector">
                <select name="month">
                    @for($i = 0; $i < 12; $i++)
                        @php $date = now()->subMonths($i)->format('Y-m'); @endphp
                        <option value="{{ $date }}" {{ $selectedMonth == $date ? 'selected' : '' }}>
                            {{ now()->subMonths($i)->format('F Y') }} Period
                        </option>
                    @endfor
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- Health Score Overview -->
    @if(isset($aiInsights['executive_summary']))
    <div class="health-score-card">
        <div class="score-circle">
            <div class="score-value">{{ $aiInsights['executive_summary']['overall_health_score'] ?? 0 }}</div>
            <div class="score-label">Health Score</div>
        </div>
        <div class="health-info">
            <h2>{{ str_replace('_', ' ', $aiInsights['executive_summary']['financial_status'] ?? 'Unknown') }}</h2>
            <p>{{ $aiInsights['executive_summary']['key_insight'] ?? 'Analyzing your financial patterns...' }}</p>
            @if(!empty($aiInsights['spending_trends']['monthly_trend']))
            <div class="trend-badge">
                <span>{{ in_array($aiInsights['spending_trends']['monthly_trend'], ['increasing', 'volatile']) ? '📈' : '📉' }}</span>
                <span>{{ ucfirst($aiInsights['spending_trends']['monthly_trend']) }} trend ({{ number_format(abs($aiInsights['spending_trends']['trend_percentage'] ?? 0), 1) }}%)</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Urgent Alerts -->
    @if(isset($aiInsights['executive_summary']['urgent_actions']) && count($aiInsights['executive_summary']['urgent_actions']) > 0)
    <div class="alerts-section">
        @foreach($aiInsights['executive_summary']['urgent_actions'] as $action)
        <div class="alert alert-urgent">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <strong>Urgent Action Required</strong>
                <div>{{ $action }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Key Metrics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value">M{{ number_format($totalExpenses ?? 0, 2) }}</div>
            <div class="stat-trend {{ ($expenseChange ?? 0) >= 0 ? 'down' : 'up' }}">
                <span>{{ ($expenseChange ?? 0) >= 0 ? '↑' : '↓' }}</span>
                <span>{{ number_format(abs($expenseChange ?? 0), 1) }}% vs last period</span>
            </div>
            @if(isset($spendingVelocity['projected_month_end']))
            <div class="stat-subtitle">Projected: M{{ number_format($spendingVelocity['projected_month_end'], 2) }}</div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-label">Daily Burn Rate</div>
            <div class="stat-value">M{{ number_format(($totalExpenses ?? 0) / max($daysElapsed ?? 1, 1), 2) }}</div>
            @if(isset($spendingVelocity['status']))
            <div class="stat-trend {{ $spendingVelocity['status'] == 'fast' ? 'down' : 'up' }}">
                <span>{{ $spendingVelocity['status'] == 'fast' ? '⚡' : '🐢' }}</span>
                <span>{{ ucfirst($spendingVelocity['status']) }} pace</span>
            </div>
            @endif
            <div class="stat-subtitle">{{ number_format(abs($spendingVelocity['acceleration'] ?? 0), 1) }}% vs historical</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Budget Status</div>
            <div class="stat-value" style="{{ ($remainingBudget ?? 0) >= 0 ? 'color: #10b981;' : 'color: #ef4444;' }}">
                M{{ number_format(abs($remainingBudget ?? 0), 2) }}
            </div>
            <div class="stat-trend {{ ($remainingBudget ?? 0) >= 0 ? 'up' : 'down' }}">
                <span>{{ ($remainingBudget ?? 0) >= 0 ? '✓' : '✗' }}</span>
                <span>{{ ($remainingBudget ?? 0) >= 0 ? 'Remaining' : 'Over Budget' }}</span>
            </div>
            @if(isset($aiInsights['kpi_summary']['days_to_budget_exhaustion']) && $aiInsights['kpi_summary']['days_to_budget_exhaustion'])
            <div class="stat-subtitle">{{ $aiInsights['kpi_summary']['days_to_budget_exhaustion'] }} days until exhaustion</div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-label">Savings Rate</div>
            <div class="stat-value">{{ number_format((($totalIncome ?? 0) > 0 ? ((($totalIncome ?? 0) - ($totalExpenses ?? 0)) / ($totalIncome ?? 0)) * 100 : 0), 1) }}%</div>
            <div class="stat-trend {{ ($netSavings ?? 0) >= 0 ? 'up' : 'down' }}">
                <span>{{ ($netSavings ?? 0) >= 0 ? '💰' : '💸' }}</span>
                <span>M{{ number_format(abs($netSavings ?? 0), 2) }} saved</span>
            </div>
        </div>
    </div>

    <!-- AI Predictions -->
    @if(isset($aiInsights['spending_trends']['forecast_next_3_months']) && count($aiInsights['spending_trends']['forecast_next_3_months']) > 0)
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">🔮</div>
                <span>3-Month Forecast</span>
            </div>
        </div>
        <p class="card-subtitle">AI-powered predictions based on {{ count($allTransactions ?? []) }} transactions and 12 months of historical data</p>
        
        <div class="forecast-grid">
            @foreach($aiInsights['spending_trends']['forecast_next_3_months'] as $forecast)
            <div class="forecast-card">
                <div class="forecast-month">{{ $forecast['month'] }}</div>
                <div class="forecast-amount">M{{ number_format($forecast['predicted_spend'], 0) }}</div>
                <div class="forecast-confidence">{{ ucfirst($forecast['confidence'] ?? 'medium') }} confidence</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Spending Anomalies -->
    @if(isset($aiInsights['spending_trends']['unusual_spikes']) && count($aiInsights['spending_trends']['unusual_spikes']) > 0)
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">🚨</div>
                <span>Unusual Spending Detected</span>
            </div>
        </div>
        
        @foreach($aiInsights['spending_trends']['unusual_spikes'] as $spike)
        <div class="alert alert-warning">
            <div class="alert-icon">⚡</div>
            <div class="alert-content">
                <strong>{{ \Carbon\Carbon::parse($spike['date'])->format('M d, Y') }} - M{{ number_format($spike['amount'], 2) }}</strong>
                <div style="font-size: 13px; margin-top: 4px;">{{ $spike['description'] }} ({{ $spike['category'] }})</div>
                <div style="font-size: 12px; color: #f59e0b; margin-top: 4px;">
                    {{ number_format($spike['deviation_percentage'], 0) }}% above normal spending
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Interactive Section: Category Performance & Weekly Pattern -->
    <div class="interactive-section">
        <!-- Category Performance -->
        @if(isset($aiInsights['category_analysis']) && count($aiInsights['category_analysis']) > 0)
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-icon">📊</div>
                    <span>Category Performance</span>
                </div>
            </div>
            
            @foreach($aiInsights['category_analysis'] as $cat)
            <div class="category-item">
                <div class="category-header">
                    <div class="category-name">{{ $cat['category'] }}</div>
                    <div class="category-percent">{{ number_format($cat['percent_used'] ?? 0, 0) }}%</div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill {{ ($cat['percent_used'] ?? 0) > 100 ? 'danger' : (($cat['percent_used'] ?? 0) > 80 ? 'warning' : '') }}" 
                         style="width: {{ min($cat['percent_used'] ?? 0, 100) }}%"></div>
                </div>
                <div class="category-details">
                    <span>M{{ number_format($cat['spent'] ?? 0, 2) }} / M{{ number_format($cat['budgeted'] ?? 0, 2) }}</span>
                    @if(isset($cat['variance_vs_historical']))
                    <span class="variance-badge {{ $cat['variance_vs_historical'] > 0 ? 'positive' : 'negative' }}">
                        {{ $cat['variance_vs_historical'] > 0 ? '+' : '' }}{{ number_format($cat['variance_vs_historical'], 0) }}% vs avg
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Weekly Spending Pattern -->
        @if(isset($aiInsights['weekly_daily_insights']['day_of_week_pattern']))
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <div class="card-icon">📅</div>
                    <span>Weekly Pattern</span>
                </div>
            </div>
            <p class="card-subtitle">{{ $aiInsights['weekly_daily_insights']['day_of_week_pattern']['pattern_interpretation'] ?? 'Analyzing your weekly habits...' }}</p>
            
            @php
                $dayPattern = $aiInsights['weekly_daily_insights']['day_of_week_pattern'];
            @endphp
            
            <div style="background: #f9fafb; padding: 16px; border-radius: 12px; margin-top: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Highest</div>
                        <div style="font-weight: 700; color: #ef4444;">{{ $dayPattern['highest_spending_day'] ?? 'Unknown' }}</div>
                        <div style="font-size: 13px; color: #6b7280;">M{{ number_format($dayPattern['highest_amount'] ?? 0, 0) }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Lowest</div>
                        <div style="font-weight: 700; color: #10b981;">{{ $dayPattern['lowest_spending_day'] ?? 'Unknown' }}</div>
                        <div style="font-size: 13px; color: #6b7280;">M{{ number_format($dayPattern['lowest_amount'] ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Historical Trend Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">📈</div>
                <span>12-Month Trend Analysis</span>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown Chart -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">🎯</div>
                <span>Spending Distribution</span>
            </div>
        </div>
        <div class="chart-container" style="height: 350px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- AI Recommendations -->
    @if(isset($aiInsights['actionable_recommendations']) && count($aiInsights['actionable_recommendations']) > 0)
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">💡</div>
                <span>Smart Recommendations</span>
            </div>
        </div>
        <p class="card-subtitle">AI-generated insights to optimize your spending and increase savings</p>
        
        @foreach($aiInsights['actionable_recommendations'] as $rec)
        <div class="recommendation-item">
            <div class="rec-header">
                <div class="rec-title">{{ $rec['title'] }}</div>
                <div class="priority-badge {{ $rec['priority'] ?? 'low' }}">{{ strtoupper($rec['priority'] ?? 'low') }}</div>
            </div>
            <div class="rec-description">{{ $rec['description'] }}</div>
            @if(isset($rec['expected_impact']))
            <div class="rec-impact">
                <span>💰</span>
                <span>{{ $rec['expected_impact'] }}</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Behavioral Insights -->
    @if(isset($aiInsights['behavioral_insights']))
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">🧠</div>
                <span>Spending Behavior Analysis</span>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Strengths</h4>
                @if(isset($aiInsights['behavioral_insights']['strengths']))
                    @foreach($aiInsights['behavioral_insights']['strengths'] as $strength)
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 12px; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                        <span style="color: #10b981; flex-shrink: 0;">✓</span>
                        <span style="font-size: 14px; color: #1a1a1a;">{{ $strength }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
            
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Areas to Improve</h4>
                @if(isset($aiInsights['behavioral_insights']['weaknesses']))
                    @foreach($aiInsights['behavioral_insights']['weaknesses'] as $weakness)
                    <div style="background: rgba(245, 158, 11, 0.1); padding: 12px; border-radius: 8px; margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                        <span style="color: #f59e0b; flex-shrink: 0;">→</span>
                        <span style="font-size: 14px; color: #1a1a1a;">{{ $weakness }}</span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        @if(isset($aiInsights['behavioral_insights']['spending_personality']))
        <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Your Spending Personality</div>
            <div style="font-size: 18px; font-weight: 700;">{{ $aiInsights['behavioral_insights']['spending_personality'] }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">📝</div>
                <span>Recent Transactions</span>
            </div>
        </div>
        
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($allTransactions ?? [], 0, 10) as $txn)
                <tr>
                    <td>
                        <div class="transaction-date">{{ \Carbon\Carbon::parse($txn['date'])->format('M d, Y') }}</div>
                    </td>
                    <td>
                        <div class="transaction-desc">{{ $txn['description'] }}</div>
                    </td>
                    <td>
                        <span class="transaction-category">{{ $txn['category'] }}</span>
                    </td>
                    <td>
                        <div class="transaction-amount {{ $txn['type'] }}">
                            {{ $txn['type'] == 'income' ? '+' : '' }}M{{ number_format($txn['amount'], 2) }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.js default configuration
Chart.defaults.color = '#6b7280';
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.titleFont = { size: 13, weight: '600' };
Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };

// Trend Chart
@if(isset($monthlyTrend) && count($monthlyTrend) > 0)
const trendCtx = document.getElementById('trendChart');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyTrend->pluck('month')),
        datasets: [
            {
                label: 'Expenses',
                data: @json($monthlyTrend->pluck('expenses')),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#ef4444',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            },
            {
                label: 'Income',
                data: @json($monthlyTrend->pluck('income')),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            },
            {
                label: 'Savings',
                data: @json($monthlyTrend->pluck('savings')),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 13,
                        weight: '600'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': M' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f3f4f6',
                    drawBorder: false
                },
                ticks: {
                    callback: function(value) {
                        return 'M' + value;
                    },
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            }
        }
    }
});
@endif

// Category Distribution Chart
@if(isset($categoryChart) && count($categoryChart) > 0)
const categoryCtx = document.getElementById('categoryChart');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: @json($categoryChart->pluck('category')),
        datasets: [{
            data: @json($categoryChart->pluck('amount')),
            backgroundColor: [
                '#667eea',
                '#764ba2',
                '#f093fb',
                '#4facfe',
                '#43e97b',
                '#fa709a',
                '#fee140',
                '#30cfd0',
                '#a8edea',
                '#ff6b6b'
            ],
            borderWidth: 0,
            hoverOffset: 15
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    font: {
                        size: 13,
                        weight: '600'
                    },
                    generateLabels: function(chart) {
                        const data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const percentage = ((value / total) * 100).toFixed(1);
                                return {
                                    text: `${label} (${percentage}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                        return [];
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: M${value.toFixed(2)} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
@endif

// Animate progress bars on load
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});
</script>

@endsection