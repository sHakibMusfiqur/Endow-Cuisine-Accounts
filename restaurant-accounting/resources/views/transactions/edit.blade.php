@extends('layouts.app')

@section('title', 'Edit Transaction - Restaurant Accounting')
@section('page-title', 'Edit Transaction')

@section('content')
<style>
    /* Mobile Responsive Styles for Transaction Forms */
    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }

        .col-md-8 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0;
        }

        .card {
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .card-header h5 {
            font-size: 1rem;
        }

        .card-body {
            padding: 15px;
        }

        .form-label {
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            font-size: 16px; /* Prevents zoom on iOS */
            padding: 10px 12px;
        }

        .input-group-text {
            font-size: 0.9rem;
            padding: 10px 12px;
        }

        /* Transaction type buttons */
        .btn-group {
            display: flex;
            flex-direction: row;
        }

        .transaction-type-btn {
            flex: 1;
            padding: 12px 10px;
            font-size: 0.9rem;
        }

        /* Quill Editor Adjustments */
        #description-editor {
            min-height: 120px;
        }

        .ql-editor {
            min-height: 100px;
            font-size: 0.95rem;
        }

        .ql-toolbar {
            padding: 6px 8px;
        }

        /* Form action buttons */
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
            justify-content: center;
        }

        /* Reverse button order on mobile for better UX */
        .d-flex.justify-content-between {
            flex-direction: column-reverse;
        }
    }

    @media (max-width: 480px) {
        .card-body {
            padding: 12px;
        }

        .form-label {
            font-size: 0.85rem;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            padding: 8px 10px;
        }

        .transaction-type-btn {
            padding: 10px 8px;
            font-size: 0.85rem;
        }

        .btn {
            padding: 10px 15px;
            font-size: 0.9rem;
        }
    }

    /* Touch device optimizations */
    @media (hover: none) and (pointer: coarse) {
        .form-control,
        .form-select {
            min-height: 44px;
        }

        .btn {
            min-height: 44px;
        }

        .transaction-type-btn {
            min-height: 48px;
        }
    }
</style>

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
                            <div id="description-editor" class="form-control @error('description') is-invalid @enderror" style="height: 150px; border: 1px solid #ced4da; border-radius: 0.375rem;"></div>
                            <input type="hidden" name="description" id="description" value="{{ old('description', $transaction->description) }}">
                            @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden currency field - System uses only KRW -->
                        <input type="hidden" name="currency_id" value="{{ $transaction->currency_id }}">

                        <div class="mb-3">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group" aria-label="Transaction Type">
                                <button type="button" class="btn btn-outline-success transaction-type-btn {{ ($transaction->income > 0 || old('transaction_type') == 'income') ? 'active' : '' }}" data-type="income">
                                    <i class="fas fa-arrow-up"></i> Income
                                </button>
                                <button type="button" class="btn btn-outline-danger transaction-type-btn {{ ($transaction->expense > 0 || old('transaction_type') == 'expense') ? 'active' : '' }}" data-type="expense">
                                    <i class="fas fa-arrow-down"></i> Expense
                                </button>
                            </div>
                            <input type="hidden" name="transaction_type" id="transaction_type" value="{{ old('transaction_type', $transaction->income > 0 ? 'income' : 'expense') }}">
                        </div>

                        <div class="mb-3" id="income_section">
                            <label for="income" class="form-label">Income Amount (KRW) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₩</span>
                                <input type="number" class="form-control @error('income') is-invalid @enderror" 
                                       id="income" name="income" value="{{ old('income', $transaction->income > 0 ? $transaction->income : '') }}" step="0.01" min="0" placeholder="Enter income amount">
                            </div>
                            @error('income')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="expense_section">
                            <label for="expense" class="form-label">Expense Amount (KRW) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₩</span>
                                <input type="number" class="form-control @error('expense') is-invalid @enderror" 
                                       id="expense" name="expense" value="{{ old('expense', $transaction->expense > 0 ? $transaction->expense : '') }}" step="0.01" min="0" placeholder="Enter expense amount">
                            </div>
                            @error('expense')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="" disabled selected>Select Category</option>
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

