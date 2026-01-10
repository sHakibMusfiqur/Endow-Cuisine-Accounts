@extends('layouts.app')

@section('title', 'Create Transaction - Restaurant Accounting')
@section('page-title', 'Create Transaction')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle"></i> Add New Transaction</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                   id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <div id="description-editor" class="form-control @error('description') is-invalid @enderror" style="height: 150px; border: 1px solid #ced4da; border-radius: 0.375rem;"></div>
                            <input type="hidden" name="description" id="description" value="{{ old('description') }}">
                            @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency_id') is-invalid @enderror" 
                                    id="currency_id" name="currency_id" required>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" 
                                        data-symbol="{{ $currency->symbol }}"
                                        data-rate="{{ $currency->exchange_rate }}"
                                        {{ (old('currency_id', $defaultCurrency->id ?? '') == $currency->id) ? 'selected' : '' }}>
                                    {{ $currency->name }} ({{ $currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Amounts will be automatically converted to KRW (₩)
                            </small>
                            @error('currency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group" aria-label="Transaction Type">
                                <button type="button" class="btn btn-outline-success transaction-type-btn active" data-type="income">
                                    <i class="fas fa-arrow-up"></i> Income
                                </button>
                                <button type="button" class="btn btn-outline-danger transaction-type-btn" data-type="expense">
                                    <i class="fas fa-arrow-down"></i> Expense
                                </button>
                            </div>
                            <input type="hidden" name="transaction_type" id="transaction_type" value="{{ old('transaction_type', 'income') }}">
                        </div>

                        <div class="mb-3" id="income_section" style="display: none;">
                            <label for="income" class="form-label">Income Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="income_currency_symbol">₩</span>
                                <input type="number" class="form-control @error('income') is-invalid @enderror" 
                                       id="income" name="income" value="{{ old('income') }}" step="0.01" min="0" placeholder="Enter income amount">
                            </div>
                            <small class="text-muted" id="income_conversion" style="display: none;">
                                <i class="fas fa-exchange-alt"></i> Will be converted to: <span id="income_krw_amount">₩0.00</span>
                            </small>
                            @error('income')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="expense_section" style="display: none;">
                            <label for="expense" class="form-label">Expense Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="expense_currency_symbol">₩</span>
                                <input type="number" class="form-control @error('expense') is-invalid @enderror" 
                                       id="expense" name="expense" value="{{ old('expense') }}" step="0.01" min="0" placeholder="Enter expense amount">
                            </div>
                            <small class="text-muted" id="expense_conversion" style="display: none;">
                                <i class="fas fa-exchange-alt"></i> Will be converted to: <span id="expense_krw_amount">₩0.00</span>
                            </small>
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
                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
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
                                <i class="fas fa-save"></i> Save Transaction
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
        border: none !important;
    }
    #description-editor .ql-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }
    #description-editor .ql-container {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
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
        const currencySelect = document.getElementById('currency_id');
        const incomeCurrencySymbol = document.getElementById('income_currency_symbol');
        const expenseCurrencySymbol = document.getElementById('expense_currency_symbol');
        const incomeConversion = document.getElementById('income_conversion');
        const expenseConversion = document.getElementById('expense_conversion');
        const incomeKrwAmount = document.getElementById('income_krw_amount');
        const expenseKrwAmount = document.getElementById('expense_krw_amount');

        // Store old category value for restoration
        const oldCategoryId = "{{ old('category_id') }}";

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
                
                // Restore old value if validation failed
                if (oldCategoryId && oldCategoryId == category.id) {
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
                expenseInput.value = '';
            } else if (transactionType === 'expense') {
                incomeSection.style.display = 'none';
                expenseSection.style.display = 'block';
                incomeInput.required = false;
                expenseInput.required = true;
                incomeInput.value = '';
            }
            updateConversion();
        }

        /**
         * Update currency symbols
         */
        function updateCurrencySymbol() {
            const selectedOption = currencySelect.options[currencySelect.selectedIndex];
            const symbol = selectedOption.dataset.symbol;
            incomeCurrencySymbol.textContent = symbol;
            expenseCurrencySymbol.textContent = symbol;
            updateConversion();
        }

        /**
         * Update conversion display
         */
        function updateConversion() {
            const selectedOption = currencySelect.options[currencySelect.selectedIndex];
            const rate = parseFloat(selectedOption.dataset.rate);
            const symbol = selectedOption.dataset.symbol;
            const transactionType = transactionTypeInput.value;
            
            // If not KRW, show conversion
            if (symbol !== '₩') {
                if (transactionType === 'income') {
                    const amount = parseFloat(incomeInput.value) || 0;
                    const krwAmount = amount * rate;
                    incomeKrwAmount.textContent = '₩' + krwAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    incomeConversion.style.display = 'block';
                } else if (transactionType === 'expense') {
                    const amount = parseFloat(expenseInput.value) || 0;
                    const krwAmount = amount * rate;
                    expenseKrwAmount.textContent = '₩' + krwAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    expenseConversion.style.display = 'block';
                }
            } else {
                incomeConversion.style.display = 'none';
                expenseConversion.style.display = 'none';
            }
        }

        // Event listeners
        currencySelect.addEventListener('change', updateCurrencySymbol);
        incomeInput.addEventListener('input', updateConversion);
        expenseInput.addEventListener('input', updateConversion);

        // Initialize on page load
        const initialTransactionType = transactionTypeInput.value || 'income';
        
        // Set correct button as active
        transactionTypeButtons.forEach(btn => {
            if (btn.dataset.type === initialTransactionType) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        updateCurrencySymbol();
        updateFormSections(initialTransactionType);
        loadCategories(initialTransactionType);
    });
</script>
@endpush
@endsection
