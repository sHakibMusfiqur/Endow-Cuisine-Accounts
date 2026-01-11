<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Endow Cuisine Accounting')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .email-logo {
            margin-bottom: 15px;
        }
        .email-logo img {
            max-width: 100px;
            height: auto;
        }
        .email-logo .fallback-icon {
            font-size: 50px;
            color: white;
        }
        .email-header h1 {
            margin: 10px 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 40px;
            color: #333333;
            line-height: 1.6;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 15px 0;
        }
        .btn:hover {
            background: linear-gradient(135deg, #FF3D47 0%, #EA222A 100%);
        }
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Email Header with Logo Placeholder -->
        <div class="email-header">
            <div class="email-logo">
                {{-- Logo Image Placeholder - Replace src with actual logo path --}}
                {{-- When logo file exists, it will be displayed automatically --}}
                @if(file_exists(public_path('images/logo-white.png')))
                    <img src="{{ asset('images/logo-white.png') }}" alt="Endow Cuisine Logo">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Endow Cuisine Logo">
                @else
                    {{-- Fallback to icon if logo doesn't exist --}}
                    <div class="fallback-icon">🍴</div>
                @endif
            </div>
            <h1>Endow Cuisine</h1>
            <p>Accounting System</p>
        </div>

        <!-- Email Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Email Footer -->
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Endow Cuisine. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
            @yield('footer')
        </div>
    </div>
</body>
</html>
