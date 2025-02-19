@extends('layouts.app') <!-- Use your layout file -->

@section('content')
<div class="container">
    <h1 class="mb-4">Budgets</h1>
  <!-- Success Message Banner -->
  @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Budget
        </a>
    </div>

    <div class="table-responsive">
    <table class="table" id = "budgetsTable">
        <thead>
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Category</th>
                <th>Allocated Amount</th>
                <th>Total Spent</th>
                <th>Remaining Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($budgetSummary as $summary)
            <tr>
            <td>{{ \Carbon\Carbon::create()->month($summary['month'])->format('F') }}</td> <!-- Full month name -->
                    <td>{{ $summary['year'] }}</td>
                    <td>{{ $summary['category'] }}</td> <!-- Category name -->
                    <td>M{{ $summary['allocated_amount'] }}</td>
                    <td>M{{ $summary['total_spent'] }}</td>
                    <td>M{{ $summary['remaining_balance'] }}</td> <!-- Remaining balance -->
                <td>
                    <a href="{{ route('budgets.edit', $summary['id']) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('budgets.destroy', $summary['id']) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this budget?');">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

   

</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- DataTables Responsive CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- DataTables Responsive JS -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
        $('#budgetsTable').DataTable({
            // You can customize the DataTable options here
            responsive: true,
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[3, "desc"]], // Order by date column
            "language": {
                "emptyTable": "No transactions available"
            }
        });
    });
</script>
@endsection
