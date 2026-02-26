@php
    /**
     * @var \App\Models\Book $book
     */
@endphp

<header class="main-header">
    <div class="header-top">
        <div class="logo-section">
            <a href="{{ route('home') }}" class="logo-link">
                <div class="logo-icon-wrapper">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="url(#logo-gradient)"/>
                        <path d="M12 14h6v20h-6v-20zm9 0h6v20h-6v-20zm9 0h6v20h-6v-20z" fill="rgba(255,255,255,0.3)"/>
                        <path d="M14 16h20c1 0 2 1 2 2v12c0 1-1 2-2 2H14c-1 0-2-1-2-2V18c0-1 1-2 2-2z" fill="white"/>
                        <path d="M16 20h8v2h-8v-2zm0 4h12v2H16v-2zm0 4h6v2h-6v-2z" fill="url(#logo-gradient)"/>
                        <defs>
                            <linearGradient id="logo-gradient" x1="0" y1="0" x2="48" y2="48">
                                <stop offset="0%" stop-color="#0d9488"/>
                                <stop offset="100%" stop-color="#14b8a6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-part1">THƯ VIỆN</span>
                    <span class="logo-part2">LibNet</span>
                </div>
            </a>
        </div>
        <div class="hotline-section">
            <div class="hotline-item">
                <i class="fas fa-phone-alt hotline-icon"></i>
                <div class="hotline-info">
                    <span class="hotline-label">Hotline khách lẻ</span>
                    <a href="tel:0327888669" class="hotline-number">0327 888 669</a>
                </div>
            </div>
            <div class="hotline-divider"></div>
            <div class="hotline-item">
                <i class="fas fa-building hotline-icon"></i>
                <div class="hotline-info">
                    <span class="hotline-label">Hotline khách sỉ</span>
                    <a href="tel:02439741791" class="hotline-number">024 3974 1791</a>
                </div>
            </div>
        </div>
        <div class="user-actions">
            @auth
                <!-- Notification Bell -->
                <div class="notif-bell-wrapper" style="position: relative;">
                    <a href="#" class="cart-link" id="notif-bell" title="Thông báo">
                        <i class="fas fa-bell"></i>
                        <span class="cart-badge" id="notif-bell-count" style="display: none;">0</span>
                    </a>
                    <div class="notif-panel" id="notif-panel">
                        <div class="notif-header">
                            <h3>Thông báo</h3>
                            <a href="#" id="notif-mark-all">Đánh dấu đã đọc hết</a>
                        </div>
                        <div class="notif-list" id="notif-list">
                            <!-- JS will render items here -->
                        </div>
                    </div>
                </div>

                <a href="{{ route('reservation-cart.index') }}" class="cart-link" id="reservation-cart-link" title="Giỏ đặt trước">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Giỏ đặt trước</span>
                    <span class="cart-badge" id="reservation-cart-count" style="display: none;">0</span>
                </a>
                <div class="user-menu-dropdown" style="position: relative;">
                    <a href="#" class="auth-link user-menu-toggle">
                        <i class="fas fa-user-circle user-icon-fa"></i>
                        <span>{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="dropdown-user-info">
                                <span class="dropdown-username">{{ auth()->user()->name }}</span>
                                <span class="dropdown-email">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                        @if(auth()->user()->reader)
                            <a href="{{ route('account.borrowed-books') }}" class="dropdown-item">
                                <i class="fas fa-book-reader"></i> Sách đang mượn
                            </a>
                        @endif
                        <a href="{{ route('account') }}" class="dropdown-item">
                            <i class="fas fa-user-cog"></i> Thông tin tài khoản
                        </a>
                        <a href="{{ route('account.change-password') }}" class="dropdown-item">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </a>
                        <a href="{{ route('orders.index') }}" class="dropdown-item">
                            <i class="fas fa-history"></i> Lịch sử mua hàng
                        </a>
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'staff')
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item dashboard-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="auth-link login-btn">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="auth-link register-btn">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
            @endauth
        </div>
    </div>
    <div class="header-nav">
        <div class="search-bar search-bar-centered">
            <form action="{{ route('books.public') }}" method="GET" class="search-form">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Tìm kiếm sách, tác giả, thể loại..." 
                        value="{{ request('keyword') }}" class="search-input">
                </div>
                <button type="submit" class="search-button">
                    <span>Tìm kiếm</span>
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    // Cho phép click vào cả vùng xám bao quanh để focus ô tìm kiếm
    document.addEventListener('DOMContentLoaded', function () {
        var searchBar = document.querySelector('.search-bar');
        if (!searchBar) return;

        var searchInput = searchBar.querySelector('.search-input');
        if (!searchInput) return;

        searchBar.addEventListener('click', function (e) {
            // Giữ nguyên hành vi khi bấm nút "Tìm kiếm"
            if (e.target.closest('button')) return;
            searchInput.focus();
        });
    });
