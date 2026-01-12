@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-user-circle me-2"></i>My Profile
            </h2>
        </div>
    </div>

    <div class="row">
        <!-- Profile Header Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <!-- Profile Photo -->
                    <div class="profile-photo-container mb-4">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                 alt="Profile Photo" 
                                 class="rounded-circle profile-photo">
                        @else
                            <div class="profile-initials rounded-circle mx-auto">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <!-- User Name -->
                    <h4 class="mb-2">{{ $user->name }}</h4>

                    <!-- Role Badge -->
                    @php
                        $roleName = $user->roles->first()?->name ?? 'No Role';
                        $badgeClass = match($roleName) {
                            'admin' => 'bg-danger',
                            'accountant' => 'bg-dark',
                            'manager' => 'badge-outline-white',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 mb-3">
                        <i class="fas fa-shield-alt me-1"></i>{{ ucfirst($roleName) }}
                    </span>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('profile.edit') }}" class="btn btn-dark">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                        <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-md-8">
            <!-- Personal Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-envelope me-2 text-muted"></i>Email:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-phone me-2 text-muted"></i>Phone:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->phone ?? 'Not provided' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Address:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->address ?? 'Not provided' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-shield-alt me-2 text-muted"></i>Role:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ ucfirst($roleName) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-clock me-2 text-muted"></i>Last Login:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-calendar-plus me-2 text-muted"></i>Member Since:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $user->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-photo-container {
        position: relative;
        display: inline-block;
    }

    .profile-photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 4px solid #f8f9fa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .profile-initials {
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        border: 4px solid #f8f9fa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .badge-outline-white {
        background: transparent;
        border: 2px solid #333;
        color: #333;
    }

    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endsection
