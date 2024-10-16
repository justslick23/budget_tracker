<!-- Sidebar -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item active">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="ti-home menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            <br><br>

            <!-- Categories -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#categories" aria-expanded="false" aria-controls="categories">
                    <i class="ti-tag menu-icon"></i>
                    <span class="menu-title">Categories</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="categories">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('categories.index') }}">View Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('categories.create') }}">Add Category</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Budget -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#budget" aria-expanded="false" aria-controls="budget">
                    <i class="ti-wallet menu-icon"></i>
                    <span class="menu-title">Budget</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="budget">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('budgets.index') }}">View Budget</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('budgets.create') }}">Add Budget</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Incomes -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#incomes" aria-expanded="false" aria-controls="incomes">
                    <i class="ti-stats-up menu-icon"></i>
                    <span class="menu-title">Incomes</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="incomes">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('incomes.index') }}">View Incomes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('incomes.create') }}">Add Income</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Expenses -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#expenses" aria-expanded="false" aria-controls="expenses">
                    <i class="ti-money menu-icon"></i>
                    <span class="menu-title">Expenses</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="expenses">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('expenses.index') }}">View Expenses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('expenses.create') }}">Add Expense</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="ti-power-off menu-icon"></i>
                    <span class="menu-title">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>