</script>

@auth
<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadReservationCartCount();
        initNotificationBell();
    });

    function loadReservationCartCount() {
        const badge = document.getElementById('reservation-cart-count');
        if (!badge) return;
        
        fetch('{{ route('reservation-cart.count') }}')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error loading cart count:', error));
    }

    function initNotificationBell() {
        const bell = document.getElementById('notif-bell');
        const badge = document.getElementById('notif-bell-count');
        const panel = document.getElementById('notif-panel');
        const list = document.getElementById('notif-list');
        const markAllBtn = document.getElementById('notif-mark-all');

        if (!bell || !badge || !panel || !list) return;

        function setBadge(n) {
            const c = Number(n) || 0;
            if (c > 0) {
                badge.textContent = String(c);
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        function fetchCount() {
            fetch('{{ route('notifications.count') }}')
                .then(r => r.json())
                .then(d => setBadge(d.count))
                .catch(() => {});
        }

        function markRead(id) {
            return fetch('{{ route('notifications.read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id })
            }).then(() => fetchCount());
        }

        function renderItems(items) {
            if (!items || items.length === 0) {
                list.innerHTML = '<div style="padding: 14px; color: #64748b;">Chưa có thông báo.</div>';
                return;
            }

            list.innerHTML = items.map(item => {
                const unread = !item.read_at;
                const title = (item.subject || 'Thông báo').replace(/</g, '&lt;');
                const body = (item.body || '').replace(/</g, '&lt;');
                const time = item.created_at ? new Date(item.created_at).toLocaleString('vi-VN') : '';

                return `
                    <div class="notif-item" data-id="${item.id}" style="padding: 12px 14px; border-bottom: 1px solid #e2e8f0; cursor: pointer; background: ${unread ? '#f0fdfa' : '#fff'};">
                        <div style="font-weight: 700; color: #0f172a; font-size: 13px; margin-bottom: 4px;">${title}</div>
                        <div style="color: #475569; font-size: 12px; line-height: 1.35;">${body}</div>
                        <div style="color: #94a3b8; font-size: 11px; margin-top: 6px;">${time}</div>
                    </div>
                `;
            }).join('');

            list.querySelectorAll('.notif-item').forEach(el => {
                el.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    markRead(id);
                    this.style.background = '#fff';
                });
            });
        }

        function fetchLatest() {
            list.innerHTML = '<div style="padding: 14px; color: #64748b;">Đang tải...</div>';
            fetch('{{ route('notifications.latest') }}?limit=10')
                .then(r => r.json())
                .then(d => renderItems(d.items || []))
                .catch(() => {
                    list.innerHTML = '<div style="padding: 14px; color: #ef4444;">Không tải được thông báo.</div>';
                });
        }

        bell.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            panel.classList.toggle('show');
            if (panel.classList.contains('show')) {
                fetchLatest();
                fetchCount();
            }
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== bell) {
                panel.classList.remove('show');
            }
        });

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    fetchLatest();
                    fetchCount();
                });
            });
        }

        // initial
        fetchCount();
    }
</script>
@endauth

