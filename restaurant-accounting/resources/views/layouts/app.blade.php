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
        }

        .sidebar.sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            white-space: nowrap;
            overflow: hidden;
            transition: all var(--transition-speed) ease;
        }

        .sidebar.sidebar-collapsed .logo {
            padding: 0 12px 20px;
            font-size: 20px;
            text-align: center;
        }

        .sidebar .logo .logo-text {
            transition: opacity var(--transition-speed) ease;
        }

        .sidebar.sidebar-collapsed .logo .logo-text {
            opacity: 0;
            display: none;
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
        }

        @media (min-width: 769px) {
            .mobile-toggle {
                display: none;
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
        <div class="logo">
            <i class="fas fa-utensils"></i> <span class="logo-text">Restaurant</span>
        </div>
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
            @if(auth()->user()->isAdmin())
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
            @endif
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
               href="{{ route('reports.index') }}"
               data-tooltip="Reports">
                <i class="fas fa-file-alt"></i> <span class="nav-text">Reports</span>
            </a>
        </nav>
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
                <span class="me-3">
                    <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                    <span class="badge bg-secondary">{{ ucfirst(auth()->user()->role) }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
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
