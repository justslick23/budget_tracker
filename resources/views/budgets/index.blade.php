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
            @foreach ($budgets as $budget)
            <tr>
                <td>{{ \Carbon\Carbon::create()->month($budget->month)->format('F') }}</td> <!-- Full month name -->
                <td>{{ $budget->year }}</td>
                <td>{{ $budget->category->name }}</td> <!-- Assuming category has a 'name' attribute -->
                <td>M{{ number_format($budget->amount, 2) }}</td>
                <td>M{{ number_format($budget->spent, 2) }}</td>
                <td>M{{ number_format($budget->amount - $budget->spent, 2) }}</td>
                <td>
                    <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" style="display:inline-block;">
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
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#budgetsTable').DataTable({
            // You can customize the DataTable options here
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
