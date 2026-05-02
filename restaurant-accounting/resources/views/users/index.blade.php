@extends('layouts.app')

@section('title', 'User Management - Restaurant Accounting')
@section('page-title', 'User Management')

@section('content')
<style>
    /* Card and Container Styles */
    .user-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        background: #ffffff;
    }
    
    .user-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-1px);
    }

    /* User Avatar Styles */
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        color: white;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        min-width: 40px;
    }

    .user-name-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Badge Styles - Roles */
    .role-badge {
        padding: 6px 14px;
        border-radius: 24px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .role-admin {
        background-color: #dc2626;
        color: white;
    }

    .role-accountant {
        background-color: #000000;
        color: white;
    }

    .role-manager {
        background-color: #6b7280;
        color: white;
    }

    /* Badge Styles - Modules */
    .module-badge {
        padding: 6px 14px;
        border-radius: 24px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .module-restaurant {
        background-color: #dc2626;
        color: white;
    }

    .module-inventory {
        background-color: #1a1a1a;
        color: white;
    }

    /* Table Styles */
    .user-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .user-table thead {
        background: linear-gradient(to right, #f3f4f6, #ffffff);
        border-bottom: 2px solid #e5e7eb;
    }

    .user-table thead th {
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #374151;
        padding: 16px 12px;
        border: none;
    }

    .user-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }

    .user-table tbody tr:hover {
        background-color: #fafafa;
    }

    .user-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        color: #374151;
        font-size: 0.95rem;
    }

    .user-table tbody td strong {
        color: #111827;
        font-weight: 600;
    }

    /* Action Button Group */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        align-items: center;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 0.9rem;
        cursor: pointer;
        padding: 0;
    }

    .action-btn:hover {
        text-decoration: none;
    }

    .action-btn.view:hover {
        background-color: #dbeafe;
        color: #0284c7;
        border-color: #0284c7;
    }

    .action-btn.edit:hover {
        background-color: #fef3c7;
        color: #d97706;
        border-color: #d97706;
    }

    .action-btn.delete:hover {
        background-color: #fee2e2;
        color: #dc2626;
        border-color: #dc2626;
    }

    .action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Header Styles */
    .header-section {
        margin-bottom: 28px;
    }

    .header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .header-subtitle {
        font-size: 0.95rem;
        color: #6b7280;
        margin-bottom: 0;
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    /* Custom Button Styles */
    .btn-red {
        background-color: #dc2626;
        border-color: #dc2626;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-red:hover {
        background-color: #b91c1c;
        border-color: #b91c1c;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .btn-black {
        background-color: #000000;
        border-color: #000000;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-black:hover {
        background-color: #1f2937;
        border-color: #1f2937;
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Status Badge */
    .status-you {
        background-color: #fef3c7;
        color: #92400e;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-icon {
        font-size: 3.5rem;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .empty-state-text {
        font-size: 1rem;
        color: #6b7280;
        font-weight: 500;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center header-section">
        <div>
            <h1 class="header-title">
                <i class="fas fa-users me-2"></i>User Management
            </h1>
                   </div>
        <div class="header-actions">
            <a href="{{ route('activity-logs.index') }}" class="btn btn-black">
                <i class="fas fa-history me-2"></i>Activity Logs
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-red">
                <i class="fas fa-plus me-2"></i>Add New User
            </a>
        </div>
    </div>

    <div class="card user-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover user-table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Module</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="user-name-wrapper">
                                    <div class="user-avatar" title="{{ $user->name }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <br>
                                            <span class="status-you">
                                                <i class="fas fa-check-circle me-1"></i>You
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span title="{{ $user->email }}">{{ $user->email }}</span>
                            </td>
                            <td>
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td>
                                @php
                                    $role = $user->roles->first()?->name ?? 'N/A';
                                @endphp
                                <span class="role-badge role-{{ strtolower($role) }}">
                                    {{ ucfirst($role) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $role = $user->roles->first()?->name ?? 'N/A';
                                @endphp
                                @if($role === 'accountant' && $user->module_access)
                                    <span class="module-badge module-{{ strtolower($user->module_access) }}">
                                        <i class="fas fa-cube me-1"></i>{{ ucfirst($user->module_access) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('users.show', $user) }}" class="action-btn view" title="View User" data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <a href="{{ route('users.edit', $user) }}" class="action-btn edit" title="Edit User" data-bs-toggle="tooltip">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" 
                                            class="action-btn delete" 
                                            title="Delete User"
                                            data-bs-toggle="tooltip"
                                            onclick="if(confirm('Are you sure you want to delete this user?')) { document.getElementById('delete-form-{{ $user->id }}').submit(); }">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" 
                                          action="{{ route('users.destroy', $user) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <p class="empty-state-text">No users found</p>
                                    <p class="text-muted mt-2">
                                        <a href="{{ route('users.create') }}" class="btn btn-red btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>Create First User
                                        </a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <x-pagination :items="$users" />
</div>
@endsection
