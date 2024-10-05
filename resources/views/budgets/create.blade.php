@extends('layouts.app')

@section('content')
<main class="content">
    <div class="container">
        <h1 class="h3 mb-3">Create Budget</h1>
        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" name="year" id="year" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-control" required>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Budget Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Budget</button>
        </form>
    </div>
</main>
@endsection
