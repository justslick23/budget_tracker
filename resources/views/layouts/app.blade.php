<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Budget-Tracker')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Mona+Sans:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <!-- endinject -->
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMm7P/4s4/c1A7yxeK1ukbF0aAO9n/0KnL7pdp" crossorigin="anonymous">
    
    <!-- DataTables Core CSS (Single Version - 1.13.6) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <!-- DataTables Buttons Extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- endinject -->
    
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
</head>

<body>
    <div class="container-scroller">
        @include('layouts.navbar') <!-- Include your navbar here -->

        <div class="main-container-fluid page-body-wrapper">
            @include('layouts.sidebar') <!-- Include your sidebar here -->
            
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="content">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SCRIPTS - PROPER ORDER ==================== -->
    
    <!-- 1. jQuery FIRST (Required by all other plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- 2. Vendor Bundle (Bootstrap, Popper, etc.) -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    
    <!-- 3. Chart.js (For dashboard charts) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- 4. Moment.js (Required for Date Range Picker) -->
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    
    <!-- 5. Date Range Picker -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
    <!-- 6. DataTables Core (Single Version - 1.13.6) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <!-- 7. DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <!-- 8. JSZip (Required for Excel export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    
    <!-- 9. pdfMake (Required for PDF export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <!-- 10. Local DataTables files (if needed) -->
    <script src="{{ asset('assets/vendors/datatables/net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatables/net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.select.min.js') }}"></script>
    
    <!-- 11. Template Scripts -->
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    
    <!-- 12. Custom Dashboard Scripts -->
    <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    
    <!-- 13. Page-specific scripts from child templates -->
    @yield('scripts')
</body>

</html>