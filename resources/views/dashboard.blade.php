@extends('layouts.app')
@section('title', 'Financial Intelligence Dashboard')
@section('content')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
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
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        padding: 40px;
        border-radius: 24px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 20px 60px rgba(8, 145, 178, 0.3);
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
        font-family: 'Inter', sans-serif;
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
        flex-wrap: wrap;
    }
    
    .period-selector select,
    .period-selector button {
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 12px;
        color: #0891b2;
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
    
    /* View Toggle */
    .view-toggle {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px;
        border-radius: 12px;
    }
    
    .view-toggle button {
        padding: 8px 16px;
        background: transparent;
        border: none;
        color: white;
        font-weight: 600;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .view-toggle button.active {
        background: white;
        color: #0891b2;
    }
    
    /* Health Score Card */
    .health-score-card {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 20px 60px rgba(8, 145, 178, 0.3);
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
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Inter', sans-serif;
    }
    
    .score-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0891b2;
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
    .alert-info { border-color: #0891b2; background: linear-gradient(to right, rgba(8, 145, 178, 0.05), white); }
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
    .alert-info .alert-icon { background: rgba(8, 145, 178, 0.1); color: #0891b2; }
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
        background: linear-gradient(135deg, rgba(8, 145, 178, 0.05) 0%, transparent 70%);
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
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Inter', sans-serif;
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
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
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
    
    /* CATEGORY BUDGET COMPARISON - NEW ENHANCED VERSION */
    .budget-comparison-grid {
        display: grid;
        gap: 20px;
        margin-top: 20px;
    }
    
    .budget-category-card {
        background: linear-gradient(135deg, #f9fafb 0%, white 100%);
        border-radius: 16px;
        padding: 24px;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .budget-category-card:hover {
        border-color: #0891b2;
        box-shadow: 0 8px 25px rgba(8, 145, 178, 0.15);
        transform: translateY(-2px);
    }
    
    .budget-category-card.over-budget {
        border-color: #ef4444;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, white 100%);
    }
    
    .budget-category-card.near-limit {
        border-color: #f59e0b;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, white 100%);
    }
    
    .budget-category-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
    }
    
    .budget-category-name {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        font-family: 'Inter', sans-serif;
    }
    
    .budget-status-badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .budget-status-badge.on-track {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    
    .budget-status-badge.near-limit {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .budget-status-badge.over-budget {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .budget-amounts {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .budget-amount-box {
        text-align: center;
        padding: 12px;
        background: #f9fafb;
        border-radius: 12px;
    }
    
    .budget-amount-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 600;
    }
    
    .budget-amount-value {
        font-size: 20px;
        font-weight: 800;
        font-family: 'Inter', sans-serif;
    }
    
    .budget-amount-value.budgeted {
        color: #0891b2;
    }
    
    .budget-amount-value.spent {
        color: #1a1a1a;
    }
    
    .budget-amount-value.remaining {
        color: #10b981;
    }
    
    .budget-amount-value.overspent {
        color: #ef4444;
    }
    
    /* Visual Progress Bar */
    .budget-visual-progress {
        margin-bottom: 16px;
    }
    
    .budget-progress-bar {
        height: 12px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        margin-bottom: 8px;
    }
    
    .budget-progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.6s ease;
        position: relative;
        background: linear-gradient(90deg, #0891b2 0%, #0e7490 100%);
    }
    
    .budget-progress-fill.warning {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }
    
    .budget-progress-fill.danger {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }
    
    /* Markers on progress bar */
    .budget-progress-marker {
        position: absolute;
        top: 0;
        width: 2px;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
    }
    
    .budget-progress-labels {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #6b7280;
    }
    
    .budget-percentage {
        font-weight: 700;
        font-family: 'Inter', sans-serif;
    }
    
    /* Transaction Details */
    .budget-transaction-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        font-size: 13px;
    }
    
    .budget-txn-count {
        color: #6b7280;
    }
    
    .budget-avg-txn {
        font-weight: 600;
        color: #1a1a1a;
    }
    
    .budget-vs-avg {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .budget-vs-avg.positive {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .budget-vs-avg.negative {
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
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
        border-color: #0891b2;
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(8, 145, 178, 0.2);
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
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
        font-family: 'Inter', sans-serif;
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
        color: #0891b2;
        font-family: 'Inter', sans-serif;
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
        background: linear-gradient(90deg, #0891b2 0%, #0e7490 100%);
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
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
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
        border-color: #0891b2;
        box-shadow: 0 8px 25px rgba(8, 145, 178, 0.15);
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
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
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
    
    /* DataTables Customization - FIXED */
    .dataTables_wrapper {
        padding: 0 !important;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 16px;
    }
    
    .dataTables_filter input {
        border: 2px solid #e5e7eb !important;
        border-radius: 8px !important;
        padding: 8px 12px !important;
        margin-left: 8px !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    .dataTables_length select {
        border: 2px solid #e5e7eb !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        margin: 0 8px !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    .dt-buttons {
        margin-bottom: 16px !important;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .dt-button {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;
        color: white !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-family: 'Inter', sans-serif !important;
        transition: all 0.3s ease !important;
        cursor: pointer !important;
    }
    
    .dt-button:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 15px rgba(8, 145, 178, 0.3) !important;
    }
    
    table.dataTable {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    
    table.dataTable thead th {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;
        color: white !important;
        font-weight: 600 !important;
        padding: 16px !important;
        border: none !important;
        text-align: left !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    table.dataTable thead th:first-child {
        border-top-left-radius: 12px !important;
    }
    
    table.dataTable thead th:last-child {
        border-top-right-radius: 12px !important;
    }
    
    table.dataTable tbody tr {
        transition: all 0.3s ease !important;
        background: white !important;
    }
    
    table.dataTable tbody tr:hover {
        background: #f9fafb !important;
    }
    
    table.dataTable tbody td {
        padding: 16px !important;
        border-bottom: 1px solid #f3f4f6 !important;
        vertical-align: middle !important;
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
    
    .transaction-type {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    .transaction-type.income {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    
    .transaction-type.expense {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .transaction-amount {
        font-weight: 700;
        text-align: right !important;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
    }
    
    .transaction-amount.income {
        color: #10b981;
    }
    
    .transaction-amount.expense {
        color: #1a1a1a;
    }
    
    /* Pagination styling */
    .dataTables_paginate {
        margin-top: 20px !important;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 8px 12px !important;
        margin: 0 4px !important;
        border-radius: 8px !important;
        border: 1px solid #e5e7eb !important;
        background: white !important;
        color: #1a1a1a !important;
        transition: all 0.3s ease !important;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #0891b2 !important;
        color: white !important;
        border-color: #0891b2 !important;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;
        color: white !important;
        border-color: #0891b2 !important;
    }
    
    /* Year Overview Section */
    .year-overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    
    .year-stat-card {
        background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
        padding: 20px;
        border-radius: 16px;
        text-align: center;
    }
    
    .year-stat-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .year-stat-value {
        font-size: 28px;
        font-weight: 800;
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Inter', sans-serif;
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
        
        .period-selector {
            width: 100%;
        }
        
        .period-selector select,
        .period-selector button {
            flex: 1;
        }
        
        .budget-amounts {
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
                @if($viewMode === 'month')
                <p style="font-size: 14px; opacity: 0.9; margin-top: 8px;">
                    📅 Period: {{ \Carbon\Carbon::parse($selectedMonth)->subMonth()->format('M d, Y') }} (26th) 
                    to {{ \Carbon\Carbon::parse($selectedMonth)->format('M d, Y') }} (25th)
                </p>
                @else
                <p style="font-size: 14px; opacity: 0.9; margin-top: 8px;">
                    📅 Year Overview: {{ $selectedYear }}
                </p>
                @endif
                <div class="ai-badge">
                    <span>✨</span>
                    <span>Powered by Gemini AI</span>
                </div>
            </div>
            <form method="GET" action="{{ route('dashboard.index') }}" class="period-selector">
                <div class="view-toggle">
                    <button type="button" class="{{ $viewMode === 'month' ? 'active' : '' }}" 
                            onclick="document.querySelector('input[name=view]').value='month'; this.form.submit();">
                        Month
                    </button>
                    <button type="button" class="{{ $viewMode === 'year' ? 'active' : '' }}"
                            onclick="document.querySelector('input[name=view]').value='year'; this.form.submit();">
                        Year
                    </button>
                </div>
                <input type="hidden" name="view" value="{{ $viewMode }}">
                
                @if($viewMode === 'month')
                <select name="month">
                    @for($i = 0; $i < 12; $i++)
                        @php $date = now()->subMonths($i)->format('Y-m'); @endphp
                        <option value="{{ $date }}" {{ $selectedMonth == $date ? 'selected' : '' }}>
                            {{ now()->subMonths($i)->format('F Y') }} Period
                        </option>
                    @endfor
                </select>
                @else
                <select name="year">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
                @endif
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    @if($viewMode === 'year')
    <!-- Year Overview Section -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">📊</div>
                <span>{{ $selectedYear }} Annual Performance</span>
            </div>
        </div>
        
        <div class="year-overview-grid">
            <div class="year-stat-card">
                <div class="year-stat-label">Total Income</div>
                <div class="year-stat-value">M{{ number_format($yearIncome ?? 0, 2) }}</div>
            </div>
            <div class="year-stat-card">
                <div class="year-stat-label">Total Expenses</div>
                <div class="year-stat-value">M{{ number_format($yearExpenses ?? 0, 2) }}</div>
            </div>
            <div class="year-stat-card">
                <div class="year-stat-label">Net Savings</div>
                <div class="year-stat-value" style="{{ ($yearSavings ?? 0) >= 0 ? 'color: #10b981;' : 'color: #ef4444;' }}">
                    M{{ number_format($yearSavings ?? 0, 2) }}
                </div>
            </div>
            <div class="year-stat-card">
                <div class="year-stat-label">Savings Rate</div>
                <div class="year-stat-value">{{ number_format($yearSavingsRate ?? 0, 1) }}%</div>
            </div>
        </div>

        @if(isset($yearOverYearChange))
        <div class="alert {{ $yearOverYearChange > 0 ? 'alert-warning' : 'alert-success' }}">
            <div class="alert-icon">{{ $yearOverYearChange > 0 ? '📈' : '📉' }}</div>
            <div class="alert-content">
                <strong>Year-over-Year Change</strong>
                <div>Spending {{ $yearOverYearChange > 0 ? 'increased' : 'decreased' }} by {{ number_format(abs($yearOverYearChange), 1) }}% compared to last year</div>
            </div>
        </div>
        @endif

        <!-- Monthly Breakdown Chart -->
        <div class="chart-container">
            <canvas id="yearlyMonthlyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Health Score Overview -->
    @if($viewMode === 'month' && isset($aiInsights['executive_summary']))
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
    @if($viewMode === 'month' && isset($aiInsights['executive_summary']['urgent_actions']) && count($aiInsights['executive_summary']['urgent_actions']) > 0)
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

    <!-- Positive Highlights -->
    @if($viewMode === 'month' && isset($aiInsights['executive_summary']['positive_highlights']) && count($aiInsights['executive_summary']['positive_highlights']) > 0)
    <div class="alerts-section">
        @foreach($aiInsights['executive_summary']['positive_highlights'] as $highlight)
        <div class="alert alert-success">
            <div class="alert-icon">✓</div>
            <div class="alert-content">
                <strong>Great Job!</strong>
                <div>{{ $highlight }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Key Metrics -->
    @if($viewMode === 'month')
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

        <div class="stat-card">
            <div class="stat-label">Weekend Spending</div>
            <div class="stat-value">M{{ number_format($weekendSpending ?? 0, 2) }}</div>
            <div class="stat-subtitle">M{{ number_format($weekdaySpending ?? 0, 2) }} on weekdays</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Transaction Count</div>
            <div class="stat-value">{{ count($allTransactions ?? []) }}</div>
            <div class="stat-subtitle">Average: M{{ number_format(count($allTransactions ?? []) > 0 ? ($totalExpenses ?? 0) / count(array_filter($allTransactions ?? [], fn($t) => $t['type'] === 'expense')) : 0, 2) }} per transaction</div>
        </div>
    </div>

    <!-- ENHANCED BUDGET VS ACTUAL COMPARISON -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">🎯</div>
                <span>Budget vs Actual Spending</span>
            </div>
        </div>
        <p class="card-subtitle">Visual comparison of budgeted amounts versus actual spending across all categories</p>
        
        <div class="budget-comparison-grid">
            @foreach($categoryBreakdown ?? [] as $category)
            @php
                $budgeted = $category['budget'] ?? 0;
                $spent = $category['expense'] ?? 0;
                $remaining = $budgeted - $spent;
                $percentUsed = $budgeted > 0 ? ($spent / $budgeted) * 100 : 0;
                
                // Determine status
                $status = 'on-track';
                $statusClass = '';
                if ($percentUsed > 100) {
                    $status = 'over-budget';
                    $statusClass = 'over-budget';
                } elseif ($percentUsed > 80) {
                    $status = 'near-limit';
                    $statusClass = 'near-limit';
                }
                
                // Progress bar class
                $progressClass = '';
                if ($percentUsed > 100) {
                    $progressClass = 'danger';
                } elseif ($percentUsed > 80) {
                    $progressClass = 'warning';
                }
            @endphp
            
            <div class="budget-category-card {{ $statusClass }}">
                <div class="budget-category-header">
                    <div class="budget-category-name">{{ $category['name'] }}</div>
                    <div class="budget-status-badge {{ $status }}">
                        {{ $status === 'over-budget' ? 'OVER BUDGET' : ($status === 'near-limit' ? 'NEAR LIMIT' : 'ON TRACK') }}
                    </div>
                </div>
                
                <div class="budget-amounts">
                    <div class="budget-amount-box">
                        <div class="budget-amount-label">Budgeted</div>
                        <div class="budget-amount-value budgeted">M{{ number_format($budgeted, 2) }}</div>
                    </div>
                    <div class="budget-amount-box">
                        <div class="budget-amount-label">Spent</div>
                        <div class="budget-amount-value spent">M{{ number_format($spent, 2) }}</div>
                    </div>
                    <div class="budget-amount-box">
                        <div class="budget-amount-label">{{ $remaining >= 0 ? 'Remaining' : 'Over' }}</div>
                        <div class="budget-amount-value {{ $remaining >= 0 ? 'remaining' : 'overspent' }}">
                            M{{ number_format(abs($remaining), 2) }}
                        </div>
                    </div>
                </div>
                
                <div class="budget-visual-progress">
                    <div class="budget-progress-bar">
                        <div class="budget-progress-fill {{ $progressClass }}" 
                             style="width: {{ min($percentUsed, 100) }}%"></div>
                        @if($budgeted > 0)
                        <div class="budget-progress-marker" style="left: 80%;" title="80% threshold"></div>
                        @endif
                    </div>
                    <div class="budget-progress-labels">
                        <span>0%</span>
                        <span class="budget-percentage">{{ number_format($percentUsed, 1) }}% used</span>
                        <span>100%</span>
                    </div>
                </div>
                
                <div class="budget-transaction-details">
                    <span class="budget-txn-count">{{ $category['transaction_count'] ?? 0 }} transactions</span>
                    <span class="budget-avg-txn">Avg: M{{ number_format($category['avg_transaction'] ?? 0, 2) }}</span>
                    @if(isset($category['vs_average']))
                    <span class="budget-vs-avg {{ $category['vs_average'] > 0 ? 'positive' : 'negative' }}">
                        {{ $category['vs_average'] > 0 ? '+' : '' }}{{ number_format($category['vs_average'], 1) }}% vs avg
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- AI Predictions -->
    @if($viewMode === 'month' && isset($aiInsights['spending_trends']['forecast_next_3_months']) && count($aiInsights['spending_trends']['forecast_next_3_months']) > 0)
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
    @if($viewMode === 'month' && isset($aiInsights['spending_trends']['unusual_spikes']) && count($aiInsights['spending_trends']['unusual_spikes']) > 0)
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

    <!-- Historical Trend Chart -->
    @if($viewMode === 'month')
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
    @endif

    <!-- Category Performance & Weekly Pattern -->
    @if($viewMode === 'month')
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
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
    @endif

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
    @if($viewMode === 'month' && isset($aiInsights['actionable_recommendations']) && count($aiInsights['actionable_recommendations']) > 0)
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
    @if($viewMode === 'month' && isset($aiInsights['behavioral_insights']))
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

    <!-- All Transactions Table - FIXED -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-icon">📝</div>
                <span>All Transactions ({{ count($allTransactions ?? []) }})</span>
            </div>
        </div>
    
        <table id="transactionsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allTransactions ?? [] as $txn)
                <tr>
                    <td class="transaction-date">{{ \Carbon\Carbon::parse($txn['date'])->format('M d, Y g:i A') }}</td>
                    <td class="transaction-desc">{{ $txn['description'] }}</td>
                    <td><span class="transaction-category">{{ $txn['category'] }}</span></td>
                    <td><span class="transaction-type {{ $txn['type'] }}">{{ $txn['type'] }}</span></td>
                    <td class="transaction-amount {{ $txn['type'] }}">
                        {{ $txn['type'] == 'income' ? '+' : '-' }}M{{ number_format($txn['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables with proper configuration
    var table = $('#transactionsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '📋 Copy',
                className: 'dt-button'
            },
            {
                extend: 'csv',
                text: '📊 CSV',
                className: 'dt-button'
            },
            {
                extend: 'excel',
                text: '📗 Excel',
                className: 'dt-button'
            },
            {
                extend: 'pdf',
                text: '📄 PDF',
                className: 'dt-button',
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '🖨️ Print',
                className: 'dt-button'
            }
        ],
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']], // Sort by date descending
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ transactions",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "No transactions available",
            infoFiltered: "(filtered from _MAX_ total transactions)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        columnDefs: [
            { 
                targets: 4, // Amount column
                type: 'num',
                render: function(data, type, row) {
                    if (type === 'sort' || type === 'type') {
                        // Extract number from formatted string for sorting
                        return parseFloat(data.replace(/[^0-9.-]+/g, ''));
                    }
                    return data;
                }
            }
        ]
    });

    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-fill, .budget-progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});

// Chart.js Configuration
Chart.defaults.color = '#6b7280';
Chart.defaults.font.family = "'Manrope', sans-serif";
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 8;

// 12-Month Trend Chart
@if($viewMode === 'month' && isset($monthlyTrend) && count($monthlyTrend) > 0)
const trendCtx = document.getElementById('trendChart');
if (trendCtx) {
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
                        font: { size: 13, weight: '600' }
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
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        callback: function(value) { return 'M' + value; },
                        font: { size: 12, weight: '500' }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { size: 12, weight: '500' } }
                }
            }
        }
    });
}
@endif

// Category Distribution Chart - Horizontal Bar
@if(isset($categoryChart) && count($categoryChart) > 0)
const categoryCtx = document.getElementById('categoryChart');
if (categoryCtx) {
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: @json($categoryChart->pluck('category')),
            datasets: [{
                label: 'Spending',
                data: @json($categoryChart->pluck('amount')),
                backgroundColor: [
                    '#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b',
                    '#fa709a', '#fee140', '#30cfd0', '#a8edea', '#ff6b6b',
                    '#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b'
                ],
                borderWidth: 0,
                borderRadius: 8,
                barThickness: 30
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.x;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `M${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { 
                        color: '#f3f4f6',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) { 
                            return 'M' + value.toFixed(0); 
                        },
                        font: { size: 12, weight: '500' }
                    }
                },
                y: {
                    grid: { 
                        display: false,
                        drawBorder: false
                    },
                    ticks: { 
                        font: { size: 13, weight: '600' },
                        color: '#1a1a1a'
                    }
                }
            }
        }
    });
}
@endif

// Yearly Monthly Breakdown Chart
@if($viewMode === 'year' && isset($yearlyMonthlyBreakdown))
const yearlyCtx = document.getElementById('yearlyMonthlyChart');
if (yearlyCtx) {
    new Chart(yearlyCtx, {
        type: 'bar',
        data: {
            labels: @json(array_column($yearlyMonthlyBreakdown, 'month')),
            datasets: [
                {
                    label: 'Income',
                    data: @json(array_column($yearlyMonthlyBreakdown, 'income')),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10b981',
                    borderWidth: 2
                },
                {
                    label: 'Expenses',
                    data: @json(array_column($yearlyMonthlyBreakdown, 'expenses')),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 2
                },
                {
                    label: 'Savings',
                    data: @json(array_column($yearlyMonthlyBreakdown, 'savings')),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: '#667eea',
                    borderWidth: 2
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
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 13, weight: '600' }
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
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        callback: function(value) { return 'M' + value; },
                        font: { size: 12, weight: '500' }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { size: 12, weight: '500' } }
                }
            }
        }
    });
}
@endif
</script>

@endsection