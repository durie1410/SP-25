@php
    $currentRoute = optional(request()->route())->getName();
    $user = auth()->user();
    // Load relationship reader để hiển thị "Sách đang mượn" nếu có
    if ($user) {
        $user->load('reader');
    }
@endphp
<aside class="account-sidebar">
    @if($user)
        <div class="user-profile">
            <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="username">{{ $user->name }}</div>
        </div>
    @endif
    <nav class="account-nav">
        <ul>
            @if($user && $user->reader)
                <li class="{{ $currentRoute === 'account.borrowed-books' ? 'active' : '' }}">
                    <a href="{{ route('account.borrowed-books') }}"><span class="icon">📚</span><span class="nav-label">Sách đang mượn</span></a>
                </li>
            @endif
            <li class="{{ $currentRoute === 'account.favorite-books' ? 'active' : '' }}">
                <a href="{{ route('account.favorite-books') }}"><span class="icon">❤️</span><span class="nav-label">Sách yêu thích</span></a>
            </li>
            <li class="{{ $currentRoute === 'account' ? 'active' : '' }}">
                <a href="{{ route('account') }}"><span class="icon">👤</span><span class="nav-label">Thông tin cá nhân</span></a>
            </li>
            <li class="{{ $currentRoute === 'account.change-password' ? 'active' : '' }}">
                <a href="{{ route('account.change-password') }}"><span class="icon">🔒</span><span class="nav-label">Đổi mật khẩu</span></a>
            </li>
            <li class="{{ in_array($currentRoute, ['orders.index', 'orders.detail', 'orders.show']) ? 'active' : '' }}">
                <a href="{{ route('orders.index') }}"><span class="icon">📋</span><span class="nav-label">Lịch sử đơn mượn</span></a>
            </li>
            <li class="{{ $currentRoute === 'reservation-cart.history' ? 'active' : '' }}">
                <a href="{{ route('reservation-cart.history') }}"><span class="icon">🗂️</span><span class="nav-label">Lịch sử đặt trước</span></a>
            </li>
            <li><a href="#" class="logout-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><span
                        class="icon">➡️</span><span class="nav-label">Đăng xuất</span></a></li>
        </ul>
    </nav>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</aside>