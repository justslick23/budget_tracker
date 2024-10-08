<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
<div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo">
            <h4 class="navbar-brand text-white" style="margin: 0;">
                <i class="fas fa-wallet"></i> Budget Tracker
            </h4>
        </a>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
            </button>
        </div>
    </div>
    <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item active">
                <a href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            <li class="nav-section">
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

            <li class="nav-item">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</div>

<!-- JavaScript Code -->
<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

<!-- jQuery Scrollbar -->
<script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

<!-- Custom Script for Toggle and Logout Confirmation -->
<script>
    $(document).ready(function() {
        // Toggle sidebar
        $('.toggle-sidebar').on('click', function() {
            $('.sidebar').toggleClass('active');
        });

        // Handle collapse functionality for submenus
        $('.nav-item a[data-bs-toggle="collapse"]').on('click', function(e) {
            e.preventDefault(); // Prevent default anchor behavior
            var target = $(this).attr('href');
            $(target).collapse('toggle');
            $(target).siblings('.collapse.show').collapse('hide'); // Close other open menus
        });

        // Show sidebar in mobile view
        $(window).on('resize', function() {
            if ($(window).width() < 768) {
                $('.sidebar').removeClass('active'); // Ensure sidebar is hidden on small screens
            }
        });

        // Confirm logout
        $('#logout-form').on('submit', function() {
            return confirm('Are you sure you want to logout?');
        });
    });
</script>


<style>
    .sidebar {
        transition: width 0.3s ease; /* Add easing for smooth transition */
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
            transition: left 0.3s ease; /* Smooth transition */
        }

        .sidebar.active {
            left: 0; /* Show sidebar */
        }
    }
</style>
