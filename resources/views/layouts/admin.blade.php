<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Quản Lý Thư Viện LibNet')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/pagination.css') }}" rel="stylesheet">
    <style>
        :root {
            /* Modern Teal/Emerald Theme */
            --primary-color: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #14b8a6;
            --primary-gradient: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            --secondary-color: #f59e0b;
            --secondary-dark: #d97706;
            --accent-purple: #8b5cf6;
            --accent-blue: #3b82f6;
            --accent-pink: #ec4899;
            --accent-emerald: #10b981;

            /* Light Theme Backgrounds */
            --background-dark: #f8fafc;
            --background-card: #ffffff;
            --background-elevated: #ffffff;
            --header-bg: #ffffff;
            --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active: rgba(20, 184, 166, 0.2);

            /* Text Colors */
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --text-disabled: #94a3b8;
            --text-sidebar: #e2e8f0;
            --text-sidebar-muted: #94a3b8;

            /* Borders */
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-card-hover: 0 10px 40px rgba(0, 0, 0, 0.12);

            /* Transitions */
            --transition-fast: 0.15s;
            --transition-normal: 0.25s;
            --transition-slow: 0.4s;
            --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
            --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);

            /* Border Radius */
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
            --radius-xl: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-dark);
            color: var(--text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
        }

        /* Header - Modern Clean Style */
        .admin-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--header-bg);
            padding: 0 32px;
            height: 70px;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            transition: all var(--transition-normal) var(--ease-smooth);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo:hover {
            transform: scale(1.02);
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--primary-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .logo-text-sub {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .logo-text-main {
            font-size: 22px;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-search {
            position: relative;
        }

        .header-search input {
            background: var(--background-dark);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 10px 42px 10px 16px;
            border-radius: var(--radius-lg);
            width: 280px;
            transition: all var(--transition-normal) var(--ease-smooth);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .header-search input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
            width: 320px;
            background: #ffffff;
        }

        .header-search input::placeholder {
            color: var(--text-muted);
        }

        .header-search button {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .header-search button:hover {
            color: var(--primary-color);
            background: rgba(13, 148, 136, 0.08);
        }

        .user-menu {
            position: relative;
        }

        .btn-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px 6px 6px;
            background: var(--background-dark);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 50px;
            cursor: pointer;
            transition: all var(--transition-normal) var(--ease-smooth);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-user:hover {
            background: #fff;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            transition: transform var(--transition-fast) var(--ease-bounce);
        }

        .btn-user:hover .user-avatar {
            transform: scale(1.05);
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 220px;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all var(--transition-normal) var(--ease-smooth);
            z-index: 1000;
            overflow: hidden;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all var(--transition-fast) var(--ease-smooth);
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
        }

        .dropdown-item:hover {
            background: rgba(13, 148, 136, 0.08);
            color: var(--primary-color);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 4px 0;
        }

        .logout-item {
            color: #ef4444;
        }

        .logout-item:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
        }

        /* Main Layout */
        .admin-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        /* Sidebar - Dark Modern Style */
        .sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--sidebar-bg);
            overflow-y: auto;
            overflow-x: hidden;
            position: sticky;
            top: 70px;
            height: calc(100vh - 70px);
            flex-shrink: 0;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-section-title {
            padding: 20px 20px 10px;
            font-size: 10px;
            font-weight: 700;
            color: var(--text-sidebar-muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            margin: 2px 12px;
            color: var(--text-sidebar);
            text-decoration: none;
            transition: all var(--transition-normal) var(--ease-smooth);
            cursor: pointer;
            position: relative;
            font-size: 14px;
            font-weight: 400;
            border-radius: var(--radius-md);
            white-space: nowrap;
            overflow: hidden;
        }

        .menu-item span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .menu-item:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .menu-item.active {
            background: var(--sidebar-active);
            color: var(--primary-light);
            font-weight: 500;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--primary-light);
            border-radius: 0 3px 3px 0;
        }

        .menu-item i {
            width: 20px;
            font-size: 16px;
            opacity: 0.8;
        }

        .menu-item:hover i,
        .menu-item.active i {
            opacity: 1;
        }

        .menu-badge {
            margin-left: auto;
            background: var(--primary-light);
            color: #000;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }

        .menu-badge.new {
            background: #ef4444;
            color: white;
        }

        .submenu {
            background: rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-height: 0;
            transition: max-height var(--transition-normal) var(--ease-smooth);
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px 10px 52px;
            color: var(--text-sidebar-muted);
            text-decoration: none;
            transition: all var(--transition-fast);
            font-size: 13px;
        }

        .submenu-item:hover {
            color: var(--text-sidebar);
            background: rgba(255, 255, 255, 0.05);
        }

        .submenu-item.active {
            color: var(--primary-light);
        }

        .arrow {
            margin-left: auto;
            transition: transform var(--transition-normal);
            font-size: 11px;
            opacity: 0.6;
        }

        .arrow.rotated {
            transform: rotate(180deg);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 32px;
            background: var(--background-dark);
            min-width: 0;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-title i {
            color: var(--primary-color);
            font-size: 26px;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Cards - Clean Modern Style */
        .card {
            background: var(--background-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 0;
            margin-bottom: 24px;
            transition: all var(--transition-normal) var(--ease-smooth);
            box-shadow: var(--shadow-card);
        }

        .card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary-color);
            font-size: 18px;
        }

        .card-body {
            padding: 24px;
        }

        /* Buttons - Modern Style */
        .btn {
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all var(--transition-normal) var(--ease-smooth);
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
        }

        .btn-secondary {
            background: #fff;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background: var(--background-dark);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }

        .btn-warning {
            background: var(--secondary-color);
            color: #000;
        }

        .btn-warning:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 13px;
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 15px;
        }

        /* Forms - Modern Style */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all var(--transition-normal) var(--ease-smooth);
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Tables - Modern Style */
        .table-responsive {
            overflow-x: auto;
            margin: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead th {
            padding: 14px 16px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
            background: var(--background-dark);
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: all var(--transition-fast);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background: rgba(13, 148, 136, 0.04);
        }

        .table tbody td {
            padding: 14px 16px;
            color: var(--text-secondary);
            font-size: 14px;
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }

        .badge-info {
            background: rgba(13, 148, 136, 0.12);
            color: var(--primary-color);
        }

        .badge-secondary {
            background: rgba(100, 116, 139, 0.12);
            color: #475569;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 24px;
            transition: all var(--transition-normal) var(--ease-smooth);
            box-shadow: var(--shadow-card);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-4px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-title {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all var(--transition-normal) var(--ease-smooth);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-icon.primary {
            background: rgba(13, 148, 136, 0.12);
            color: var(--primary-color);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.12);
            color: var(--secondary-color);
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-muted);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .stat-trend.positive {
            color: #10b981;
        }

        .stat-trend.negative {
            color: #ef4444;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .alert-danger {
            background: rgba(255, 107, 107, 0.15);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }

        .alert-warning {
            background: rgba(255, 221, 0, 0.15);
            border: 1px solid rgba(255, 221, 0, 0.3);
            color: var(--secondary-color);
        }

        .alert-info {
            background: rgba(0, 255, 153, 0.15);
            border: 1px solid rgba(0, 255, 153, 0.3);
            color: var(--primary-color);
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            background: var(--background-card);
            border: 1px solid rgba(0, 255, 153, 0.1);
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all var(--transition-fast);
        }

        .pagination a:hover {
            background: rgba(0, 255, 153, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination .active span {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #000;
        }

        /* Responsive */
        @media (max-width: 1280px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
                min-width: 240px;
            }

            .main-content {
                padding: 24px;
            }

            .admin-header {
                padding: 0 24px;
            }

            .header-search input {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                height: 100vh;
                z-index: 2000;
                transition: left var(--transition-normal);
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                padding: 16px;
            }

            .admin-header {
                padding: 0 16px;
                height: 60px;
            }

            .header-search {
                display: none;
            }

            .page-title {
                font-size: 22px;
            }

            .page-title i {
                font-size: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr !important;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 28px;
            }

            .card-header {
                padding: 16px 20px;
            }

            .card-body {
                padding: 20px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f9fafb;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 255, 153, 0.3);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 255, 153, 0.5);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--primary-color);
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Header -->
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="logo" style="text-decoration: none;">
                <div class="logo-icon">📚</div>
                <div class="logo-text">
                    <span class="logo-text-sub">THƯ VIỆN</span>
                    <span class="logo-text-main">LibNet</span>
                </div>
            </a>
        </div>

        <div class="header-right">
            <!-- Role Badge -->
            <div style="display: flex; align-items: center; gap: 8px; padding: 6px 14px; background: rgba(13, 148, 136, 0.08); border-radius: 20px;">
                <i class="fas fa-user-shield" style="color: var(--primary-color); font-size: 12px;"></i>
                <span style="font-size: 12px; font-weight: 600; color: var(--primary-color);">
                    {{ auth()->user()->isAdmin() ? 'Admin' : 'Nhân viên' }}
                </span>
            </div>
            
            <div class="user-menu">
                <button class="btn-user" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down" style="font-size: 10px; opacity: 0.6;"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <div style="padding: 14px 16px; border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 14px;">{{ auth()->user()->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('home') }}" class="dropdown-item">
                        <i class="fas fa-home"></i>
                        Về trang chủ
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-menu">
                <!-- Dashboard -->
                <div class="menu-section-title">DASHBOARD</div>
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tổng quan</span>
                </a>

                <!-- Quản lý dữ liệu -->
                <div class="menu-section-title">QUẢN LÝ DỮ LIỆU</div>
                <a href="{{ route('admin.books.index') }}"
                    class="menu-item {{ request()->routeIs('admin.books.*') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>Quản lý sách</span>
                </a>

                <!-- Quản lý kho -->
                <div class="menu-section-title">QUẢN LÝ KHO</div>
                @if(Route::has('admin.inventory.index'))
                    <a href="{{ route('admin.inventory.index') }}"
                        class="menu-item {{ request()->routeIs('admin.inventory.index') || request()->routeIs('admin.inventory.show') || request()->routeIs('admin.inventory.edit') ? 'active' : '' }}">
                        <i class="fas fa-warehouse"></i>
                        <span>Danh sách kho</span>
                    </a>
                @endif
                @if(Route::has('admin.inventory.receipts'))
                    <a href="{{ route('admin.inventory.receipts') }}"
                        class="menu-item {{ request()->routeIs('admin.inventory.receipts.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        <span>Phiếu nhập kho</span>
                    </a>
                @endif
                @if(Route::has('admin.inventory.transactions'))
                    <a href="{{ route('admin.inventory.transactions') }}"
                        class="menu-item {{ request()->routeIs('admin.inventory.transactions') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Giao dịch kho</span>
                    </a>
                @endif
                @if(Route::has('admin.inventory.report'))
                    <a href="{{ route('admin.inventory.report') }}"
                        class="menu-item {{ request()->routeIs('admin.inventory.report') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Báo cáo kho</span>
                    </a>
                @endif
                @if(auth()->user()->isAdmin() && Route::has('admin.inventory.delete-requests.index'))
                    <a href="{{ route('admin.inventory.delete-requests.index') }}"
                       class="menu-item {{ request()->routeIs('admin.inventory.delete-requests.index') ? 'active' : '' }}">
                        <i class="fas fa-trash-alt"></i>
                        <span>Duyệt xóa sách</span>
                    </a>
                @endif

                <!-- Quản lý Độc giả - Only for Admin -->
                @if(!auth()->user()->isStaff())
                <div class="menu-section-title">PHÂN QUYỀN</div>
                @if(Route::has('admin.user-management.dashboard'))
                    <a href="{{ route('admin.user-management.dashboard') }}"
                        class="menu-item {{ request()->routeIs('admin.user-management.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Tổng quan</span>
                    </a>
                @endif
                <a href="{{ route('admin.users.index') }}"
                    class="menu-item {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.user-management.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Admin</span>
                </a>
                @endif

                <div class="menu-section-title">ĐẶT TRƯỚC</div>
                @if(Route::has('admin.inventory-reservations.index'))
                    <a href="{{ route('admin.inventory-reservations.index') }}"
                        class="menu-item {{ request()->routeIs('admin.inventory-reservations.*') ? 'active' : '' }}">
                        <i class="fas fa-bookmark"></i>
                        <span>Quản lý đặt trước</span>
                    </a>
                @endif

                <div class="menu-section-title">PHẠT</div>
                @if(Route::has('admin.fines.index'))
                    <a href="{{ route('admin.fines.index') }}"
                        class="menu-item {{ request()->routeIs('admin.fines.*') ? 'active' : '' }}">
                        <i class="fas fa-gavel"></i>
                        <span>Quản lý phạt</span>
                    </a>
                @endif

                <!-- Mượn trả sách -->
                <div class="menu-section-title">MƯỢN TRẢ SÁCH</div>
                <a href="{{ route('admin.borrows.index') }}"
                    class="menu-item {{ request()->routeIs('admin.borrows.*') ? 'active' : '' }}">
                    <i class="fas fa-book-reader"></i>
                    <span>Quản lý đơn mượn</span>
                </a>
                {{-- <a href="{{ route('admin.shipping_logs.index') }}" class="menu-item d-flex align-items-center gap-2 
                          {{ request()->routeIs('admin.shipping_logs.*') ? 'active' : '' }}">
                    <i class="bi bi-truck fs-5"></i>
                    <span>🚚 Giao Hàng (Đơn Mua)</span>
                </a> --}}
                @if(Route::has('admin.vouchers.index'))
                    <a href="{{ route('admin.vouchers.index') }}"
                        class="menu-item {{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Vouchers</span>
                    </a>
                @endif




                <!-- Tài chính -->


                <!-- Hệ thống -->
                <div class="menu-section-title">HỆ THỐNG</div>
                @can('manage-notifications')
                    @if(Route::has('admin.notifications.index'))
                        <a href="{{ route('admin.notifications.index') }}"
                            class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i>
                            <span>Thông báo</span>
                        </a>
                    @endif
                @endcan
                @if(Route::has('admin.banners.index'))
                    <a href="{{ route('admin.banners.index') }}"
                        class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <i class="fas fa-images"></i>
                        <span>Quản lý Banner</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Alerts replaced by Global Modal -->

            @yield('content')
        </div>
    </div>

    <script>
        // Toggle user menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target)) {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        // Toggle submenu
        function toggleSubmenu(id, element) {
            const submenu = document.getElementById(id);
            const arrow = element.querySelector('.arrow');

            submenu.classList.toggle('show');
            arrow.classList.toggle('rotated');
        }

        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    @include('partials.global-modal')
    @stack('scripts')
</body>

</html>