<style>
    /* Modern Header Styles */
    .logo-link {
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .logo-icon-wrapper {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
    }
    
    .logo-icon-wrapper svg {
        width: 100%;
        height: 100%;
        filter: drop-shadow(0 2px 4px rgba(13, 148, 136, 0.3));
        transition: transform 0.3s ease;
    }
    
    .logo-link:hover .logo-icon-wrapper svg {
        transform: scale(1.05);
    }
    
    .hotline-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0d9488, #14b8a6);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .hotline-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .hotline-divider {
        width: 1px;
        height: 40px;
        background: linear-gradient(to bottom, transparent, #e2e8f0, transparent);
    }
    
    /* Centered Search Bar */
    .search-bar-centered {
        max-width: 700px;
        margin: 0 auto;
    }
    
    /* Search Improvements */
    .search-input-wrapper {
        position: relative;
        flex: 1;
    }
    
    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }
    
    .search-form .search-input {
        padding-left: 44px;
    }

    /* User Actions Dropdown Styling */
    .user-icon-fa {
        font-size: 20px;
        color: var(--primary-color);
    }
    
    .dropdown-arrow {
        font-size: 10px;
        margin-left: 4px;
        transition: transform 0.2s;
    }
    
    .user-menu-toggle:hover .dropdown-arrow {
        transform: rotate(180deg);
    }

    .user-menu-dropdown .user-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        min-width: 280px;
        z-index: 1000;
        overflow: hidden;
        animation: dropdownFade 0.2s ease;
    }
    
    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .user-menu-dropdown:hover .user-dropdown-menu {
        display: block;
    }
    
    .dropdown-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
        border-bottom: 1px solid #e2e8f0;
    }
    
    .dropdown-avatar {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #0d9488, #14b8a6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .dropdown-user-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .dropdown-username {
        font-weight: 600;
        color: var(--text-color);
        font-size: 14px;
    }
    
    .dropdown-email {
        font-size: 12px;
        color: #64748b;
    }
    
    .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 4px 0;
    }

    .user-menu-dropdown .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 14px;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
    }
    
    .user-menu-dropdown .dropdown-item i {
        width: 18px;
        color: #94a3b8;
        font-size: 14px;
        transition: color 0.2s;
    }

    .user-menu-dropdown .dropdown-item:hover {
        background: #f8fafc;
        color: var(--primary-color);
    }
    
    .user-menu-dropdown .dropdown-item:hover i {
        color: var(--primary-color);
    }
    
    .user-menu-dropdown .dropdown-item.dashboard-link {
        color: #0d9488;
    }
    
    .user-menu-dropdown .dropdown-item.dashboard-link i {
        color: #0d9488;
    }

    .user-menu-dropdown .dropdown-item.logout-btn {
        color: #dc2626;
    }
    
    .user-menu-dropdown .dropdown-item.logout-btn i {
        color: #dc2626;
    }

    .user-menu-dropdown .dropdown-item.logout-btn:hover {
        background: #fef2f2;
    }

    /* Notification bell */
    .notif-bell-wrapper .cart-link {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border: 1px solid var(--border-color);
        background: #fff;
        color: var(--primary-color);
    }

    .notif-bell-wrapper .cart-link:hover {
        background: rgba(13, 148, 136, 0.06);
        border-color: rgba(13, 148, 136, 0.35);
    }

    .notif-bell-wrapper .cart-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 999px;
        font-size: 11px;
        line-height: 18px;
        display: none;
        align-items: center;
        justify-content: center;
        background: #ef4444;
        color: #fff;
        border: 2px solid #fff;
    }

    .notif-panel {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        width: 360px;
        max-height: 420px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 14px 50px rgba(15, 23, 42, 0.15);
        z-index: 3000;
    }

    .notif-panel.show {
        display: block;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
    }

    .notif-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .notif-header a {
        font-size: 12px;
        color: #0d9488;
        text-decoration: none;
        font-weight: 600;
    }

    .notif-header a:hover {
        text-decoration: underline;
    }

    .notif-list {
        max-height: 360px;
        overflow-y: auto;
    }

    /* Login/Register buttons */
    .login-btn {
        background: transparent !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
    
    .login-btn:hover {
        background: var(--primary-color) !important;
        color: white !important;
    }
    
    .register-btn {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: white !important;
    }
    
    .register-btn:hover {
        background: var(--primary-hover) !important;
        border-color: var(--primary-hover) !important;
    }

    @media (max-width: 768px) {
        .hotline-section {
            display: none;
        }

        .logo-part1 {
            display: none;
        }
        
        .user-actions .auth-link span {
            display: none;
        }
    }
</style>