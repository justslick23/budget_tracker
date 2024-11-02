@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Income</h1>

    <form action="{{ route('incomes.update', $income->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="source" class="form-label">Source</label>
            <input type="text" class="form-control @error('source') is-invalid @enderror" 
                   id="source" name="source" value="{{ old('source', $income->source) }}" required>
            @error('source')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                   id="amount" name="amount" value="{{ old('amount', $income->amount) }}" 
                   required step="0.01">
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                   id="date" name="date" value="{{ old('date', $income->date) }}" required>
            @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group form-check">
            <label for="include_in_budget">Include in Current Budget</label>
            <input type="checkbox" name="include_in_budget" id="include_in_budget" 
                   value="1" {{ old('include_in_budget', $income->include_in_budget) ? 'checked' : '' }}>
        </div>

        <button type="submit" class="btn btn-primary">Update Income</button>
        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
