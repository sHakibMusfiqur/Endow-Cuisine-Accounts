@extends('layouts.app')

@section('title', 'Edit Transaction - Restaurant Accounting')
@section('page-title', 'Edit Transaction')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-edit"></i> Edit Transaction</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('transactions.update', $transaction) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                   id="date" name="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                            @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ old('description', $transaction->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="transaction_type" id="type_income" value="income" 
                                       {{ ($transaction->income > 0 || old('transaction_type') == 'income') ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="type_income">
                                    <i class="fas fa-arrow-up"></i> Income
                                </label>

                                <input type="radio" class="btn-check" name="transaction_type" id="type_expense" value="expense"
                                       {{ ($transaction->expense > 0 || old('transaction_type') == 'expense') ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger" for="type_expense">
                                    <i class="fas fa-arrow-down"></i> Expense
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="income_section">
                            <label for="income" class="form-label">Income Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control @error('income') is-invalid @enderror" 
                                       id="income" name="income" value="{{ old('income', $transaction->income) }}" step="0.01" min="0">
                            </div>
                            @error('income')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="expense_section">
                            <label for="expense" class="form-label">Expense Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control @error('expense') is-invalid @enderror" 
                                       id="expense" name="expense" value="{{ old('expense', $transaction->expense) }}" step="0.01" min="0">
                            </div>
                            @error('expense')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <optgroup label="Income Categories" id="income_categories">
                                    @foreach($incomeCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense Categories" id="expense_categories">
                                    @foreach($expenseCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method_id') is-invalid @enderror" 
                                    id="payment_method_id" name="payment_method_id" required>
                                <option value="">Select Payment Method</option>
                                @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('payment_method_id', $transaction->payment_method_id) == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('payment_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const incomeRadio = document.getElementById('type_income');
        const expenseRadio = document.getElementById('type_expense');
        const incomeSection = document.getElementById('income_section');
        const expenseSection = document.getElementById('expense_section');
        const incomeCategories = document.getElementById('income_categories');
        const expenseCategories = document.getElementById('expense_categories');
        const incomeInput = document.getElementById('income');
        const expenseInput = document.getElementById('expense');

        function updateForm() {
            if (incomeRadio.checked) {
                incomeSection.style.display = 'block';
                expenseSection.style.display = 'none';
                incomeCategories.style.display = 'block';
                expenseCategories.style.display = 'none';
                incomeInput.required = true;
                expenseInput.required = false;
                expenseInput.value = 0;
            } else if (expenseRadio.checked) {
                incomeSection.style.display = 'none';
                expenseSection.style.display = 'block';
                incomeCategories.style.display = 'none';
                expenseCategories.style.display = 'block';
                incomeInput.required = false;
                expenseInput.required = true;
                incomeInput.value = 0;
            }
        }

        incomeRadio.addEventListener('change', updateForm);
        expenseRadio.addEventListener('change', updateForm);

        // Initialize on page load
        updateForm();
    });
</script>
@endpush
@endsection
