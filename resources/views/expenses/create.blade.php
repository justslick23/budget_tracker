@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Add New Expense</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (M)</label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" required step="0.01" placeholder="Enter amount">
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
    <label for="category_id" class="form-label">Category</label>
    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
        <option value="" disabled selected>Select a category</option>
        @foreach ($categories as $category) <!-- Assuming you pass $categories to your view -->
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>



<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <input class="form-control @error('description') is-invalid @enderror" 
           id="description" 
           name="description" 
           list="pastDescriptions" 
           placeholder="Enter a brief description" 
           required>
    
    <datalist id="pastDescriptions">
        @foreach ($pastDescriptions as $description)
            <option value="{{ $description }}"></option>
        @endforeach
    </datalist>
    
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Add Expense</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
