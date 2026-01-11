@extends('emails.layout')

@section('title', 'Reset Password - Endow Cuisine Accounting')

@section('content')
    <h2 style="color: #333; margin-top: 0;">Reset Your Password</h2>
    
    <p>Hello,</p>
    
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <p style="text-align: center;">
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
    </p>
    
    <p>This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.</p>
    
    <p>If you did not request a password reset, no further action is required.</p>
    
    <div class="divider"></div>
    
    <p style="font-size: 12px; color: #6c757d;">
        If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
        <br>
        <a href="{{ $resetUrl }}" style="color: #EA222A; word-break: break-all;">{{ $resetUrl }}</a>
    </p>
@endsection
