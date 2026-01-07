@extends('layouts.app')

@section('title', 'Notifications - Restaurant Accounting')
@section('page-title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-bell"></i> All Notifications</h4>
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
        </form>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
            <div class="list-group-item {{ $notification->is_read ? '' : 'list-group-item-warning' }}">
                <div class="d-flex w-100 justify-content-between">
                    <div>
                        @if($notification->type == 'warning')
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        @elseif($notification->type == 'error')
                        <i class="fas fa-times-circle text-danger"></i>
                        @elseif($notification->type == 'success')
                        <i class="fas fa-check-circle text-success"></i>
                        @else
                        <i class="fas fa-info-circle text-info"></i>
                        @endif
                        <strong>{{ $notification->message }}</strong>
                    </div>
                    <div>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        @if(!$notification->is_read)
                        <form action="{{ route('notifications.read', $notification) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link">
                                <i class="fas fa-check"></i> Mark Read
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="list-group-item text-center text-muted">
                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                <p>No notifications yet</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
