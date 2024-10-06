<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo">
                <img
                    src="{{ asset('assets/img/logo.svg') }}" 
                    alt="navbar brand"
                    class="navbar-brand"
                    height="20"
                />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-item active">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Transactions</h4>
                </li>

                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#expenses" role="button" aria-expanded="false" aria-controls="expenses">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Expenses</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="expenses">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('expenses.index') }}">
                                    <span class="sub-item">View Expenses</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('expenses.create') }}">
                                    <span class="sub-item">Add Expense</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#incomes" role="button" aria-expanded="false" aria-controls="incomes">
                        <i class="fas fa-dollar-sign"></i>
                        <p>Incomes</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="incomes">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('incomes.index') }}">
                                    <span class="sub-item">View Incomes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('incomes.create') }}">
                                    <span class="sub-item">Add Income</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#budget" role="button" aria-expanded="false" aria-controls="budget">
                        <i class="fas fa-wallet"></i>
                        <p>Budget</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="budget">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('budgets.index') }}">
                                    <span class="sub-item">View Budget</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('budgets.create') }}">
                                    <span class="sub-item">Add Budget</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#categories" role="button" aria-expanded="false" aria-controls="categories">
                        <i class="fas fa-tags"></i>
                        <p>Categories</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="categories">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('categories.index') }}">
                                    <span class="sub-item">View Categories</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('categories.create') }}">
                                    <span class="sub-item">Add Category</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript Code -->
<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

<!-- jQuery Scrollbar -->
<script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

<!-- Custom Script for Toggle -->
<script>
    $(document).ready(function() {
        // Toggle sidebar
        $('.toggle-sidebar').on('click', function() {
            $('.sidebar').toggleClass('active');
        });

        // Handle collapse functionality for submenus
        $('.nav-item a[data-bs-toggle="collapse"]').on('click', function() {
            var target = $(this).attr('href');
            $(target).collapse('toggle');
        });

        // Show sidebar in mobile view
        $(window).on('resize', function() {
            if ($(window).width() < 768) {
                $('.sidebar').removeClass('active'); // Ensure sidebar is hidden on small screens
            }
        });
    });
</script>

<!-- Custom CSS for Sidebar -->
<style>
    .sidebar {
        transition: width 0.3s;
        overflow: hidden; /* Prevent overflow */
    }

    .sidebar.active {
        width: 60px; /* Set to your desired collapsed width */
    }

    .sidebar.active .nav-item p {
        display: none; /* Hide text in collapsed state */
    }

    .sidebar.active .nav-item i {
        margin: 0 auto; /* Center icons when collapsed */
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sidebar {
            position: fixed; /* Fix sidebar in place on mobile */
            left: -250px; /* Hide it offscreen */
            width: 250px; /* Width of the sidebar when visible */
            transition: left 0.3s; /* Smooth transition */
        }
        .sidebar.active {
            left: 0; /* Show sidebar */
        }
    }
</style>
