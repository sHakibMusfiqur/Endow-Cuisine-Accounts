@extends('layouts.app')

@section('title', 'User Details - Restaurant Accounting')
@section('page-title', 'User Details')

@section('content')
<style>
    .user-card {
        border-left: 4px solid #dc2626;
    }
    .role-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
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
    .log-item {
        border-left: 3px solid #e5e7eb;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .log-item.log-create {
        border-left-color: #10b981;
    }
    .log-item.log-update {
        border-left-color: #3b82f6;
    }
    .log-item.log-delete {
        border-left-color: #dc2626;
    }
    .log-item.log-view {
        border-left-color: #6b7280;
    }
    .log-item.log-login {
        border-left-color: #8b5cf6;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-user-circle"></i> User Profile</h4>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            @if($user->id !== auth()->id())
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card user-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                 style="width: 120px; height: 120px; background-color: #f3f4f6;">
                                <i class="fas fa-user fa-4x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                    <div class="mb-3">
                        @php
                            $role = $user->roles->first()?->name ?? 'N/A';
                        @endphp
                        <span class="role-badge role-{{ $role }}">{{ ucfirst($role) }}</span>
                    </div>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-phone text-muted"></i>
                            <strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <strong>Address:</strong> {{ $user->address ?? 'Not provided' }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-calendar text-muted"></i>
                            <strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock text-muted"></i>
                            <strong>Last Login:</strong> {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card user-card">
                <div class="card-header" style="background-color: #f9fafb;">
                    <h5><i class="fas fa-history"></i> Recent Activity</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @forelse($activityLogs as $log)
                    <div class="log-item log-{{ $log->action }}">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ ucfirst($log->action) }}</strong>
                                <span class="badge bg-secondary ms-2">{{ $log->module ?? 'system' }}</span>
                            </div>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-muted">{{ $log->description }}</p>
                        <small class="text-muted">
                            <i class="fas fa-map-pin"></i> {{ $log->ip_address }}
                        </small>
                        @if($log->metadata)
                        <details class="mt-1">
                            <summary class="text-muted" style="cursor: pointer;">View Details</summary>
                            <pre class="mt-2 p-2 bg-light" style="font-size: 0.85rem;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <p>No activity logs found for this user</p>
                    </div>
                    @endforelse
                </div>
                <x-pagination :items="$activityLogs" />
            </div>
        </div>
    </div>
</div>
@endsection