@push('styles')
<!-- Quill CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 120px;
        font-size: 1rem;
    }
    .ql-container {
        font-family: inherit;
    }
    #description-editor {
        padding: 0;
        overflow: hidden;
    }
    #description-editor .ql-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        border-bottom: none;
    }
    #description-editor .ql-container {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        border-top: none;
    }
    /* Transaction Type Toggle Buttons */
    .transaction-type-btn.active.btn-outline-success {
        background-color: #198754;
        color: white;
        border-color: #198754;
    }
    .transaction-type-btn.active.btn-outline-danger {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<!-- Quill JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill Editor
        const quill = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Enter transaction details...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Set initial content if editing or validation failed
        const hiddenInput = document.getElementById('description');
        if (hiddenInput.value) {
            quill.root.innerHTML = hiddenInput.value;
        }

        // Update hidden input on text change
        quill.on('text-change', function() {
            const content = quill.root.innerHTML;
            // Check if editor is empty (only contains <p><br></p> or similar)
            const text = quill.getText().trim();
            if (text.length === 0) {
                hiddenInput.value = '';
            } else {
                hiddenInput.value = content;
            }
        });

        // Update hidden input before form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const content = quill.root.innerHTML;
            const text = quill.getText().trim();
            
            // Update hidden field
            if (text.length === 0) {
                hiddenInput.value = '';
            } else {
                hiddenInput.value = content;
            }
            
            // Validate that description is not empty
            if (text.length === 0) {
                e.preventDefault();
                alert('Please enter a description for the transaction.');
                quill.focus();
                return false;
            }
        });

        // Categories data from server
        const categoriesData = @json($categories);
        
        // DOM elements
        const transactionTypeButtons = document.querySelectorAll('.transaction-type-btn');
        const transactionTypeInput = document.getElementById('transaction_type');
        const categorySelect = document.getElementById('category_id');
        const incomeSection = document.getElementById('income_section');
        const expenseSection = document.getElementById('expense_section');
        const incomeInput = document.getElementById('income');
        const expenseInput = document.getElementById('expense');

        // Store old/current category value for restoration
        const savedCategoryId = "{{ old('category_id', $transaction->category_id) }}";

        /**
         * Filter and populate category dropdown based on transaction type
         */
        function loadCategories(transactionType) {
            // Get the first option (placeholder) to preserve it
            const placeholder = categorySelect.options[0];
            
            // Clear all options
            categorySelect.innerHTML = '';
            
            // Re-add the placeholder
            categorySelect.appendChild(placeholder);
            
            // Filter categories by type
            const filteredCategories = categoriesData.filter(cat => cat.type === transactionType);
            
            // Add filtered categories to dropdown
            filteredCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                
                // Restore saved value
                if (savedCategoryId && savedCategoryId == category.id) {
                    option.selected = true;
                }
                
                categorySelect.appendChild(option);
            });
        }

        /**
         * Handle transaction type button clicks
         */
        transactionTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                transactionTypeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get transaction type
                const transactionType = this.dataset.type;
                
                // Update hidden input
                transactionTypeInput.value = transactionType;
                
                // Update form sections
                updateFormSections(transactionType);
                
                // Load categories for selected type
                loadCategories(transactionType);
            });
        });

        /**
         * Update form sections based on transaction type
         */
        function updateFormSections(transactionType) {
            if (transactionType === 'income') {
                incomeSection.style.display = 'block';
                expenseSection.style.display = 'none';
                incomeInput.required = true;
                expenseInput.required = false;
                if (!expenseInput.value || expenseInput.value == '0') {
                    expenseInput.value = '';
                }
            } else if (transactionType === 'expense') {
                incomeSection.style.display = 'none';
                expenseSection.style.display = 'block';
                incomeInput.required = false;
                expenseInput.required = true;
                if (!incomeInput.value || incomeInput.value == '0') {
                    incomeInput.value = '';
                }
            }
        }

        // Initialize on page load
        const initialTransactionType = transactionTypeInput.value;
        
        // Set correct button as active
        transactionTypeButtons.forEach(btn => {
            if (btn.dataset.type === initialTransactionType) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        updateFormSections(initialTransactionType);
        loadCategories(initialTransactionType);
    });
</script>
@endpush
@endsection
