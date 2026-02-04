<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tài khoản') - Nhà Xuất Bản Xây Dựng</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
    @stack('styles')
</head>

<body>
    @include('components.frontend-header')

    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <div class="breadcrumb-container">
            <a href="{{ route('home') }}" class="breadcrumb-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">@yield('breadcrumb', 'Tài khoản')</span>
        </div>
    </nav>

    <main class="account-container">
        @include('account._sidebar')

        <section class="account-content">
            <!-- Alerts replaced by Global Modal -->

            @yield('content')
        </section>
    </main>

    @include('components.footer')
    @include('partials.global-modal')
    @stack('scripts')
</body>

</html>