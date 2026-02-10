<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Real Estate CRM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --primary-light: #7c94f5;
            --secondary: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --sidebar-width: 260px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #0f172a 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar-logo {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo i {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-logo h2 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            padding: 0 20px 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, transparent 100%);
            color: white;
            border-left: 3px solid var(--primary);
        }

        .menu-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .menu-item span {
            flex: 1;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .menu-badge {
            background: var(--danger);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: white;
            height: var(--header-height);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-700);
            cursor: pointer;
        }

        .header-search {
            position: relative;
        }

        .header-search input {
            width: 400px;
            padding: 10px 40px 10px 16px;
            border: 1px solid var(--gray-300);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .header-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .header-search i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            position: relative;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            border-radius: 10px;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s;
        }

        .header-icon:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .header-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px 6px 6px;
            background: var(--gray-50);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-menu:hover {
            background: var(--gray-100);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Content Area */
        .content {
            padding: 30px;
            max-width: 1600px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 0.875rem;
        }

        .breadcrumb a {
            color: var(--gray-600);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb i {
            color: var(--gray-400);
            font-size: 0.75rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .header-search input {
                width: 250px;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 15px;
            }

            .header-search {
                display: none;
            }

            .content {
                padding: 20px 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .user-info {
                display: none;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <i class="fas fa-building"></i>
            <h2>Real Estate CRM</h2>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Property Management</div>
                <a href="{{ route('societies.index') }}" class="menu-item {{ request()->routeIs('societies.*') ? 'active' : '' }}">
                    <i class="fas fa-city"></i>
                    <span>Societies</span>
                </a>
                <a href="{{ route('blocks.index') }}" class="menu-item {{ request()->routeIs('blocks.*') ? 'active' : '' }}">
                    <i class="fas fa-th"></i>
                    <span>Blocks</span>
                </a>
                <a href="{{ route('streets.index') }}" class="menu-item {{ request()->routeIs('streets.*') ? 'active' : '' }}">
                    <i class="fas fa-road"></i>
                    <span>Streets</span>
                </a>
                <a href="{{ route('plots.index') }}" class="menu-item {{ request()->routeIs('plots.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Plots</span>
                </a>
                <a href="{{ route('properties.index') }}" class="menu-item {{ request()->routeIs('properties.*') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Properties</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">CRM</div>
                <a href="{{ route('leads.index') }}" class="menu-item {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="fas fa-user-plus"></i>
                    <span>Leads</span>
                    @if(isset($pendingLeads) && $pendingLeads > 0)
                        <span class="menu-badge">{{ $pendingLeads }}</span>
                    @endif
                </a>
                <a href="{{ route('clients.index') }}" class="menu-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
                <a href="{{ route('dealers.index') }}" class="menu-item {{ request()->routeIs('dealers.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Dealers</span>
                </a>
                <a href="{{ route('deals.index') }}" class="menu-item {{ request()->routeIs('deals.*') ? 'active' : '' }}">
                    <i class="fas fa-handshake"></i>
                    <span>Deals</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Financial</div>
                <a href="{{ route('files.index') }}" class="menu-item {{ request()->routeIs('files.*') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i>
                    <span>Property Files</span>
                </a>
                <a href="{{ route('payments.index') }}" class="menu-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="{{ route('account-payments.index') }}" class="menu-item {{ request()->routeIs('account-payments.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet"></i>
                    <span>Account Payments</span>
                </a>
                <a href="{{ route('expenses.index') }}" class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    <span>Expenses</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Reports & Analytics</div>
                <a href="{{ route('reports.index') }}" class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="header-search">
                    <input type="text" placeholder="Search properties, clients, deals...">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <div class="header-right">
                <div class="header-icon">
                    <i class="fas fa-bell"></i>
                    <span class="badge">5</span>
                </div>

                <div class="user-menu">
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->role?->name ?? 'User' }}</div>
                    </div>
                    <i class="fas fa-chevron-down" style="color: var(--gray-400); font-size: 0.75rem;"></i>
                </div>

                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="header-icon" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content">
            @yield('content')
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        menuToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close mobile menu when clicking a link
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
