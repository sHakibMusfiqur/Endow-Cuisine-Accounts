@extends('emails.layout')

@section('title', 'Verify New Email Address - Endow Cuisine Accounting')

@section('content')
    <h2 style="color: #333; margin-top: 0;">Verify Your New Email Address</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>You recently requested to change your email address from <strong>{{ $user->email }}</strong> to <strong>{{ $newEmail }}</strong>.</p>
    
    <p>Please click the button below to verify your new email address and complete the change:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" class="btn">Verify New Email Address</a>
    </div>
    
    <p><strong>Important:</strong> This link will expire in 24 hours for security reasons.</p>
    
    <p>If you did not request this change, please ignore this email or contact our support team immediately. Your current email address will remain unchanged.</p>
    
    <p>Best regards,<br>The Endow Cuisine Accounting Team</p>
    
    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
    
    <p style="font-size: 12px; color: #999;">
        If you're having trouble clicking the "Verify New Email Address" button, copy and paste the URL below into your web browser:
    </p>
    <p style="font-size: 12px; color: #666; word-break: break-all;">
        {{ $verificationUrl }}
    </p>
@endsection
