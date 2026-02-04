@extends('layouts.admin')

@section('title', 'Dashboard - Quản Lý Thư Viện LibNet')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-pie"></i>
        Dashboard
    </h1>
    <p class="page-subtitle">
        Tổng quan hệ thống quản lý thư viện • {{ now()->format('d/m/Y') }} | <span id="current-time">{{ now()->format('H:i') }}</span>
    </p>
</div>

<!-- Stats Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <!-- Total Books -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Tổng Sách</div>
            <div class="stat-icon primary">
                <i class="fas fa-book"></i>
            </div>
        </div>
        <div class="stat-value">{{ number_format($totalBooks ?? 0) }}</div>
        <div class="stat-label">Quyển trong hệ thống</div>
        <div class="stat-trend positive">
            <i class="fas fa-check-circle"></i>
            <span>Đang hoạt động</span>
        </div>
    </div>
    
    <!-- Currently Borrowing -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Đang Mượn</div>
            <div class="stat-icon success">
                <i class="fas fa-hand-holding"></i>
            </div>
        </div>
        <div class="stat-value">{{ number_format($totalBorrowingReaders ?? 0) }}</div>
        <div class="stat-label">Sách đang cho mượn</div>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i>
            <span>Hoạt động tốt</span>
        </div>
    </div>

    <!-- Categories -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Thể Loại</div>
            <div class="stat-icon warning">
                <i class="fas fa-tags"></i>
            </div>
        </div>
        <div class="stat-value">{{ number_format(count($categoryStats ?? [])) }}</div>
        <div class="stat-label">Danh mục sách</div>
        <div class="stat-trend positive">
            <i class="fas fa-layer-group"></i>
            <span>Phân loại đầy đủ</span>
        </div>
    </div>

    <!-- Today Activity -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">Hôm Nay</div>
            <div class="stat-icon danger">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="stat-value">{{ count($recentActivities ?? []) }}</div>
        <div class="stat-label">Hoạt động gần đây</div>
        <div class="stat-trend positive">
            <i class="fas fa-clock"></i>
            <span>Cập nhật liên tục</span>
        </div>
    </div>
</div>

