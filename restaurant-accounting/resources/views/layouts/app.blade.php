<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Endow Cuisine Accounting')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

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
            background: linear-gradient(180deg, #292929 0%, #1a1a1a 100%);
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
            background: #ffffff;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            flex-shrink: 0;
            padding: 4px;
        }

        .sidebar .logo i {
            font-size: 26px;
            color: #EA222A;
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
            border-bottom-color: rgba(234,34,42,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2), 0 0 20px rgba(255,255,255,0.05);
        }

        .sidebar .logo:hover .logo-icon {
            transform: scale(1.1) rotate(-5deg);
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(234,34,42,0.3);
        }

        .sidebar .logo:hover i {
            transform: scale(1.1);
            color: #FF3D47;
            filter: drop-shadow(0 0 8px rgba(234,34,42,0.5));
        }

        .sidebar .logo:hover .logo-text .app-name {
            color: #EA222A;
            transform: translateX(2px);
        }

        .sidebar .logo:hover .logo-text .app-tagline {
            color: rgba(234,34,42,0.8);
        }

        /* Active State (when on dashboard) */
        .sidebar .logo.active {
            background: linear-gradient(135deg, rgba(234,34,42,0.15) 0%, rgba(255,255,255,0.1) 100%);
            border-bottom: 2px solid #EA222A;
            box-shadow: 0 2px 8px rgba(234,34,42,0.3);
        }

        .sidebar .logo.active .logo-icon {
            background: #ffffff;
            box-shadow: 0 0 15px rgba(234,34,42,0.4);
        }

        .sidebar .logo.active i {
            color: #EA222A;
            filter: drop-shadow(0 0 6px rgba(234,34,42,0.6));
        }

        .sidebar .logo.active .logo-text .app-name {
            color: #EA222A;
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
            background: linear-gradient(135deg, #292929, #1a1a1a);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3), 0 0 20px rgba(234,34,42,0.1);
            z-index: 1001;
            border: 1px solid rgba(234,34,42,0.2);
        }

        .sidebar .logo::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #292929;
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
            outline: 2px solid #EA222A;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(234,34,42,0.2);
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .sidebar .logo {
                border: 2px solid rgba(255,255,255,0.3);
            }

            .sidebar .logo.active {
                border: 2px solid #EA222A;
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
            background: #292929;
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
            color: #292929;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-right: 15px;
        }

        .toggle-btn:hover {
            background: rgba(41, 41, 41, 0.1);
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
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            color: white;
        }

        .stat-card.expense {
            background: linear-gradient(135deg, #292929 0%, #1a1a1a 100%);
            color: white;
        }

        .stat-card.balance {
            background: linear-gradient(135deg, #EA222A 0%, #B81D23 100%);
            color: white;
        }

        .stat-card.net {
            background: linear-gradient(135deg, #292929 0%, #3d3d3d 100%);
            color: white;
        }

        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #FF3D47 0%, #EA222A 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #34ce57 0%, #28a745 100%);
        }

        .btn-info {
            background: linear-gradient(135deg, #292929 0%, #1a1a1a 100%);
            border: none;
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #3d3d3d 0%, #292929 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border: none;
            color: #212529;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #ffcd39 0%, #ffc107 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #e4606d 0%, #dc3545 100%);
        }

        /* ============================================
           PAGINATION - PROJECT THEME
           ============================================ */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin: 20px 0;
        }

        .page-item .page-link {
            color: #111;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            padding: 8px 14px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .page-item .page-link:hover {
            background-color: #fff5f5;
            color: #b91c1c;
            border-color: #fecaca;
            transform: translateY(-1px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            border-color: #EA222A;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(234, 34, 42, 0.3);
        }

        .page-item.active .page-link:hover {
            background: linear-gradient(135deg, #FF3D47 0%, #EA222A 100%);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .page-item.disabled .page-link {
            color: #9ca3af;
            background-color: #f9fafb;
            border-color: #e5e7eb;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .page-item.disabled .page-link:hover {
            transform: none;
        }

        /* Override Bootstrap outline buttons to match project theme */
        .btn-outline-primary {
            color: #EA222A;
            border-color: #EA222A;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            background-color: #EA222A;
            border-color: #EA222A;
            color: #ffffff;
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

        /* ============================================
           MOBILE & TABLET RESPONSIVE DESIGN
           ============================================ */

        /* Mobile & Tablet General - Up to 768px */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition-speed) ease;
                width: var(--sidebar-width);
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            }

            .sidebar.sidebar-collapsed {
                width: var(--sidebar-width);
            }

            .main-content {
                margin-left: 0 !important;
                padding: 15px;
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

            /* Navbar Responsive */
            .navbar-top {
                padding: 12px 15px;
                margin: -15px -15px 15px -15px;
                flex-wrap: wrap;
            }

            .navbar-top h4 {
                font-size: 1.1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 150px;
            }

            /* Stat Cards - Full Width on Mobile */
            .stat-card {
                margin-bottom: 15px;
                padding: 15px;
            }

            /* Table Responsive Improvements */
            .table-responsive {
                border-radius: 8px;
                padding: 10px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table {
                font-size: 0.85rem;
                min-width: 800px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
                white-space: nowrap;
            }

            /* Card Improvements */
            .card {
                margin-bottom: 15px;
                border-radius: 8px;
            }

            .card-body {
                padding: 15px;
            }

            /* Button Group Adjustments */
            .btn-group {
                flex-wrap: wrap;
                gap: 5px;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            /* Form Improvements */
            .form-control,
            .form-select {
                font-size: 16px; /* Prevents iOS zoom on focus */
            }

            /* Alerts */
            .alert {
                font-size: 0.9rem;
                padding: 10px 15px;
            }

            /* Dropdown Menu */
            .dropdown-menu {
                font-size: 0.9rem;
            }

            /* User Profile in Navbar */
            .dropdown-toggle span {
                display: none; /* Hide username on very small screens */
            }
        }

        @media (min-width: 769px) {
            .mobile-toggle {
                display: none;
            }
        }

        /* Extra small screens (phones in portrait) - Compact Mode */
        @media (max-width: 480px) {
            .sidebar .logo {
                padding: 12px;
            }

            .logo-icon {
                min-width: 38px;
                min-height: 38px;
            }

            .sidebar .logo i {
                font-size: 20px;
            }

            /* Keep logo text visible on mobile */
            .logo-text {
                display: flex !important;
                opacity: 1;
            }

            .logo-text .app-name {
                font-size: 15px;
            }

            .logo-text .app-tagline {
                font-size: 9px;
            }

            /* Extra compact navbar */
            .navbar-top {
                padding: 10px 12px;
                margin: -15px -15px 12px -15px;
            }

            .navbar-top h4 {
                font-size: 1rem;
                max-width: 120px;
            }

            /* Show only user initials */
            .dropdown-toggle img,
            .dropdown-toggle > div {
                width: 32px !important;
                height: 32px !important;
                font-size: 14px !important;
            }

            /* Full width buttons */
            .btn {
                font-size: 0.9rem;
            }

            /* Stat cards more compact */
            .stat-card {
                padding: 12px;
            }

            .stat-card h5 {
                font-size: 0.9rem;
            }

            .stat-card h2 {
                font-size: 1.5rem;
            }

            /* Tables even more compact */
            .table {
                font-size: 0.8rem;
            }

            .table th,
            .table td {
                padding: 6px 4px;
            }

            /* Card headers */
            .card-header h5 {
                font-size: 1rem;
            }
        }

        /* Tablet Portrait - 481px to 768px */
        @media (min-width: 481px) and (max-width: 768px) {
            .navbar-top h4 {
                max-width: 300px;
            }

            .dropdown-toggle span {
                display: inline; /* Show username on larger mobile/tablet */
            }

            .table {
                font-size: 0.9rem;
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

            .navbar-top {
                padding: 12px 20px;
            }

            .main-content {
                padding: 18px;
            }
        }

        /* Desktop - Large Screens */
        @media (min-width: 1025px) and (max-width: 1440px) {
            .main-content {
                padding: 20px;
            }
        }

        /* Ultra-wide Screens */
        @media (min-width: 1920px) {
            .main-content {
                max-width: 1800px;
                margin-left: auto;
                margin-right: auto;
                padding-left: calc(var(--sidebar-width) + 40px);
            }

            .main-content.sidebar-collapsed {
                padding-left: calc(var(--sidebar-collapsed-width) + 40px);
            }
        }

        /* Touch Screen Optimizations */
        @media (hover: none) and (pointer: coarse) {
            /* Increase touch targets */
            .btn {
                min-height: 44px;
                padding: 10px 16px;
            }

            .btn-sm {
                min-height: 38px;
                padding: 8px 12px;
            }

            .nav-link {
                min-height: 48px;
                padding: 14px 20px;
            }

            .form-control,
            .form-select {
                min-height: 44px;
                padding: 10px 12px;
            }

            .dropdown-item {
                min-height: 44px;
                padding: 10px 16px;
            }

            /* Better spacing for touch */
            .btn-group .btn {
                margin: 2px;
            }
        }

        /* Landscape Mode - Phones */
        @media (max-height: 500px) and (orientation: landscape) {
            .sidebar {
                padding-top: 10px;
            }

            .sidebar .logo {
                margin-bottom: 10px;
                padding: 10px 16px;
            }

            .nav-link {
                padding: 8px 20px;
                margin: 1px 10px;
            }

            .sidebar-footer {
                padding: 10px 20px !important;
            }
        }

        /* Print Styles */
        @media print {
            .sidebar,
            .navbar-top,
            .sidebar-overlay,
            .toggle-btn,
            .btn,
            .alert {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .table {
                font-size: 10pt;
            }

            .card {
                border: 1px solid #ddd;
                box-shadow: none;
            }
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
                {{-- Logo Image Placeholder - Replace src with actual logo path --}}
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Endow Cuisine Logo"
                         style="width: 40px; height: 40px; object-fit: contain;">
                @elseif(file_exists(public_path('images/logo.svg')))
                    <img src="{{ asset('images/logo.svg') }}"
                         alt="Endow Cuisine Logo"
                         style="width: 40px; height: 40px; object-fit: contain;">
                @elseif(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Endow Cuisine Logo"
                         style="width: 40px; height: 40px; object-fit: contain;">
                @else
                    {{-- Fallback to icon if logo doesn't exist --}}
                    <i class="fas fa-utensils" aria-hidden="true"></i>
                @endif
            </div>
            <div class="logo-text">
                <span class="app-name">Endow Cuisine</span>
                {{-- <span class="app-tagline">Accounting System</span> --}}
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
            @can('manage categories')
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
            @endcan
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
               href="{{ route('reports.index') }}"
               data-tooltip="Reports">
                <i class="fas fa-file-alt"></i> <span class="nav-text">Reports</span>
            </a>
            @can('manage users')
            <a class="nav-link {{ request()->routeIs('users.*') || request()->routeIs('activity-logs.*') ? 'active' : '' }}"
               href="{{ route('users.index') }}"
               data-tooltip="User Management">
                <i class="fas fa-users-cog"></i> <span class="nav-text">User Management</span>
            </a>
            @endcan
            <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
               href="{{ route('profile.show') }}"
               data-tooltip="My Profile">
                <i class="fas fa-user-circle"></i> <span class="nav-text">My Profile</span>
            </a>
        </nav>

        <!-- System Info Footer -->
        <div class="sidebar-footer" style="padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
            <div class="currency-info" style="color: rgba(255,255,255,0.6); font-size: 11px; text-align: center;">
                <i class="fas fa-won-sign"></i> <span class="nav-text">Currency: <strong style="color: #EA222A;">KRW (₩)</strong></span>
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
                                 style="width: 36px; height: 36px; background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%); color: white; font-weight: bold; font-size: 16px; border: 2px solid #dee2e6;">
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
