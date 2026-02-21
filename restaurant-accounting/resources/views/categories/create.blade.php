@extends('layouts.app')

@section('title', 'Create Category - Restaurant Accounting')
@section('page-title', 'Create Category')

@section('content')
<style>
    /* Clean & Spacious Category Form */
    .page-header-section {
        margin-bottom: 3rem;
    }
    
    .page-header-section h1 {
        font-size: 2rem;
        font-weight: 600;
        color: #000;
        margin-bottom: 0.75rem;
        letter-spacing: -0.5px;
    }
    
    .page-header-section p {
        color: #6c757d;
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    
    .title-accent {
        width: 50px;
        height: 3px;
        background-color: #dc3545;
    }
    
    .category-form-card {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.075);
        border-top: 3px solid #dc3545;
        padding: 3rem;
    }
    
    .card-title-section {
        margin-bottom: 2.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .card-title-section h5 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #000;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }
    
    .card-title-section i {
        color: #dc3545;
        font-size: 1.15rem;
    }
    
    /* Form Fields - Clean & Spacious */
    .form-group-clean {
        margin-bottom: 2rem;
    }
    
    .form-group-clean label {
        display: block;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #000;
        margin-bottom: 0.625rem;
    }
    
    .form-group-clean .required-mark {
        color: #dc3545;
        margin-left: 3px;
    }
    
    .form-group-clean .form-control,
    .form-group-clean .form-select {
        height: 50px;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        transition: all 0.2s ease;
    }
    
    .form-group-clean .form-control:focus,
    .form-group-clean .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.12);
        outline: 0;
        background-color: #fff;
    }
    
    .form-group-clean .form-control::placeholder {
        color: #adb5bd;
    }
    
    /* Module Helper Text */
    .helper-message {
        margin-top: 0.625rem;
        padding: 0.75rem 1rem;
        background-color: #fff;
        border-left: 3px solid #6c757d;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #495057;
        display: none;
        border: 1px solid #e9ecef;
        border-left-width: 3px;
    }
    
    .helper-message.show {
        display: block;
        animation: slideDown 0.25s ease;
    }
    
    .helper-message.restaurant {
        border-left-color: #dc3545;
    }
    
    .helper-message.inventory {
        border-left-color: #000;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Button Section */
    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 0.875rem;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
    }
    
    .btn-outline-back {
        min-width: 120px;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        font-size: 0.9375rem;
        border: 1px solid #000;
        color: #000;
        background: #fff;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-outline-back:hover {
        background: #f8f9fa;
        color: #000;
        border-color: #000;
    }
    
    .btn-primary-save {
        min-width: 140px;
        padding: 0.625rem 1.75rem;
        font-weight: 500;
        font-size: 0.9375rem;
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.25);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-primary-save:hover {
        background: #c82333;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        color: #fff;
    }
    
    /* Error States */
    .form-group-clean .is-invalid {
        border-color: #dc3545;
    }
    
    .form-group-clean .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
</style>

<div class="container py-5">
    <!-- Page Header -->
    <div class="page-header-section">
        <h1>Create Category</h1>
        <p>Add a new category for Restaurant or Inventory module</p>
        <div class="title-accent"></div>
    </div>

    <!-- Form Card -->
    <div class="category-form-card">
        <!-- Card Title -->
        <div class="card-title-section">
            <h5>
                <i class="fas fa-plus-circle"></i>
                <span>Add New Category</span>
            </h5>
        </div>
        
        <!-- Form -->
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <!-- Category Name -->
            <div class="form-group-clean">
                <label for="name">
                    Category Name <span class="required-mark">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Enter category name"
                       required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Module -->
            <div class="form-group-clean">
                <label for="module">
                    Module <span class="required-mark">*</span>
                </label>
                <select class="form-select @error('module') is-invalid @enderror" 
                        id="module" 
                        name="module" 
                        required>
                    <option value="">Select Module</option>
                    <option value="restaurant" {{ old('module') == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                    <option value="inventory" {{ old('module') == 'inventory' ? 'selected' : '' }}>Inventory</option>
                </select>
                @error('module')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                <!-- Dynamic Helper Messages -->
                <div class="helper-message restaurant" id="helper-restaurant">
                    This category will affect restaurant profit and normal reports.
                </div>
                <div class="helper-message inventory" id="helper-inventory">
                    This category will be used for inventory transactions and inventory reports.
                </div>
            </div>

            <!-- Type -->
            <div class="form-group-clean">
                <label for="type">
                    Type <span class="required-mark">*</span>
                </label>
                <select class="form-select @error('type') is-invalid @enderror" 
                        id="type" 
                        name="type" 
                        required>
                    <option value="">Select Type</option>
                    <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <a href="{{ route('categories.index') }}" class="btn btn-outline-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
                <button type="submit" class="btn btn-primary-save">
                    <i class="fas fa-save"></i>
                    <span>Save Category</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Dynamic Module Helper
    document.addEventListener('DOMContentLoaded', function() {
        const moduleSelect = document.getElementById('module');
        const helperRestaurant = document.getElementById('helper-restaurant');
        const helperInventory = document.getElementById('helper-inventory');
        
        function showModuleHelper() {
            const value = moduleSelect.value;
            
            // Hide all helpers
            helperRestaurant.classList.remove('show');
            helperInventory.classList.remove('show');
            
            // Show selected helper
            if (value === 'restaurant') {
                helperRestaurant.classList.add('show');
            } else if (value === 'inventory') {
                helperInventory.classList.add('show');
            }
        }
        
        // Check on page load
        showModuleHelper();
        
        // Listen for changes
        moduleSelect.addEventListener('change', showModuleHelper);
    });
</script>
@endsection
