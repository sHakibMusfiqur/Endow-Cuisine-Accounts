@extends('emails.layout')

@section('title', 'Welcome to Endow Cuisine Accounting')

@section('content')
    <h2 style="color: #333; margin-top: 0;">Welcome to Endow Cuisine!</h2>
    
    <p>Hello {{ $userName }},</p>
    
    <p>Welcome to the Endow Cuisine Accounting System! We're excited to have you on board.</p>
    
    <p>Your account has been successfully created and you can now access all the features of our restaurant accounting platform:</p>
    
    <ul style="line-height: 2;">
        <li>📊 Track daily transactions</li>
        <li>💰 Manage income and expenses</li>
        <li>📈 Generate detailed reports</li>
        <li>🏷️ Organize with categories</li>
        <li>💳 Multiple payment methods</li>
    </ul>
    
    <p style="text-align: center;">
        <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard</a>
    </p>
    
    <div class="divider"></div>
    
    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p>Best regards,<br>The Endow Cuisine Team</p>
@endsection
