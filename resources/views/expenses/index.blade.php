@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Expenses</h2>
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
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Expense
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped" id = "expensesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $index => $expense)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>M{{ number_format($expense->amount, 2) }}</td>
                        <td>    <span class="badge bg-primary">{{ $expense->category->name }}</span>
                        </td> <!-- Accessing category name -->
                        <td>{{ $expense->description }}</td>
                        <td>{{ \Carbon\Carbon::parse($expense->date)->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table {
        border-radius: 10px; /* Rounded corners */
        overflow: hidden; /* Ensure the corners are rounded */
    }
    .table th, .table td {
        vertical-align: middle; /* Center align text vertically */
    }
</style>
@endpush
@section('scripts')
<!-- Include DataTable initialization -->
<!-- DataTables CSS -->
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
    $('#expensesTable').DataTable({
        // Optional: Customize DataTable settings here
        responsive: true,

        "order": [[1, "desc"]], // Default sorting by year
        "lengthMenu": [5, 10, 25, 50], // Page length options
    });
});
</script>
@endsection

