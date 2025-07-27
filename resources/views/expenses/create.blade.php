@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4 text-primary fw-bold">Add New Expense</h2>

    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                {{-- Amount --}}
                <div class="mb-4">
                    <label for="amount" class="form-label fw-semibold">Amount (M)</label>
                    <input type="number" step="0.01" placeholder="Enter amount"
                        class="form-control @error('amount') is-invalid @enderror"
                        id="amount" name="amount" required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label for="category_id" class="form-label fw-semibold">Category</label>
                    <select class="form-select @error('category_id') is-invalid @enderror"
                        id="category_id" name="category_id" required>
                        <option value="" disabled selected>Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <input type="text" placeholder="Enter a brief description"
                        list="pastDescriptions"
                        class="form-control @error('description') is-invalid @enderror"
                        id="description" name="description" required>
                    <datalist id="pastDescriptions">
                        @foreach ($pastDescriptions as $description)
                            <option value="{{ $description }}"></option>
                        @endforeach
                    </datalist>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date --}}
                <div class="mb-4">
                    <label for="date" class="form-label fw-semibold">Date</label>
                    <input type="date"
                        class="form-control @error('date') is-invalid @enderror"
                        id="date" name="date" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Recurring Option --}}
                <div class="border rounded p-3 mb-4 bg-light-subtle">
                    <div class="form-check mb-3">
                        <input type="checkbox"
                            class="form-check-input"
                            id="recurring"
                            name="recurring"
                            value="1">
                        <label class="form-check-label fw-semibold" for="recurring">
                            Mark as Recurring (Monthly)
                        </label>
                    </div>

                    <div id="recurring-day" style="display: none;">
                        <label for="day_of_month" class="form-label">Day of Month</label>
                        <input type="number"
                               class="form-control @error('day_of_month') is-invalid @enderror"
                               id="day_of_month"
                               name="day_of_month"
                               min="1" max="31"
                               placeholder="Enter day of month (1–31)">
                        @error('day_of_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- JS to toggle recurring day --}}
                <script>
                    document.getElementById('recurring').addEventListener('change', function () {
                        document.getElementById('recurring-day').style.display = this.checked ? 'block' : 'none';
                    });
                </script>

<script>
    const recurringCheckbox = document.getElementById('recurring');
    const recurringDayDiv = document.getElementById('recurring-day');
    const dateInput = document.getElementById('date');

    function toggleRecurringFields() {
        if (recurringCheckbox.checked) {
            recurringDayDiv.style.display = 'block';
            dateInput.disabled = true;
            dateInput.required = false;
        } else {
            recurringDayDiv.style.display = 'none';
            dateInput.disabled = false;
            dateInput.required = true;
        }
    }

    recurringCheckbox.addEventListener('change', toggleRecurringFields);

    // On page load, apply initial state (for edit forms or old input)
    window.addEventListener('DOMContentLoaded', toggleRecurringFields);
</script>


                {{-- Actions --}}
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary px-4">Add Expense</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