<!-- Financial Summary Section - Only for Admin -->
@if(!auth()->user()->isStaff())
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-wallet"></i>
            Tổng Hợp Doanh Thu
        </h3>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <!-- Total Revenue -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(13, 148, 136, 0.08), rgba(13, 148, 136, 0.04)); border-color: rgba(13, 148, 136, 0.2);">
                <div class="stat-header">
                    <div class="stat-title">Tổng Doanh Thu</div>
                    <div class="stat-icon primary">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="stat-value" style="color: var(--primary-color); font-size: 28px;">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">VNĐ • Từ mượn sách</div>
            </div>
            
            <!-- Monthly Revenue -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.04)); border-color: rgba(16, 185, 129, 0.2);">
                <div class="stat-header">
                    <div class="stat-title">Tháng Này</div>
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-value" style="color: #10b981; font-size: 28px;">{{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">VNĐ • Doanh thu tháng</div>
                @if(isset($revenueChangePercent) && $revenueChangePercent != 0)
                <div class="stat-trend {{ $revenueChangePercent > 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $revenueChangePercent > 0 ? 'up' : 'down' }}"></i>
                    <span>{{ number_format(abs($revenueChangePercent), 1) }}% so với tháng trước</span>
                </div>
                @endif
            </div>
            
            <!-- Today Revenue -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(245, 158, 11, 0.04)); border-color: rgba(245, 158, 11, 0.2);">
                <div class="stat-header">
                    <div class="stat-title">Hôm Nay</div>
                    <div class="stat-icon warning">
                        <i class="fas fa-sun"></i>
                    </div>
                </div>
                <div class="stat-value" style="color: #f59e0b; font-size: 28px;">{{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">VNĐ • Doanh thu hôm nay</div>
            </div>
            
            <!-- Fines Paid -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.04)); border-color: rgba(59, 130, 246, 0.2);">
                <div class="stat-header">
                    <div class="stat-title">Tiền Phạt</div>
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="stat-value" style="color: #3b82f6; font-size: 28px;">{{ number_format($totalFinesPaid ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">VNĐ • Đã thu</div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Borrow Chart -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Thống Kê Mượn Sách
                </h3>
            </div>
            <select class="form-select" id="chartPeriod" style="width: auto; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                <option value="7">7 ngày qua</option>
                <option value="30" selected>30 ngày qua</option>
                <option value="90">3 tháng qua</option>
                <option value="365">1 năm qua</option>
            </select>
        </div>
        <div class="card-body" style="height: 280px;">
            <canvas id="borrowChart"></canvas>
        </div>
    </div>
    
    <!-- Category Chart -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">
                    <i class="fas fa-pie-chart"></i>
                    Phân Bố Thể Loại
                </h3>
            </div>
        </div>
        <div class="card-body" style="height: 280px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Revenue Chart Row - Only for Admin -->
@if(!auth()->user()->isStaff())
<div style="margin-bottom: 24px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i>
                Biểu Đồ Doanh Thu
            </h3>
            <select class="form-select" id="revenueChartPeriod" style="width: auto; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                <option value="7">7 ngày qua</option>
                <option value="30" selected>30 ngày qua</option>
                <option value="90">3 tháng qua</option>
                <option value="365">1 năm qua</option>
            </select>
        </div>
        <div class="card-body" style="height: 280px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>
@endif

<!-- Activity and System Info Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Hoạt Động Gần Đây
            </h3>
            <a href="{{ route('admin.logs.index') }}" style="color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: 500;">
                Xem tất cả <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
            </a>
        </div>
        <div style="max-height: 360px; overflow-y: auto;">
            @forelse($recentActivities ?? [] as $activity)
                <a href="{{ $activity['action_url'] }}" class="activity-item" style="padding: 16px 24px; {{ !$loop->last ? 'border-bottom: 1px solid var(--border-color);' : '' }} display: flex; align-items: flex-start; gap: 14px; transition: all 0.2s; text-decoration: none; color: inherit;" onmouseover="this.style.background='rgba(13, 148, 136, 0.04)'" onmouseout="this.style.background='transparent'">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ $activity['bg_color'] }}; display: flex; align-items: center; justify-content: center; color: {{ $activity['icon_color'] }}; flex-shrink: 0;">
                        <i class="{{ $activity['icon'] }}" style="font-size: 14px;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="color: var(--text-primary); font-size: 14px; margin-bottom: 4px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $activity['title'] }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-clock" style="font-size: 10px;"></i>
                            <span>{{ $activity['time']->diffForHumans() }}</span>
                            <span style="color: {{ $activity['text_color'] }}; font-weight: 500; margin-left: 4px;">{{ $activity['action_text'] }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div style="padding: 48px 24px; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 12px; opacity: 0.4;"></i>
                    <p style="margin: 0; font-size: 14px;">Chưa có hoạt động nào</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- System Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-server"></i>
                Thông Tin Hệ Thống
            </h3>
            <span class="badge badge-success">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                Online
            </span>
        </div>
        <div>
            <div class="system-info-item" style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(13, 148, 136, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                        <i class="fas fa-code-branch"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 14px;">Phiên bản</span>
                </div>
                <span style="color: var(--text-primary); font-weight: 600; font-size: 14px;">v2.1.0</span>
            </div>
            
            <div class="system-info-item" style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 14px;">Uptime</span>
                </div>
                <span style="color: var(--text-primary); font-weight: 600; font-size: 14px;">15 ngày 8 giờ</span>
            </div>
            
            <div class="system-info-item" style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                        <i class="fas fa-database"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 14px;">Database</span>
                </div>
                <span style="color: var(--text-primary); font-weight: 600; font-size: 14px;">245.6 MB</span>
            </div>

            <div class="system-info-item" style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span style="color: var(--text-muted); font-size: 14px;">Response</span>
                </div>
                <span style="color: var(--text-primary); font-weight: 600; font-size: 14px;">45ms</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .stat-card {
        animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .card {
        animation: fadeInScale 0.5s cubic-bezier(0.4, 0, 0.2, 1) both;
    }

    .card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .stat-value {
        background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
    }

    .stat-trend {
        opacity: 0;
        animation: fadeIn 0.5s ease-out 0.8s both;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Activity items animation */
    .activity-item {
        animation: slideInRight 0.4s ease-out both;
    }

    .activity-item:nth-child(1) { animation-delay: 0.1s; }
    .activity-item:nth-child(2) { animation-delay: 0.2s; }
    .activity-item:nth-child(3) { animation-delay: 0.3s; }
    .activity-item:nth-child(4) { animation-delay: 0.4s; }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Update current time
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    // Update time every minute
    setInterval(updateCurrentTime, 60000);
    updateCurrentTime();
    
    // Chart data from Laravel
    const categoryData = @json($categoryStats ?? []);
    const labels = categoryData.map(item => item.ten_the_loai || item.name || 'Unknown');
    const data = categoryData.map(item => item.books_count || item.count || 0);
    
    // Revenue chart data
    const monthlyRevenueData = @json($monthlyRevenueStats ?? []);
    
    // Monthly borrow statistics data
    const monthlyBorrowData = @json($monthlyBorrowStats ?? []);
    
    // Initialize charts when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Borrow Chart (Line Chart)
        const borrowCtx = document.getElementById('borrowChart');
        if (borrowCtx && typeof Chart !== 'undefined') {
            // Sử dụng dữ liệu thực từ database
            const borrowLabels = monthlyBorrowData.length > 0 
                ? monthlyBorrowData.map(item => item.label) 
                : ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
            const borrowCounts = monthlyBorrowData.length > 0 
                ? monthlyBorrowData.map(item => item.count) 
                : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            
            let borrowChartInstance =                     new Chart(borrowCtx, {
                    type: 'line',
        data: {
                        labels: borrowLabels,
            datasets: [{
                            label: 'Sách mượn',
                        data: borrowCounts,
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.08)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                        pointBackgroundColor: '#0d9488',
                            pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
            }]
        },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                            display: false
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#fff',
                                bodyColor: '#e2e8f0',
                            borderColor: '#0d9488',
                                borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                                    drawBorder: false
                                },
                                ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                                }
                            },
                            x: {
                                grid: {
                                display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                            }
                        }
                        }
                    }
                });
            
            // Xử lý thay đổi period cho biểu đồ mượn sách
            const chartPeriodSelect = document.getElementById('chartPeriod');
            if (chartPeriodSelect && borrowChartInstance) {
                chartPeriodSelect.addEventListener('change', function() {
                    const period = parseInt(this.value);
                    // Có thể thêm logic để reload dữ liệu theo period nếu cần
                    // Hiện tại giữ nguyên dữ liệu 12 tháng
                });
            }
        }

        // Category Chart (Doughnut Chart)
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx && typeof Chart !== 'undefined') {
                if (labels.length > 0) {
                    const colors = [
                    '#0d9488',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#10b981',
                    '#3b82f6',
                    '#ec4899',
                    '#6366f1'
                    ];
                    
                    new Chart(categoryCtx, {
                        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                                backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#fff'
            }]
        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 16,
                                    color: '#64748b',
                                    font: { size: 11 }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    titleColor: '#fff',
                                    bodyColor: '#e2e8f0',
                                borderColor: '#0d9488',
                                    borderWidth: 1,
                                cornerRadius: 8,
                                padding: 12
                            }
                        },
                        cutout: '60%'
                    }
                });
                } else {
                    categoryCtx.parentElement.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
                        <i class="fas fa-chart-pie" style="font-size: 40px; margin-bottom: 12px; opacity: 0.4;"></i>
                        <p style="margin: 0; font-size: 14px;">Chưa có dữ liệu</p>
                        </div>
                    `;
                }
        }

        // Revenue Chart (Bar Chart)
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx && typeof Chart !== 'undefined' && monthlyRevenueData.length > 0) {
            const revenueLabels = monthlyRevenueData.map(item => item.label);
            const revenueValues = monthlyRevenueData.map(item => item.revenue);
            
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: revenueLabels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: revenueValues,
                        backgroundColor: '#0d9488',
                        borderColor: '#0d9488',
                        borderWidth: 0,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#fff',
                            bodyColor: '#e2e8f0',
                            borderColor: '#0d9488',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 11 },
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#64748b',
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

