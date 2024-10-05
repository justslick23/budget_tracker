<nav id="sidebar" class="sidebar js-sidebar fixed-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="#">
            <span class="align-middle">Budget Tracker</span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">
                Dashboard
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="align-middle">Overview</span>
                </a>
            </li>

            <li class="sidebar-header">
                Transactions
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#transactionsCollapse" aria-expanded="false" aria-controls="transactionsCollapse">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="align-middle">Manage Transactions</span>
                </a>
                <div id="transactionsCollapse" class="collapse">
                    <ul class="sidebar-nav">
                        <li class="sidebar-item active">
                            <a class="sidebar-link" href="{{ route('expenses.index') }}">
                                <i class="fas fa-dollar-sign"></i>
                                <span class="align-middle">Expenses</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('expenses.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span class="align-middle">Add Expense</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('incomes.index') }}">
                                <i class="fas fa-arrow-up"></i>
                                <span class="align-middle">Income</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('incomes.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span class="align-middle">Add Income</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-header">
                Budgets
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#budgetsCollapse" aria-expanded="false" aria-controls="budgetsCollapse">
                    <i class="fas fa-wallet"></i>
                    <span class="align-middle">Manage Budgets</span>
                </a>
                <div id="budgetsCollapse" class="collapse">
                    <ul class="sidebar-nav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('budgets.index') }}">
                                <i class="fas fa-wallet"></i>
                                <span class="align-middle">Budgets</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('budgets.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span class="align-middle">Add Budget</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-header">
                Categories
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#categoriesCollapse" aria-expanded="false" aria-controls="categoriesCollapse">
                    <i class="fas fa-tags"></i>
                    <span class="align-middle">Manage Categories</span>
                </a>
                <div id="categoriesCollapse" class="collapse">
                    <ul class="sidebar-nav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('categories.index') }}">
                                <i class="fas fa-list"></i>
                                <span class="align-middle">Categories</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route('categories.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                <span class="align-middle">Add Category</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-header">
                Reports
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#reportsCollapse" aria-expanded="false" aria-controls="reportsCollapse">
                    <i class="fas fa-chart-bar"></i>
                    <span class="align-middle">Reports</span>
                </a>
                <div id="reportsCollapse" class="collapse">
                    <ul class="sidebar-nav">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="#" onclick="return false;">
                                <i class="fas fa-chart-line"></i>
                                <span class="align-middle">Monthly Report</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="#" onclick="return false;">
                                <i class="fas fa-chart-pie"></i>
                                <span class="align-middle">Category Breakdown</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-header">
                Settings
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="#" onclick="return false;">
                    <i class="fas fa-cog"></i>
                    <span class="align-middle">Profile Settings</span>
                </a>
            </li>
            <ul>
    <!-- Other sidebar items -->
    
    <li class="sidebar-item">
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <a class="sidebar-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span class="align-middle">Log Out</span>
        </a>
    </li>
</ul>


     
    </div>
</nav>

<style>
    /* Sidebar Styles */
.fixed-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px; /* Adjust width as per your layout */
    height: 100%;
    overflow-y: auto; /* Add scroll if content overflows */
    background-color: #f8f9fa; /* Light background for contrast */
    z-index: 1000; /* Ensure it stays on top */
    border-right: 1px solid #e0e0e0; /* Optional: adds a border for separation */
}

.sidebar-header {
    font-size: 1.2em; /* Increase font size for headers */
    padding: 15px; /* Add padding for better spacing */
    color: #6c757d; /* Use a muted color for headers */
}

.sidebar-item.active .sidebar-link {
    background-color: #007bff; /* Highlight active link */
    color: white; /* Change text color for active link */
}

.sidebar-link {
    color: #495057; /* Standard link color */
    transition: background-color 0.2s, color 0.2s; /* Smooth transition */
    padding: 10px 15px; /* Add padding for clickable area */
    border-radius: 4px; /* Add border radius for smooth corners */
}

.sidebar-link:hover {
    background-color: #e9ecef; /* Background change on hover */
    color: #007bff; /* Change color on hover */
}

.collapse .sidebar-nav {
    padding-left: 15px; /* Indent nested items */
}

.sidebar-cta {
    padding: 15px;
    background-color: #f1f1f1; /* Light background for CTA section */
}

.sidebar-cta-content {
    text-align: center; /* Center align content */
}

.sidebar-cta-content strong {
    font-size: 1.1em; /* Slightly increase font size for emphasis */
}

</style>