@extends('layouts.admin')

@section('title', 'Thống kê tổng hợp')

@section('content')
<style>
    .stats-page {
        padding: 28px;
        display: grid;
        gap: 20px;
        background: radial-gradient(circle at top left, rgba(20, 184, 166, 0.08) 0%, transparent 42%),
                    radial-gradient(circle at 90% 10%, rgba(14, 165, 233, 0.08) 0%, transparent 35%);
    }

    .stats-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .stats-header h1 {
        font-size: 28px;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .stats-header p {
        color: #475569;
        margin: 0;
    }

    .filter-panel {
        background: #fff;
        border: 1px solid #dbe3ea;
        border-radius: 14px;
        padding: 16px;
        display: grid;
        gap: 14px;
    }

    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tab {
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 999px;
        padding: 8px 14px;
        cursor: pointer;
        font-weight: 600;
        color: #334155;
    }

    .tab.active {
        background: #0f766e;
        color: #fff;
        border-color: #0f766e;
    }

    .custom-range {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .field {
        display: grid;
        gap: 8px;
    }

    .field label {
        font-size: 13px;
        color: #475569;
        font-weight: 600;
    }

    .field input {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .btn-apply {
        border: none;
        border-radius: 10px;
        background: #0ea5a4;
        color: #fff;
        font-weight: 600;
        padding: 10px 14px;
        cursor: pointer;
    }

    .kpi-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #dbe3ea;
        border-radius: 14px;
        padding: 16px;
        display: grid;
        gap: 10px;
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.08);
    }

    .kpi-card span {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .kpi-card strong {
        font-size: 24px;
    }

    .kpi-card.success {
        border-left: 4px solid #16a34a;
    }

    .kpi-card.danger {
        border-left: 4px solid #dc2626;
    }

    .comparison-row {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .compare-card {
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        border: 1px solid #dbe3ea;
        border-radius: 14px;
        padding: 16px;
    }

    .compare-card h3 {
        font-size: 15px;
        margin-bottom: 10px;
    }

    .compare-card p {
        font-size: 24px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .delta {
        font-size: 14px;
        padding: 4px 8px;
        border-radius: 999px;
        font-weight: 700;
    }

    .delta.up {
        background: #dcfce7;
        color: #166534;
    }

    .delta.down {
        background: #fee2e2;
        color: #b91c1c;
    }

    .charts-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .chart-card {
        background: #fff;
        border: 1px solid #dbe3ea;
        border-radius: 14px;
        padding: 16px;
        min-height: 340px;
        display: grid;
        grid-template-rows: auto 1fr;
    }

    .chart-card h3 {
        margin-bottom: 12px;
        font-size: 16px;
    }

    .chart-canvas-wrap {
        position: relative;
        width: 100%;
        min-height: 260px;
        height: 100%;
    }

    .chart-canvas-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .table-card {
        background: #fff;
        border: 1px solid #dbe3ea;
        border-radius: 14px;
        padding: 16px;
        overflow: auto;
    }

    .tables-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        text-align: left;
        padding: 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }

    th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #64748b;
    }

    .empty {
        text-align: center;
        color: #94a3b8;
        font-style: italic;
    }

    @media (max-width: 1024px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-page {
            padding: 16px;
        }

        .stats-header h1 {
            font-size: 22px;
        }

        .compare-card p {
            font-size: 20px;
        }

        .chart-canvas-wrap {
            min-height: 230px;
        }
    }
</style>

<div class="stats-page">
    <div class="stats-header">
        <div>
            <h1>Trang Thống Kê Riêng</h1>
            <p>Theo dõi doanh thu, đơn hàng và xu hướng thuê sách theo thời gian.</p>
            <p style="margin-top:6px; font-size: 13px; color:#0f766e; font-weight:600;">Nguồn dữ liệu đang dùng: {{ $dataSourceLabel === 'orders' ? 'Đơn mua (orders)' : 'Phiếu mượn (borrows)' }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.statistics.index') }}" class="filter-panel">
        <div class="filter-tabs">
            <button type="submit" name="filter" value="today" class="tab {{ $filter === 'today' ? 'active' : '' }}">Hôm nay</button>
            <button type="submit" name="filter" value="7days" class="tab {{ $filter === '7days' ? 'active' : '' }}">7 ngày</button>
            <button type="submit" name="filter" value="30days" class="tab {{ $filter === '30days' ? 'active' : '' }}">30 ngày</button>
            <button type="button" class="tab {{ $filter === 'custom' ? 'active' : '' }}" id="toggleCustom">Tùy chọn</button>
        </div>

        <div class="custom-range" id="customRange" style="{{ $filter === 'custom' ? '' : 'display:none;' }}">
            <div class="field">
                <label>Từ ngày</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}">
            </div>
            <div class="field">
                <label>Đến ngày</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}">
            </div>
            <button type="submit" name="filter" value="custom" class="btn-apply">Áp dụng</button>
        </div>
    </form>

    <div class="kpi-grid">
        <div class="kpi-card">
            <span>Doanh thu hôm nay</span>
            <strong>{{ number_format($revenueStats['today'], 0, ',', '.') }} đ</strong>
        </div>
        <div class="kpi-card">
            <span>Doanh thu tháng này</span>
            <strong>{{ number_format($revenueStats['this_month'], 0, ',', '.') }} đ</strong>
        </div>
        <div class="kpi-card">
            <span>Doanh thu năm nay</span>
            <strong>{{ number_format($revenueStats['this_year'], 0, ',', '.') }} đ</strong>
        </div>
        <div class="kpi-card">
            <span>Số đơn trong kỳ</span>
            <strong>{{ number_format($orderStats['total_orders']) }}</strong>
        </div>
        <div class="kpi-card success">
            <span>Tỷ lệ hoàn thành</span>
            <strong>{{ number_format($orderStats['completion_rate'], 2) }}%</strong>
        </div>
        <div class="kpi-card danger">
            <span>Tỷ lệ huỷ</span>
            <strong>{{ number_format($orderStats['cancel_rate'], 2) }}%</strong>
        </div>
    </div>

    <div class="comparison-row">
        <div class="compare-card">
            <h3>So sánh doanh thu tháng này vs tháng trước</h3>
            <p>
                {{ number_format($monthComparison['this_month_revenue'], 0, ',', '.') }} đ
                <span class="delta {{ $monthComparison['revenue_change_percent'] >= 0 ? 'up' : 'down' }}">
                    {{ $monthComparison['revenue_change_percent'] >= 0 ? '+' : '' }}{{ number_format($monthComparison['revenue_change_percent'], 2) }}%
                </span>
            </p>
        </div>
        <div class="compare-card">
            <h3>So sánh số đơn tháng này vs tháng trước</h3>
            <p>
                {{ number_format($monthComparison['this_month_orders']) }} đơn
                <span class="delta {{ $monthComparison['orders_change_percent'] >= 0 ? 'up' : 'down' }}">
                    {{ $monthComparison['orders_change_percent'] >= 0 ? '+' : '' }}{{ number_format($monthComparison['orders_change_percent'], 2) }}%
                </span>
            </p>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <h3>Line chart: Doanh thu theo ngày</h3>
            <div class="chart-canvas-wrap">
                <canvas id="lineRevenueChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Bar chart: Số đơn theo tháng</h3>
            <div class="chart-canvas-wrap">
                <canvas id="barOrdersChart"></canvas>
            </div>
        </div>
    </div>

    <div class="tables-grid">
        <div class="table-card">
            <h3>Top sách được thuê nhiều nhất</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên sách</th>
                        <th>Lượt thuê</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topBooks as $index => $book)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $book->ten_sach }}</td>
                        <td>{{ number_format($book->rent_count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="empty">Không có dữ liệu trong khoảng thời gian đã chọn.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h3>Top người dùng thuê nhiều nhất</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Người dùng</th>
                        <th>Số đơn thuê</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topUsers as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->ho_ten }}</td>
                        <td>{{ number_format($user->rent_count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="empty">Không có dữ liệu trong khoảng thời gian đã chọn.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const lineLabels = @json($lineChart['labels']);
    const lineValues = @json($lineChart['values']);
    const barLabels = @json($barChart['labels']);
    const barValues = @json($barChart['values']);
    const isMobile = window.matchMedia('(max-width: 768px)').matches;

    function formatMoney(value) {
        return Number(value || 0).toLocaleString('vi-VN') + ' đ';
    }

    function shortDateLabel(label) {
        if (!label || typeof label !== 'string') return label;
        const parts = label.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}`;
        }
        return label;
    }

    const lineDisplayLabels = lineLabels.map(shortDateLabel);

    new Chart(document.getElementById('lineRevenueChart'), {
        type: 'line',
        data: {
            labels: lineDisplayLabels,
            datasets: [{
                label: 'Doanh thu (đ)',
                data: lineValues,
                borderColor: '#0ea5a4',
                backgroundColor: 'rgba(14, 165, 164, 0.18)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        title: (ctx) => lineLabels[ctx[0].dataIndex] || '',
                        label: (ctx) => ` ${formatMoney(ctx.parsed.y)}`,
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: isMobile ? 6 : 10,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatMoney(value),
                    },
                },
            }
        }
    });

    new Chart(document.getElementById('barOrdersChart'), {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                label: 'Số đơn',
                data: barValues,
                backgroundColor: 'rgba(2, 132, 199, 0.85)',
                borderColor: '#0369a1',
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 34,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${Number(ctx.parsed.y || 0).toLocaleString('vi-VN')} đơn`,
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        maxRotation: isMobile ? 35 : 0,
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: isMobile ? 6 : 12,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
            },
        }
    });

    const toggleCustom = document.getElementById('toggleCustom');
    const customRange = document.getElementById('customRange');
    toggleCustom?.addEventListener('click', function() {
        customRange.style.display = customRange.style.display === 'none' || customRange.style.display === '' ? 'grid' : 'none';
    });
</script>
@endpush
