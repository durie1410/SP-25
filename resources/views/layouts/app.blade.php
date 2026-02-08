<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Thư Viện Online')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --text-color: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --background-color: #f8fafc;
            --card-bg: #ffffff;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            color: var(--text-color);
            line-height: 1.5;
        }

        .container-fluid {
            padding: 24px 16px;
        }

        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid var(--border-color);
            box-shadow: 0 -1px 3px 0 rgba(0, 0, 0, 0.05);
            z-index: 1000;
            display: none;
        }

        .mobile-nav-item {
            flex: 1;
            text-align: center;
            padding: 12px 5px;
            font-weight: 500;
            color: var(--secondary-color);
            text-decoration: none;
            transition: all .2s;
        }

        .mobile-nav-item.active,
        .mobile-nav-item:hover {
            color: var(--primary-color);
            background: #eff6ff;
        }

        .mobile-nav-item i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 4px;
        }

        .mobile-nav-item span {
            font-size: 0.75rem;
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg) !important;
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            background: #f1f5f9;
            color: var(--text-color);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn {
            border-radius: var(--radius-md);
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #fff !important;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            transform: translateY(-1px);
        }

        .form-control {
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .table-responsive {
            border-radius: var(--radius-md);
            background: #fff;
            border: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .mobile-nav {
                display: flex;
            }

            body {
                padding-bottom: 70px;
            }
        }

        @media (min-width:1025px) {
            .container-fluid {
                max-width: 1280px;
                margin: 0 auto;
            }
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>

<body>
    @stack('scripts')

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Main Content -->
    <div class="container-fluid">
        @yield('content')
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        <a href="{{ route('books.public') }}"
            class="mobile-nav-item {{ request()->routeIs('books.*') ? 'active' : '' }}">
            <i class="fas fa-book"></i>
            <span>Sách</span>
        </a>
        <a href="{{ route('categories.index') }}"
            class="mobile-nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            <span>Danh mục</span>
        </a>
        @auth
            <a href="{{ route('dashboard') }}"
                class="mobile-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Tài khoản</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="mobile-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <i class="fas fa-sign-in-alt"></i>
                <span>Đăng nhập</span>
            </a>
            <a href="{{ route('register') }}" class="mobile-nav-item {{ request()->routeIs('register') ? 'active' : '' }}">
                <i class="fas fa-user-plus"></i>
                <span>Đăng ký</span>
            </a>
        @endauth
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Mobile utilities
        const MobileUtils = {
            // Show loading spinner
            showLoading: function () {
                document.getElementById('loadingSpinner').style.display = 'block';
            },

            // Hide loading spinner
            hideLoading: function () {
                document.getElementById('loadingSpinner').style.display = 'none';
            },

            // Show toast notification
            showToast: function (message, type = 'info') {
                const toastContainer = document.getElementById('toastContainer');
                const toastId = 'toast-' + Date.now();

                const toastHtml = `
                    <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class="fas fa-${this.getToastIcon(type)} text-${type} me-2"></i>
                            <strong class="me-auto">Thông báo</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;

                toastContainer.insertAdjacentHTML('beforeend', toastHtml);

                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement);
                toast.show();

                // Auto remove after 5 seconds
                setTimeout(() => {
                    toastElement.remove();
                }, 5000);
            },

            getToastIcon: function (type) {
                const icons = {
                    'success': 'check-circle',
                    'error': 'exclamation-circle',
                    'warning': 'exclamation-triangle',
                    'info': 'info-circle'
                };
                return icons[type] || 'info-circle';
            },

            // Handle form submissions with loading
            handleFormSubmit: function (formSelector) {
                const forms = document.querySelectorAll(formSelector);
                forms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        MobileUtils.showLoading();
                    });
                });
            },

            // Initialize mobile features
            init: function () {
                this.handleFormSubmit('form');

                // Add touch feedback for buttons
                document.querySelectorAll('.btn').forEach(btn => {
                    btn.addEventListener('touchstart', function () {
                        this.style.transform = 'scale(0.95)';
                    });

                    btn.addEventListener('touchend', function () {
                        this.style.transform = 'scale(1)';
                    });
                });
            }
        };

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            MobileUtils.init();
            // Hide loading spinner when page is fully loaded
            MobileUtils.hideLoading();
        });

        // Also hide loading spinner when window is fully loaded
        window.addEventListener('load', function () {
            MobileUtils.hideLoading();
        });
    </script>

 
        @include('partials.global-modal')

    @yield('scripts')
    @stack('scripts')
</body>
</html>

</body>

</html>