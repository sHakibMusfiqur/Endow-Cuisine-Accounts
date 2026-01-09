@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Update Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileUpdateForm">
                        @csrf
                        @method('PUT')

                        <!-- Current Profile Photo -->
                        <div class="mb-4 text-center">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                     alt="Profile Photo" 
                                     class="rounded-circle mb-3"
                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #dee2e6;">
                                <div>
                                    <a href="#" class="btn btn-sm btn-outline-danger" 
                                       onclick="event.preventDefault(); if(confirm('Remove profile photo?')) { document.getElementById('remove-photo-form').submit(); }">
                                        <i class="fas fa-trash-alt me-1"></i>Remove Photo
                                    </a>
                                </div>
                            @else
                                <div class="profile-initials rounded-circle mx-auto mb-3"
                                     style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; border: 3px solid #dee2e6;">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i>Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email (Read-only) -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   value="{{ $user->email }}" 
                                   readonly
                                   style="background-color: #e9ecef;">
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="e.g., +1 234 567 8900">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-2"></i>Address
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="2" 
                                      placeholder="Enter your address">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Profile Photo Upload -->
                        <div class="mb-4">
                            <label for="profile_photo" class="form-label">
                                <i class="fas fa-camera me-2"></i>Change Profile Photo
                            </label>
                            <input type="file" 
                                   class="form-control @error('profile_photo') is-invalid @enderror" 
                                   id="profile_photo" 
                                   name="profile_photo" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif">
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                        </div>

                        <!-- Role (Read-only) -->
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-shield-alt me-2"></i>Role
                            </label>
                            @php
                                $roleName = $user->roles->first()?->name ?? 'No Role';
                                $badgeClass = match($roleName) {
                                    'admin' => 'bg-danger',
                                    'accountant' => 'bg-dark',
                                    'manager' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <div>
                                <span class="badge {{ $badgeClass }} px-3 py-2">
                                    {{ ucfirst($roleName) }}
                                </span>
                            </div>
                            <small class="text-muted">Role cannot be changed. Contact administrator.</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-dark">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for removing profile photo (separate from main form) -->
<form id="remove-photo-form" action="{{ route('profile.destroy-photo') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection
