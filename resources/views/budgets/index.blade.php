@extends('layouts.app') <!-- Use your layout file -->

@section('content')
<div class="container">
    <h1 class="mb-4">Budgets</h1>

    <div class="mb-3">
        <a href="{{ route('budgets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Budget
        </a>
    </div>

    <table class="table ">
        <thead>
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Allocated Amount</th>
                <th>Total Expenses</th>
                <th>Remaining Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($budgets as $budget)
            <tr>
            <td>{{ \Carbon\Carbon::create()->month($budget->month)->format('F') }}</td> <!-- Display full month name -->
                <td>{{ $budget->year }}</td>
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

    {{ $budgets->links() }} <!-- Pagination links -->
</div>
@endsection

@section('scripts')
<!-- Include any additional scripts here -->
@endsection
