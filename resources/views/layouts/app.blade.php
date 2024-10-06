<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Dashboard')</title>

    <!-- AdminKit CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="img/icons/icon-48x48.png" />
	<link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        #sidebar-wrapper {
            width: 250px; /* Set your desired width */
            transition: all 0.3s ease; /* Smooth transition */
        }

        #sidebar-wrapper.toggled {
            width: 0; /* Collapsed state */
            overflow: hidden; /* Prevent content overflow */
        }
    </style>

</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            @include('layouts.sidebar') <!-- Include your sidebar layout here -->
        </div>

        <!-- Page content wrapper -->
        <div id="page-content-wrapper" class="w-100">
            <div class="container-fluid">
                <!-- Include Navbar -->
                @include('layouts.navbar') <!-- Include your navbar layout here -->

                <main class="content">
                    @yield('content')
                </main>
            </div>

            @yield('scripts')
            <!-- Footer -->
        </div>
    </div>

    <!-- AdminKit JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Toggle sidebar
            $('.js-sidebar-toggle').on('click', function () {
                $('#sidebar-wrapper').toggleClass('toggled');
            });
        });
    </script>
</body>
</html>
