<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Restaurant Accounting')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            padding-top: 20px;
            z-index: 1000;
            overflow-x: hidden;
            overflow-y: auto;
            transition: width var(--transition-speed) ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.sidebar-collapsed .currency-selector {
            padding: 15px 10px;
        }

        .sidebar.sidebar-collapsed .currency-selector label,
        .sidebar.sidebar-collapsed .currency-info {
            opacity: 0;
            height: 0;
            overflow: hidden;
        }

        .sidebar.sidebar-collapsed .currency-selector .form-select {
            font-size: 0;
            padding: 8px;
            text-align: center;
            position: relative;
        }

        /* Show emoji only when NOT focused/opened */
        .sidebar.sidebar-collapsed .currency-selector .form-select:not(:focus)::before {
            content: '💱';
            font-size: 20px;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        /* Normal font size when dropdown is opened */
        .sidebar.sidebar-collapsed .currency-selector .form-select:focus,
        .sidebar.sidebar-collapsed .currency-selector .form-select:active {
            font-size: 14px;
        }

        /* Ensure options are always visible with proper font size */
        .sidebar.sidebar-collapsed .currency-selector .form-select option {
            font-size: 14px;
            color: #000;
            background: #fff;
        }

        /* ============================================
           SIDEBAR LOGO - Optimized Design System
           ============================================ */
        
        /* Logo Container - Base Structure */
        .sidebar-logo {
            display: block;
            text-decoration: none;
            padding: 16px 20px;
            margin: 0 10px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .sidebar .logo {
            color: white;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            text-decoration: none;
            padding: 16px 20px;
            margin: 0 10px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        /* Logo Icon Container */
        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            min-height: 44px;
            background: linear-gradient(135deg, rgba(255,215,0,0.15), rgba(255,255,255,0.05));
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            flex-shrink: 0;
        }

        .sidebar .logo i {
            font-size: 26px;
            color: #ffd700;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: inline-block;
        }

        /* Logo Text Container */
        .logo-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow: hidden;
            transition: all var(--transition-speed) ease;
            opacity: 1;
            width: auto;
            flex: 1;
        }

        .logo-text .app-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: white;
            line-height: 1.2;
            transition: all var(--transition-speed) ease;
        }

        .logo-text .app-tagline {
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 0.3px;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            transition: all var(--transition-speed) ease;
        }

        /* Hover Effects - Expanded State */
        .sidebar .logo:hover {
            background: rgba(255,255,255,0.08);
            border-bottom-color: rgba(255,215,0,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2), 0 0 20px rgba(255,255,255,0.05);
        }

        .sidebar .logo:hover .logo-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, rgba(255,215,0,0.25), rgba(255,255,255,0.1));
            box-shadow: 0 4px 12px rgba(255,215,0,0.3);
        }

        .sidebar .logo:hover i {
            transform: scale(1.1);
            color: #ffed4e;
            filter: drop-shadow(0 0 8px rgba(255,215,0,0.5));
        }

        .sidebar .logo:hover .logo-text .app-name {
            color: #ffd700;
            transform: translateX(2px);
        }

        .sidebar .logo:hover .logo-text .app-tagline {
            color: rgba(255,215,0,0.8);
        }

        /* Active State (when on dashboard) */
        .sidebar .logo.active {
            background: linear-gradient(135deg, rgba(255,215,0,0.15) 0%, rgba(255,255,255,0.1) 100%);
            border-bottom: 2px solid #ffd700;
            box-shadow: 0 2px 8px rgba(255,215,0,0.3);
        }

        .sidebar .logo.active .logo-icon {
            background: linear-gradient(135deg, rgba(255,215,0,0.3), rgba(255,255,255,0.15));
            box-shadow: 0 0 15px rgba(255,215,0,0.4);
        }

        .sidebar .logo.active i {
            color: #ffd700;
            filter: drop-shadow(0 0 6px rgba(255,215,0,0.6));
        }

        .sidebar .logo.active .logo-text .app-name {
            color: #ffd700;
        }

        /* Click/Active Effect */
        .sidebar .logo:active {
            transform: scale(0.98);
            transition: transform 0.1s ease;
        }

        /* ============================================
           COLLAPSED SIDEBAR - Logo Adjustments
           ============================================ */
        
        .sidebar.sidebar-collapsed .logo {
            padding: 16px 12px;
            justify-content: center;
            gap: 0;
            margin: 0 8px 20px;
        }

        .sidebar.sidebar-collapsed .logo-icon {
            min-width: 48px;
            min-height: 48px;
            transform: scale(1.05);
        }

        .sidebar.sidebar-collapsed .logo i {
            font-size: 28px;
        }

        /* Hide Text in Collapsed State */
        .sidebar.sidebar-collapsed .logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            position: absolute;
            pointer-events: none;
        }

        .sidebar.sidebar-collapsed .logo-text .app-name,
        .sidebar.sidebar-collapsed .logo-text .app-tagline {
            opacity: 0;
            transform: translateX(-20px);
        }

        /* Collapsed State Hover */
        .sidebar.sidebar-collapsed .logo:hover .logo-icon {
            transform: scale(1.15) rotate(-8deg);
        }

        /* ============================================
           TOOLTIP - Collapsed State
           ============================================ */
        
        .sidebar .logo::before {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(-10px);
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 20px rgba(255,215,0,0.1);
            z-index: 1001;
            border: 1px solid rgba(255,215,0,0.2);
        }

        .sidebar .logo::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #2c3e50;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 1001;
        }

        .sidebar.sidebar-collapsed .logo:hover::before {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        .sidebar.sidebar-collapsed .logo:hover::after {
            opacity: 1;
        }

        /* ============================================
           ACCESSIBILITY & REDUCED MOTION
           ============================================ */
        
        /* Focus States for Accessibility */
        .sidebar .logo:focus-visible {
            outline: 2px solid #ffd700;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(255,215,0,0.2);
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .sidebar .logo {
                border: 2px solid rgba(255,255,255,0.3);
            }
            
            .sidebar .logo.active {
                border: 2px solid #ffd700;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            .sidebar .logo,
            .sidebar .logo i,
            .sidebar .logo-icon,
            .sidebar .logo-text,
            .logo-text .app-name,
            .logo-text .app-tagline {
                transition: none !important;
                transform: none !important;
                animation: none !important;
            }
            
            .sidebar .logo:hover .logo-icon,
            .sidebar .logo:hover i {
                transform: none !important;
            }
        }

        /* ============================================
           LOGO ANIMATIONS - Subtle Entrance
           ============================================ */
        
        @keyframes logoFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Apply entrance animation on page load */
        .sidebar .logo {
            animation: logoFadeIn 0.5s ease-out;
        }

        .sidebar .logo.active .logo-icon {
            animation: iconPulse 2s ease-in-out infinite;
        }

        /* ============================================
           PRINT STYLES - Logo Optimization
           ============================================ */
        
        @media print {
            .sidebar .logo {
                background: none !important;
                border: none !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
            
            .logo-icon {
                background: none !important;
                box-shadow: none !important;
            }
            
            .sidebar .logo i {
                color: #000 !important;
            }
            
            .logo-text .app-name,
            .logo-text .app-tagline {
                color: #000 !important;
            }
        }

        /* ============================================
           LOADING STATE - Logo Skeleton
           ============================================ */
        
        .sidebar .logo.loading .logo-icon {
            background: linear-gradient(
                90deg,
                rgba(255,255,255,0.05) 0%,
                rgba(255,255,255,0.15) 50%,
                rgba(255,255,255,0.05) 100%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all var(--transition-speed);
            display: flex;
            align-items: center;
            white-space: nowrap;
            position: relative;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar .nav-link i {
            min-width: 20px;
            margin-right: 10px;
            text-align: center;
            transition: margin var(--transition-speed) ease;
        }

        .sidebar.sidebar-collapsed .nav-link {
            padding: 12px;
            margin: 2px 10px;
            justify-content: center;
        }

        .sidebar.sidebar-collapsed .nav-link i {
            margin-right: 0;
            font-size: 20px;
        }

        .sidebar .nav-link .nav-text {
            transition: opacity var(--transition-speed) ease;
        }

        .sidebar.sidebar-collapsed .nav-link .nav-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Tooltip for collapsed sidebar */
        .sidebar.sidebar-collapsed .nav-link {
            position: relative;
        }

        .sidebar.sidebar-collapsed .nav-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #34495e;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            margin-left: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1001;
        }

        .sidebar.sidebar-collapsed .nav-link:hover::after {
            opacity: 1;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left var(--transition-speed) ease;
        }

        .main-content.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Navbar */
        .navbar-top {
            background: white;
            padding: 15px 30px;
            margin: -20px -20px 20px -20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .toggle-btn {
            background: transparent;
            border: none;
            color: #2c3e50;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-right: 15px;
        }

        .toggle-btn:hover {
            background: rgba(44, 62, 80, 0.1);
        }

        .toggle-btn i {
            transition: transform var(--transition-speed) ease;
        }

        .toggle-btn.rotated i {
            transform: rotate(90deg);
        }

        /* User Dropdown Styles */
        .dropdown-toggle::after {
            margin-left: 8px;
            vertical-align: middle;
        }

        .dropdown-menu {
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: #fff5f5 !important;
        }

        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.income {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card.expense {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .stat-card.balance {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .stat-card.net {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity var(--transition-speed) ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition-speed) ease;
                width: var(--sidebar-width);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar.sidebar-collapsed {
                width: var(--sidebar-width);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .toggle-btn {
                display: inline-block !important;
            }
            
            /* Mobile logo adjustments */
            .sidebar .logo {
                padding: 14px 16px;
                margin: 0 8px 16px;
            }
            
            .logo-icon {
                min-width: 40px;
                min-height: 40px;
            }
            
            .sidebar .logo i {
                font-size: 22px;
            }
            
            .logo-text .app-name {
                font-size: 16px;
            }
            
            .logo-text .app-tagline {
                font-size: 10px;
            }
        }

        @media (min-width: 769px) {
            .mobile-toggle {
                display: none;
            }
        }
        
        /* Extra small screens - Compact logo */
        @media (max-width: 480px) {
            .sidebar .logo {
                padding: 12px;
                justify-content: center;
            }
            
            .logo-icon {
                min-width: 36px;
                min-height: 36px;
            }
            
            .sidebar .logo i {
                font-size: 20px;
            }
            
            .logo-text {
                display: none !important;
                opacity: 0;
            }
        }
        
        /* Tablet Landscape - Optimize spacing */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar .logo {
                padding: 14px 18px;
            }
            
            .logo-text .app-name {
                font-size: 17px;
            }
        }

        /* ============================================
           CURRENCY SELECT - Fix Duplication Issue
           ============================================ */
        
        /* Ensure select options always render properly */
        #currencySelect {
            appearance: auto;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
        }

        /* Ensure options have proper styling in all browsers */
        #currencySelect option {
            font-size: 14px !important;
            color: #000 !important;
            background: #fff !important;
            padding: 8px 12px;
        }

        /* Highlighted/selected option in dropdown list */
        #currencySelect option:checked {
            background: #007bff !important;
            color: #fff !important;
        }

        /* Hover state for options */
        #currencySelect option:hover {
            background: #e9ecef !important;
        }

        /* Ensure no pseudo-elements interfere with options */
        #currencySelect option::before,
        #currencySelect option::after {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Logo Section - Optimized Structure -->
        <a href="{{ route('dashboard') }}" 
           class="logo {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
           aria-label="Go to Dashboard - Restaurant Accounting System" 
           data-tooltip="🏠 Dashboard"
           title="Go to Dashboard"
           role="link"
           tabindex="0">
            <div class="logo-icon">
                <i class="fas fa-utensils" aria-hidden="true"></i>
            </div>
            <div class="logo-text">
                <span class="app-name">Endow Cuisine</span>
                <span class="app-tagline">Accounting System</span>
            </div>
        </a>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
               href="{{ route('dashboard') }}" 
               data-tooltip="Dashboard">
                <i class="fas fa-chart-line"></i> <span class="nav-text">Dashboard</span>
            </a>
            <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" 
               href="{{ route('transactions.index') }}"
               data-tooltip="Transactions">
                <i class="fas fa-exchange-alt"></i> <span class="nav-text">Transactions</span>
            </a>
            @role('admin')
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" 
               href="{{ route('categories.index') }}"
               data-tooltip="Categories">
                <i class="fas fa-tags"></i> <span class="nav-text">Categories</span>
            </a>
            <a class="nav-link {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}" 
               href="{{ route('payment-methods.index') }}"
               data-tooltip="Payment Methods">
                <i class="fas fa-credit-card"></i> <span class="nav-text">Payment Methods</span>
            </a>
            @endrole
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
               href="{{ route('reports.index') }}"
               data-tooltip="Reports">
                <i class="fas fa-file-alt"></i> <span class="nav-text">Reports</span>
            </a>
            <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" 
               href="{{ route('profile.show') }}"
               data-tooltip="My Profile">
                <i class="fas fa-user-circle"></i> <span class="nav-text">My Profile</span>
            </a>
        </nav>

        <!-- Currency Switcher -->
        <div class="currency-selector" style="padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
            <form action="{{ route('currency.switch') }}" method="POST" id="currencySwitchForm">
                @csrf
                <label for="currencySelect" style="color: rgba(255,255,255,0.7); font-size: 12px; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-money-bill-wave"></i> <span class="nav-text">Display Currency</span>
                </label>
                <select name="currency_id" id="currencySelect" class="form-select form-select-sm" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);" onchange="this.form.submit()">
                    @foreach($allCurrencies as $currency)
                        <option 
                            value="{{ $currency->id }}" 
                            {{ (int)$activeCurrency->id === (int)$currency->id ? 'selected' : '' }}
                            style="color: #000; background: #fff;">
                            {{ $currency->code }} ({{ $currency->symbol }})
                        </option>
                    @endforeach
                </select>
            </form>
            <div class="currency-info" style="margin-top: 8px; color: rgba(255,255,255,0.6); font-size: 11px;">
                <span class="nav-text">Active: <strong style="color: #ffd700;">{{ $activeCurrency->code }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="navbar-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!-- Desktop Toggle Button -->
                <button class="toggle-btn d-none d-md-inline-block" id="desktopToggle" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <!-- Mobile Toggle Button -->
                <button class="toggle-btn d-md-none mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
            </div>
            <div class="d-flex align-items-center">
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center px-2 py-1" 
                            type="button" 
                            id="userDropdown" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            style="color: #333; cursor: pointer; border-radius: 8px; transition: background-color 0.2s;"
                            onmouseover="this.style.backgroundColor='#f8f9fa'"
                            onmouseout="this.style.backgroundColor='transparent'">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" 
                                 alt="Profile" 
                                 class="rounded-circle me-2"
                                 style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #dee2e6;">
                        @else
                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center"
                                 style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; font-size: 16px; border: 2px solid #dee2e6;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span style="font-weight: 500;">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" 
                        aria-labelledby="userDropdown"
                        style="border-radius: 8px; border: 1px solid #dee2e6; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2" 
                               href="{{ route('profile.show') }}"
                               style="transition: background-color 0.2s;">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" 
                                        class="dropdown-item text-danger py-2 w-100 text-start"
                                        style="transition: background-color 0.2s; border: none; background: none;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ===================================
        // SIDEBAR TOGGLE FUNCTIONALITY
        // ===================================
        
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const desktopToggle = document.getElementById('desktopToggle');
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        // Local Storage Keys
        const STORAGE_KEY = 'sidebar-collapsed';
        
        // Check if device is mobile
        function isMobile() {
            return window.innerWidth <= 768;
        }
        
        // Desktop Toggle Function
        function toggleSidebarDesktop() {
            const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            desktopToggle.classList.toggle('rotated');
            
            // Save state to localStorage
            localStorage.setItem(STORAGE_KEY, isCollapsed ? 'true' : 'false');
        }
        
        // Mobile Toggle Function
        function toggleSidebarMobile() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        }
        
        // Close mobile sidebar
        function closeMobileSidebar() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
        
        // Initialize sidebar state on page load
        function initializeSidebar() {
            if (!isMobile()) {
                // Desktop: Restore collapsed state from localStorage
                const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                    desktopToggle.classList.add('rotated');
                }
            } else {
                // Mobile: Always start with sidebar hidden
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('sidebar-collapsed');
            }
        }
        
        // Event Listeners
        if (desktopToggle) {
            desktopToggle.addEventListener('click', toggleSidebarDesktop);
        }
        
        if (mobileToggle) {
            mobileToggle.addEventListener('click', toggleSidebarMobile);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }
        
        // Close mobile sidebar when clicking on a link
        const sidebarLinks = sidebar.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    closeMobileSidebar();
                }
            });
        });
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (!isMobile()) {
                    // Switched to desktop view
                    closeMobileSidebar();
                    initializeSidebar();
                } else {
                    // Switched to mobile view
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('sidebar-collapsed');
                    desktopToggle.classList.remove('rotated');
                }
            }, 250);
        });
        
        // Keyboard accessibility
        document.addEventListener('keydown', (e) => {
            // Press Escape to close mobile sidebar
            if (e.key === 'Escape' && isMobile() && sidebar.classList.contains('show')) {
                closeMobileSidebar();
            }
            
            // Press Ctrl+B to toggle desktop sidebar
            if (e.ctrlKey && e.key === 'b' && !isMobile()) {
                e.preventDefault();
                toggleSidebarDesktop();
            }
        });
        
        // Initialize on page load
        initializeSidebar();
        
        // ===================================
        // AUTO-HIDE ALERTS
        // ===================================
        
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
