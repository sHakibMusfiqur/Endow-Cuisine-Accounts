@extends('layouts.app')

@section('title', 'Create Transaction - Restaurant Accounting')
@section('page-title', 'Create Transaction')

@section('content')
<style>
    /* Mobile Responsive Styles for Transaction Forms */
    @media (max-width: 768px) {
        .container {
            padding: 15px 10px !important;
            background-color: #f4f6f8 !important;
        }

        .col-md-8 {
            width: 100%;
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0;
        }

        .card {
            border-radius: 8px !important;
            margin-bottom: 15px;
        }

        .enterprise-card {
            box-shadow: 0 1px 2px rgba(0,0,0,0.08) !important;
        }

        .card-header h5 {
            font-size: 1rem;
        }

        .card-body {
            padding: 18px !important;
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

<div class="container" style="background-color: #f4f6f8; min-height: 100vh; padding-top: 20px; padding-bottom: 40px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Inventory Actions Card -->
            <div class="card mb-3 shadow-sm" style="border-radius: 8px; border: 1px solid #e8e8e8; background-color: white;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h6 class="mb-0 fw-semibold" style="font-size: 0.88rem; color: #333;">
                                <i class="fas fa-layer-group me-1" style="font-size: 0.8rem; color: #666;"></i>
                                Inventory Actions
                            </h6>
                        </div>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="{{ route('transactions.inventory-sale-multi.create') }}" class="btn btn-outline-secondary quick-action-btn inventory-sale-btn">
                                <i class="fas fa-boxes"></i> Inventory Sale
                            </a>
                            @can('manage inventory')
                            <a href="{{ route('inventory.movements.internal-purchase-multi') }}" class="btn btn-outline-secondary quick-action-btn inventory-consumption-btn">
                                <i class="fas fa-dolly-flatbed"></i> Inventory Consumption
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="card enterprise-card shadow-sm" style="border-radius: 8px; border: 1px solid #e8e8e8; border-top: 4px solid #c82333; background-color: white;">
                <div class="card-header bg-white py-3 px-4" style="border-bottom: 1px solid #f0f0f0;">
                    <h5 class="mb-0 d-flex align-items-center" style="font-weight: 600; font-size: 1.1rem; color: #2c2c2c;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-2" style="width: 26px; height: 26px; font-size: 11px; background-color: #c82333; color: white;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>
                        New Transaction
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <div class="mb-4">
                            <label for="date" class="form-label section-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-refined general-input @error('date') is-invalid @enderror" 
                                   id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 pb-4" style="border-bottom: 1px solid #ededed;">
                            <label for="description" class="form-label section-label">Description <span class="text-danger">*</span></label>
                            <div id="description-editor" class="form-control editor-refined general-input @error('description') is-invalid @enderror" style="height: 150px; border: 1px solid #dcdcdc; border-radius: 0.375rem;"></div>
                            <input type="hidden" name="description" id="description" value="{{ old('description') }}">
                            @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden currency field - System uses only KRW -->
                        <input type="hidden" name="currency_id" value="{{ $defaultCurrency->id ?? '' }}">

                        <!-- Transaction Type -->
                        <div class="mb-4 pb-4" style="border-bottom: 1px solid #ededed;">
                            <label class="form-label section-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="btn-group w-100 transaction-toggle-group" role="group" aria-label="Transaction Type">
                                <button type="button" class="btn transaction-type-btn income-btn active" data-type="income">
                                    <i class="fas fa-arrow-up"></i> Income
                                </button>
                                <button type="button" class="btn transaction-type-btn expense-btn" data-type="expense">
                                    <i class="fas fa-arrow-down"></i> Expense
                                </button>
                            </div>
                            <input type="hidden" name="transaction_type" id="transaction_type" value="{{ old('transaction_type', 'income') }}">
                        </div>

                        <!-- Payment Details -->
                        <div class="mb-4">
                            <label for="category_id" class="form-label section-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select form-control-refined general-input @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="" disabled selected>Select Category</option>
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4" id="income_section" style="display: none;">
                            <label for="income" class="form-label section-label text-success">Income Amount (KRW) <span class="text-danger">*</span></label>
                            <div class="input-group income-input-group">
                                <span class="input-group-text income-currency-badge">₩</span>
                                <input type="text" class="form-control form-control-refined income-input amount-input @error('income') is-invalid @enderror" 
                                       id="income_display" placeholder="0" autocomplete="off">
                                <input type="hidden" id="income" name="income" value="{{ old('income') }}">
                            </div>
                            @error('income')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4" id="expense_section" style="display: none;">
                            <label for="expense" class="form-label section-label text-danger">Expense Amount (KRW) <span class="text-danger">*</span></label>
                            <div class="input-group expense-input-group">
                                <span class="input-group-text expense-currency-badge">₩</span>
                                <input type="text" class="form-control form-control-refined expense-input amount-input @error('expense') is-invalid @enderror" 
                                       id="expense_display" placeholder="0" autocomplete="off">
                                <input type="hidden" id="expense" name="expense" value="{{ old('expense') }}">
                            </div>
                            @error('expense')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="payment_method_id" class="form-label section-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select form-control-refined general-input @error('payment_method_id') is-invalid @enderror" 
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

                        <div class="d-flex justify-content-between mt-4 pt-4" style="border-top: 1px solid #ededed;">
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-dark shadow-sm action-btn">
                                <i class="fas fa-arrow-left"></i> Back Transactions
                            </a>
                            <button type="submit" class="btn btn-danger shadow-sm action-btn">
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
        color: #1a1a1a;
    }
    .ql-container {
        font-family: inherit;
    }
    #description-editor {
        padding: 0;
        overflow: hidden;
        background-color: #ffffff;
    }
    #description-editor.editor-refined {
        border: 1px solid #dcdcdc !important;
    }
    #description-editor .ql-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
        border-bottom: none;
        background-color: #fafafa;
        border-color: #dcdcdc;
    }
    #description-editor .ql-container {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        border-top: none;
        border-color: #dcdcdc;
    }
    /* Transaction Type Toggle Buttons - Red/Green Theme */
    .transaction-toggle-group {
        display: flex;
        gap: 0;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .transaction-type-btn {
        background-color: #f8f8f8;
        color: #666;
        border: 1px solid #dcdcdc;
        padding: 11px 20px;
        font-weight: 500;
        transition: all 0.25s ease;
        font-size: 0.95rem;
        flex: 1;
    }
    .transaction-type-btn:first-child {
        border-right: none;
    }
    .transaction-type-btn.income-btn.active {
        background-color: #e8f5e9;
        color: #1e7e34;
        border: 2px solid #198754;
        border-width: 2px;
        padding: 10px 19px;
        font-weight: 600;
    }
    .transaction-type-btn.expense-btn.active {
        background-color: #fdecea;
        color: #b02a37;
        border: 2px solid #c82333;
        border-width: 2px;
        padding: 10px 19px;
        font-weight: 600;
    }
    .transaction-type-btn:hover:not(.active) {
        background-color: #ececec;
        border-color: #bbb;
    }
    /* Refined Form Controls */
    .form-control-refined,
    .form-select.form-control-refined {
        border: 1px solid #dcdcdc;
        padding: 10px 14px;
        height: auto;
        min-height: 42px;
        font-size: 0.95rem;
        transition: all 0.25s ease;
        background-color: #ffffff;
        color: #1a1a1a;
    }
    .form-control-refined::placeholder {
        color: #aaa;
    }
    /* General Input Focus */
    .general-input:focus,
    .form-select.general-input:focus,
    .editor-refined.general-input:focus-within {
        border-color: #c82333;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(200, 35, 51, 0.1);
        outline: none;
    }
    /* Income Input Focus - Green Glow */
    .income-input:focus,
    .income-input-group:focus-within .income-input {
        border-color: #198754;
        background-color: #eef9f1;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        outline: none;
    }
    .income-input-group:focus-within .input-group-text {
        border-color: #198754;
        background-color: #e1f3e5;
        color: #1e7e34;
    }
    /* Expense Input Focus - Red Glow */
    .expense-input:focus,
    .expense-input-group:focus-within .expense-input {
        border-color: #dc3545;
        background-color: #fdeeee;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
        outline: none;
    }
    .expense-input-group:focus-within .input-group-text {
        border-color: #dc3545;
        background-color: #fcd8da;
        color: #b02a37;
    }
    /* Amount Input Enhanced Styling */
    .amount-input {
        font-size: 20px !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em;
        text-align: right;
        padding-right: 16px !important;
        transition: all 0.2s ease-in-out;
    }
    .amount-input::placeholder {
        color: #bbb;
        font-weight: 500;
    }
    /* Income Amount Specific */
    .income-input.amount-input {
        background-color: #eef9f1;
        border-color: #198754;
        color: #1e7e34;
    }
    /* Expense Amount Specific */
    .expense-input.amount-input {
        background-color: #fdeeee;
        border-color: #dc3545;
        color: #b02a37;
    }
    /* Remove number input spinner arrows */
    .amount-input::-webkit-outer-spin-button,
    .amount-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .amount-input[type=number] {
        appearance: textfield;
        -moz-appearance: textfield;
    }
    /* Currency Badge Styling */
    .income-currency-badge {
        background-color: #f8f9fa !important;
        border-right: 1px solid #d4edda !important;
        color: #1e7e34 !important;
        font-weight: 600;
        font-size: 16px;
    }
    .expense-currency-badge {
        background-color: #f8f9fa !important;
        border-right: 1px solid #f5c6cb !important;
        color: #b02a37 !important;
        font-weight: 600;
        font-size: 16px;
    }
    .input-group .input-group-text {
        border: 1px solid #dcdcdc;
        background-color: #fafafa;
        color: #555;
        font-weight: 500;
        transition: all 0.25s ease;
    }
    /* Form Labels */
    .form-label.section-label {
        color: #2c2c2c;
        margin-bottom: 8px;
        font-size: 0.88rem;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    /* Action Buttons */
    .action-btn {
        padding: 11px 26px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }
    .btn-danger.action-btn {
        background-color: #c82333;
        border-color: #c82333;
        box-shadow: 0 1px 3px rgba(200, 35, 51, 0.2);
    }
    .btn-danger.action-btn:hover {
        background-color: #a71d2a;
        border-color: #a71d2a;
        box-shadow: 0 2px 4px rgba(167, 29, 42, 0.25);
    }
    .btn-outline-dark.action-btn {
        border-color: #666;
        color: #333;
        border-width: 1px;
    }
    .btn-outline-dark.action-btn:hover {
        background-color: #495057;
        border-color: #495057;
        color: white;
    }
    /* Quick Action Buttons */
    .quick-action-btn {
        border-radius: 6px;
        font-size: 0.875rem;
        padding: 8px 18px;
        border-color: #999;
        color: #555;
        transition: all 0.25s ease;
        font-weight: 500;
    }
    .inventory-sale-btn:hover {
        background-color: #198754;
        border-color: #198754;
        color: white;
        box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2);
    }
    .inventory-consumption-btn:hover {
        background-color: #c82333;
        border-color: #c82333;
        color: white;
        box-shadow: 0 2px 4px rgba(200, 35, 51, 0.2);
    }
    
    /* Enterprise Card Styling */
    .enterprise-card {
        border: 1px solid #e8e8e8;
    }
    
    /* Income Label Color */
    .text-success {
        color: #198754 !important;
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
        const incomeDisplayInput = document.getElementById('income_display');
        const expenseDisplayInput = document.getElementById('expense_display');

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
                if (incomeDisplayInput) incomeDisplayInput.required = true;
                if (expenseDisplayInput) expenseDisplayInput.required = false;
                if (expenseInput) expenseInput.value = '';
                if (expenseDisplayInput) expenseDisplayInput.value = '';
            } else if (transactionType === 'expense') {
                incomeSection.style.display = 'none';
                expenseSection.style.display = 'block';
                if (incomeDisplayInput) incomeDisplayInput.required = false;
                if (expenseDisplayInput) expenseDisplayInput.required = true;
                if (incomeInput) incomeInput.value = '';
                if (incomeDisplayInput) incomeDisplayInput.value = '';
            }
        }

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
        
        updateFormSections(initialTransactionType);
        loadCategories(initialTransactionType);

        /**
         * Thousand Separator Formatting for Amount Inputs
         */
        
        // Get display and hidden input elements
        const incomeDisplay = document.getElementById('income_display');
        const incomeHidden = document.getElementById('income');
        const expenseDisplay = document.getElementById('expense_display');
        const expenseHidden = document.getElementById('expense');

        /**
         * Format number with thousand separators
         */
        function formatNumberWithCommas(value) {
            // Remove all non-digit characters except decimal point
            let cleanValue = value.replace(/[^\d.]/g, '');
            
            // Split by decimal point
            let parts = cleanValue.split('.');
            
            // Add thousand separators to integer part
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            
            // Limit decimal places to 2
            if (parts[1]) {
                parts[1] = parts[1].substring(0, 2);
            }
            
            return parts.join('.');
        }

        /**
         * Get raw numeric value (remove commas)
         */
        function getRawValue(value) {
            return value.replace(/,/g, '');
        }

        /**
         * Handle input on income field
         */
        if (incomeDisplay) {
            // Restore old value if exists
            const oldIncome = "{{ old('income') }}";
            if (oldIncome) {
                incomeDisplay.value = formatNumberWithCommas(oldIncome);
                incomeHidden.value = oldIncome;
            }

            incomeDisplay.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                const oldLength = e.target.value.length;
                
                // Format the value
                const formatted = formatNumberWithCommas(e.target.value);
                e.target.value = formatted;
                
                // Update hidden field with raw value
                incomeHidden.value = getRawValue(formatted);
                
                // Restore cursor position (accounting for added commas)
                const newLength = formatted.length;
                const newPosition = cursorPosition + (newLength - oldLength);
                e.target.setSelectionRange(newPosition, newPosition);
            });

            // Validate on blur
            incomeDisplay.addEventListener('blur', function(e) {
                if (e.target.value) {
                    const rawValue = getRawValue(e.target.value);
                    if (rawValue && !isNaN(rawValue)) {
                        e.target.value = formatNumberWithCommas(rawValue);
                        incomeHidden.value = rawValue;
                    }
                }
            });
        }

        /**
         * Handle input on expense field
         */
        if (expenseDisplay) {
            // Restore old value if exists
            const oldExpense = "{{ old('expense') }}";
            if (oldExpense) {
                expenseDisplay.value = formatNumberWithCommas(oldExpense);
                expenseHidden.value = oldExpense;
            }

            expenseDisplay.addEventListener('input', function(e) {
                const cursorPosition = e.target.selectionStart;
                const oldLength = e.target.value.length;
                
                // Format the value
                const formatted = formatNumberWithCommas(e.target.value);
                e.target.value = formatted;
                
                // Update hidden field with raw value
                expenseHidden.value = getRawValue(formatted);
                
                // Restore cursor position (accounting for added commas)
                const newLength = formatted.length;
                const newPosition = cursorPosition + (newLength - oldLength);
                e.target.setSelectionRange(newPosition, newPosition);
            });

            // Validate on blur
            expenseDisplay.addEventListener('blur', function(e) {
                if (e.target.value) {
                    const rawValue = getRawValue(e.target.value);
                    if (rawValue && !isNaN(rawValue)) {
                        e.target.value = formatNumberWithCommas(rawValue);
                        expenseHidden.value = rawValue;
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
