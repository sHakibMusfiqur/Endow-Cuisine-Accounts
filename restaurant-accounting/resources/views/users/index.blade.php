@extends('layouts.app')

@section('title', 'User Management - Restaurant Accounting')
@section('page-title', 'User Management')

@section('content')
<style>
    .user-card {
        border-left: 4px solid #dc2626;
        transition: all 0.3s ease;
    }
    .user-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .role-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
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
    .btn-red {
        background-color: #dc2626;
        border-color: #dc2626;
        color: white;
    }
    .btn-red:hover {
        background-color: #b91c1c;
        border-color: #b91c1c;
        color: white;
    }
    .btn-black {
        background-color: #000000;
        border-color: #000000;
        color: white;
    }
    .btn-black:hover {
        background-color: #1f2937;
        border-color: #1f2937;
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-users"></i> All Users</h4>
            <p class="text-muted mb-0">Manage system users and their roles</p>
        </div>
        <div>
            <a href="{{ route('activity-logs.index') }}" class="btn btn-black me-2">
                <i class="fas fa-history"></i> Activity Logs
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-red">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card user-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #f9fafb;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <i class="fas fa-user-circle text-muted"></i>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-secondary ms-1">You</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $role = $user->roles->first()?->name ?? 'N/A';
                                @endphp
                                <span class="role-badge role-{{ $role }}">{{ ucfirst($role) }}</span>
                            </td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
