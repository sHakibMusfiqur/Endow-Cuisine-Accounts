@extends('emails.layout')

@section('title', 'Verify Email Address - Endow Cuisine Accounting')

@section('content')
    <h2 style="color: #333; margin-top: 0;">Verify Your Email Address</h2>
    
    <p>Hello {{ $userName }},</p>
    
    <p>Thank you for registering with Endow Cuisine Accounting System!</p>
    
    <p>Please click the button below to verify your email address and activate your account:</p>
    
    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
    </p>
    
    <p>This verification link will expire in 60 minutes.</p>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <div class="divider"></div>
    
    <p style="font-size: 12px; color: #6c757d;">
        If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
        <br>
        <a href="{{ $verificationUrl }}" style="color: #EA222A; word-break: break-all;">{{ $verificationUrl }}</a>
    </p>
@endsection
