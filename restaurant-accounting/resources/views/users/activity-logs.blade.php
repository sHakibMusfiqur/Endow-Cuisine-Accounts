@extends('layouts.app')

@section('title', 'Activity Logs - Restaurant Accounting')
@section('page-title', 'System Activity Logs')

@section('content')
<style>
    .log-card {
        border-left: 4px solid #000000;
    }
    .log-item {
        border-bottom: 1px solid #e5e7eb;
        padding: 15px 0;
    }
    .log-item:last-child {
        border-bottom: none;
    }
    .action-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .action-create {
        background-color: #10b981;
        color: white;
    }
    .action-update {
        background-color: #3b82f6;
        color: white;
    }
    .action-delete {
        background-color: #dc2626;
        color: white;
    }
    .action-view {
        background-color: #6b7280;
        color: white;
    }
    .action-login {
        background-color: #8b5cf6;
        color: white;
    }
    .action-logout {
        background-color: #f59e0b;
        color: white;
    }
    .action-restock {
        background-color: #eab308;
        color: white;
    }
    .action-stock_out {
        background-color: #ef4444;
        color: white;
    }
    .module-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        background-color: #f3f4f6;
        color: #374151;
    }
    .module-inventory {
        background-color: #dbeafe;
        color: #1e40af;
    }
    .module-transactions {
        background-color: #dcfce7;
        color: #166534;
    }
    .module-users {
        background-color: #fce7f3;
        color: #9f1239;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-history"></i> System Activity Logs</h4>
            <p class="text-muted mb-0">Monitor all user activities and system events</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="card log-card">
        <div class="card-body">
            @forelse($logs as $log)
            <div class="log-item">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <span class="action-badge action-{{ str_replace('_', '-', $log->action) }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                        @if($log->module)
                        <span class="module-badge module-{{ $log->module }} ms-1">{{ ucfirst($log->module) }}</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        @if($log->user)
                        <i class="fas fa-user-circle text-muted"></i>
                        <strong>{{ $log->user->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $log->user->email }}</small>
                        @else
                        <span class="text-muted">System</span>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <p class="mb-0">{{ $log->description }}</p>
                        <small class="text-muted">
                            <i class="fas fa-map-pin"></i> {{ $log->ip_address }}
                        </small>
                    </div>
                    <div class="col-md-2 text-end">
                        <small class="text-muted">
                            {{ $log->created_at->format('d M Y') }}<br>
                            {{ $log->created_at->format('h:i A') }}
                        </small>
                        <br>
                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @if($log->metadata)
                <div class="row mt-2">
                    <div class="col-12">
                        <details>
                            <summary class="text-muted" style="cursor: pointer; font-size: 0.85rem;">
                                <i class="fas fa-info-circle"></i> View metadata
                            </summary>
                            <pre class="mt-2 p-2 bg-light" style="font-size: 0.8rem;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-history fa-3x mb-3"></i>
                <p>No activity logs found</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <x-pagination :items="$logs" />
</div>
@endsection
