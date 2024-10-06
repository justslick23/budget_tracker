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
@endsection

@section('scripts')
<!-- Include DataTable initialization -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#incomesTable').DataTable({
        // Optional: Customize DataTable settings here
        "order": [[1, "desc"]], // Default sorting by year
        "lengthMenu": [5, 10, 25, 50], // Page length options
    });
});
</script>
@endsection
