@extends('layouts.app')

@section('content')
<main class="content">
    <div class="container">
        <h1 class="h3 mb-3">Edit Category</h1>
        
        <form method="POST" action="{{ route('categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" name="name" id="name" 
                       class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

      
            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>
</main>
@endsection
