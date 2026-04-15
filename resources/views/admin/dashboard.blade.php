@extends('layouts.admin')

@section('title', 'Dashboard - Quản Lý Thuê Sách LibNet')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-pie"></i>
        Dashboard
    </h1>
    <p class="page-subtitle">
        Tổng quan nhanh • {{ now()->format('d/m/Y H:i') }}
    </p>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            Chỉ Số Chính
        </h3>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Tổng người dùng</div>
                    <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-value">{{ number_format($overviewStats['total_users'] ?? 0) }}</div>
            </div>

            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Tổng số sách</div>
                    <div class="stat-icon success"><i class="fas fa-book"></i></div>
                </div>
                <div class="stat-value">{{ number_format($overviewStats['total_books'] ?? 0) }}</div>
            </div>

            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Tổng số đơn</div>
                    <div class="stat-icon warning"><i class="fas fa-file-invoice"></i></div>
                </div>
                <div class="stat-value">{{ number_format($overviewStats['total_orders'] ?? 0) }}</div>
            </div>

            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Tổng doanh thu</div>
                    <div class="stat-icon danger"><i class="fas fa-coins"></i></div>
                </div>
                <div class="stat-value" style="font-size: 24px;">{{ number_format((float) ($overviewStats['total_revenue'] ?? 0), 0, ',', '.') }}</div>
                <div class="stat-label">VNĐ</div>
            </div>

            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Số đơn hôm nay</div>
                    <div class="stat-icon" style="background: rgba(59,130,246,.12); color: #3b82f6;"><i class="fas fa-calendar-day"></i></div>
                </div>
                <div class="stat-value">{{ number_format($overviewStats['orders_today'] ?? 0) }}</div>
            </div>

            <div class="stat-card" style="min-height: 140px;">
                <div class="stat-header">
                    <div class="stat-title">Số đơn tháng này</div>
                    <div class="stat-icon" style="background: rgba(139,92,246,.12); color: #8b5cf6;"><i class="fas fa-calendar-alt"></i></div>
                </div>
                <div class="stat-value">{{ number_format($overviewStats['orders_this_month'] ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px;">
    <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
        <div class="card-header" style="padding: 12px 16px;">
            <h3 class="card-title" style="font-size: 14px;"><i class="fas fa-clock"></i> 5 đơn gần nhất</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách</th>
                            <th>Tổng tiền</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->order_number ?? ('#' . $order->id) }}</td>
                                <td>{{ $order->customer_name ?: ($order->user->name ?? 'N/A') }}</td>
                                <td>{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td>{{ optional($order->created_at)->format('d/m H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Chưa có đơn hàng.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
        <div class="card-header" style="padding: 12px 16px;">
            <h3 class="card-title" style="font-size: 14px;"><i class="fas fa-user-plus"></i> 5 người dùng mới nhất</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($newUsers ?? [] as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Chưa có người dùng mới.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
