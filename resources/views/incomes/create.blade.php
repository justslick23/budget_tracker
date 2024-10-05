@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Income</h1>

    <form action="{{ route('incomes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="source" class="form-label">Source</label>
            <input type="text" class="form-control" id="source" name="source" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="form-group">
        <label for="include_in_budget">Include in Current Budget</label>
        <input type="checkbox" name="include_in_budget" id="include_in_budget" value="1">
    </div>
        <button type="submit" class="btn btn-success">Add Income</button>
    </form>
</div>
@endsection
