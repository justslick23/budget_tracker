@extends('layouts.app')

@section('content')
<main class="content">
    <div class="container">
        <h1 class="h3 mb-3">Create Budget</h1>
        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-control" required>
                    @php
                        $currentYear = date('Y');
                    @endphp
                    @for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="mb-3">
    <label for="month" class="form-label">Month</label>
    <select name="month" id="month" class="form-control" required>
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" 
                @if ($i == date('n')) selected @endif>
                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
            </option>
        @endfor
    </select>
</div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    <option value="" disabled selected>Select a category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
