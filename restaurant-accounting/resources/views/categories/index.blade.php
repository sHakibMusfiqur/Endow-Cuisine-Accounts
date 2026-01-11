@extends('layouts.app')

@section('title', 'Categories - Restaurant Accounting')
@section('page-title', 'Categories')

@section('content')
<style>
    /* Mobile Responsive Styles for Index Pages */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0 10px;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .d-flex.justify-content-between h4 {
            font-size: 1.1rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
            justify-content: center;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            font-size: 0.85rem;
            min-width: 600px;
        }

        .table thead th {
            font-size: 0.8rem;
            padding: 10px 8px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        .pagination {
            font-size: 0.9rem;
        }

        .pagination .page-link {
            padding: 8px 12px;
        }
    }

    @media (max-width: 480px) {
        .d-flex.justify-content-between h4 {
            font-size: 1rem;
        }

        .table {
            font-size: 0.8rem;
            min-width: 500px;
        }

        .table thead th,
        .table tbody td {
            padding: 8px 6px;
        }

        .btn-sm {
            padding: 5px 8px;
            font-size: 0.75rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 3px 6px;
        }
    }

    @media (hover: none) and (pointer: coarse) {
        .btn {
            min-height: 44px;
        }

        .btn-sm {
            min-height: 38px;
        }
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-tags"></i> All Categories</h4>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Transactions Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <span class="badge {{ $category->type == 'income' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($category->type) }}
                        </span>
                    </td>
                    <td>{{ $category->transactions->count() }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this category?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No categories found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
        {{ $categories->links() }}
    </div>
</div>
@endsection
