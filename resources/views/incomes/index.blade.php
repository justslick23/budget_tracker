@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Incomes</h1>
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
        <a href="{{ route('incomes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Income
        </a>
    </div>

    <div class="table-responsive">
    <table class="table" id = "incomesTable">
        <thead>
            <tr>
                <th>Source</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incomes as $income)
            <tr>
                <td>{{ $income->source }}</td>
                <td>M{{ number_format($income->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($income->date)->format('Y-m-d') }}</td>
                <td>
                    <a href="#" class="btn btn-info">Edit</a>
                    <form action="#" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
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
    $('#incomesTable').DataTable({
        // Optional: Customize DataTable settings here
        responsive: true,

        "order": [[1, "desc"]], // Default sorting by year
        "lengthMenu": [5, 10, 25, 50], // Page length options
    });
});
</script>
@endsection
