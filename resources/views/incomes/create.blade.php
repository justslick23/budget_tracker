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
    <input type="checkbox" name="include_in_budget" id="include_in_budget" value="1" onchange="toggleBudgetCategory()">
</div>

<div class="form-group" id="budget_category_div" style="display: none;">
    <label for="budget_id">Select Budget Category</label>
    <select class="form-control" name="budget_id" id="budget_id">
        <option value="" disabled selected>Select a budget category</option>
        @foreach ($userBudgets as $budget)
            <option value="{{ $budget->id }}">{{ $budget->category->name }} - {{ $budget->year }}/{{ $budget->month }}</option>
        @endforeach
    </select>
</div>


        <button type="submit" class="btn btn-success">Add Income</button>
    </form>
</div>
@endsection
<script>
function toggleBudgetCategory() {
    const includeInBudget = document.getElementById('include_in_budget').checked;
    const budgetCategoryDiv = document.getElementById('budget_category_div');
    budgetCategoryDiv.style.display = includeInBudget ? 'block' : 'none';
}
</script>


