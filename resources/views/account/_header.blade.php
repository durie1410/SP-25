<header class="main-header">
    <div class="header-top">
        <div class="logo-section">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #e51d2e 0%, #c41e2f 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-right: 8px;">
                📚
            </div>
            <div class="logo-text">
                <span class="logo-part1">THƯ VIỆN</span>
                <span class="logo-part2">LibNet</span>
            </div>
        </div>
        <div class="hotline-section">
            <div class="hotline-item">
                <span class="hotline-label">Hotline khách lẻ:</span>
                <a href="tel:0327888669" class="hotline-number">0327888669</a>
            </div>
            <div class="hotline-item">
                <span class="hotline-label">Hotline khách sỉ:</span>
                <a href="tel:02439741791" class="hotline-number">02439741791 - 0327888669</a>
            </div>
        </div>
        <div class="user-actions">
            @auth
                <!-- Notification Bell -->
                <div class="notif-bell-wrapper" style="position: relative;">
                    <a href="#" class="cart-link" id="notif-bell" title="Thông báo">
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        <span class="cart-badge" id="notif-bell-count" style="display: none;">0</span>
                    </a>
                    <div class="notif-panel" id="notif-panel">
                        <div class="notif-header">
                            <h3>Thông báo</h3>
                            <a href="#" id="notif-mark-all">Đánh dấu đã đọc hết</a>
                        </div>
                        <div class="notif-list" id="notif-list"></div>
                    </div>
                </div>

                <a href="{{ route('reservation-cart.index') }}" class="cart-link" id="reservation-cart-link" title="Giỏ đặt trước">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    <span>Giỏ đặt trước</span>
                    <span class="cart-badge" id="reservation-cart-count" style="display: none;">0</span>
                </a>
                <div class="user-menu-dropdown" style="position: relative;">
                    <a href="#" class="auth-link user-menu-toggle">
                        <span class="user-icon">👤</span>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header"
                            style="padding: 12px 15px; border-bottom: 1px solid #eee; font-weight: 600; color: #333;">
                            <span class="user-icon">👤</span>
                            {{ auth()->user()->name }}
                        </div>
                        <a href="{{ route('account') }}" class="dropdown-item">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <span>Thông tin tài khoản</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <i class="fas fa-right-from-bracket" aria-hidden="true"></i>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
                <style>
                    .user-menu-dropdown {
                        position: relative;
                    }

                    .user-menu-dropdown .user-dropdown-menu {
                        display: none;
                        position: absolute;
                        top: calc(100% + 5px);
                        right: 0;
                        background: white;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                        min-width: 220px;
                        z-index: 1000;
                        overflow: hidden;
                    }

                    .user-menu-dropdown:hover .user-dropdown-menu {
                        display: block;
                    }

                    .user-menu-dropdown .dropdown-item {
                        display: block;
                        padding: 10px 15px;
                        color: #333;
                        text-decoration: none;
                        border-bottom: 1px solid #eee;
                        transition: background-color 0.2s;
                        cursor: pointer;
                    }

                    .user-menu-dropdown .dropdown-item:hover {
                        background-color: #f5f5f5;
                    }

                    .user-menu-dropdown .dropdown-item.logout-btn {
                        border: none;
                        background: none;
                        width: 100%;
                        text-align: left;
                        color: #d32f2f;
                        border-top: 1px solid #eee;
                        margin-top: 5px;
                    }

                    .user-menu-dropdown .dropdown-item.logout-btn:hover {
                        background-color: #ffebee;
                    }

                    .user-menu-dropdown .dropdown-item span {
                        margin-right: 8px;
                    }
                </style>
            @else
                <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a>
            @endauth
        </div>
    </div>
    <div class="header-nav">
        <div class="search-bar">
            <form action="{{ route('books.public') }}" method="GET" class="search-form">
                <input type="text" name="keyword" placeholder="Tìm sách, tác giả, sản phẩm mong muốn..."
                    value="{{ request('keyword') }}" class="search-input">
                <button type="submit" class="search-button">🔍 Tìm kiếm</button>
            </form>
        </div>
    </div>
</header>

@auth
    <script>
        // Load số lượng giỏ sách khi trang load
        document.addEventListener('DOMContentLoaded', function () {
            loadReservationCartCount();
        });

        function loadReservationCartCount() {
            fetch('{{ route('reservation-cart.count') }}')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('reservation-cart-count');
                    if (cartCountElement) {
                        const count = data.count || 0;
                        cartCountElement.textContent = count;
                        cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }

        // Hàm để cập nhật số lượng giỏ sách (có thể gọi từ các trang khác)
        function updateBorrowCartCount(count) {
            const cartCountElement = document.getElementById('borrow-cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'flex' : 'none';
            }
        }
    </script>
@endauth