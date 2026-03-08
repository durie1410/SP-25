@extends('layouts.admin')

@section('title', 'Báo Cáo Thống Kê - Admin')

@section('content')
<div class="admin-table">
    <h3><i class="fas fa-chart-bar"></i> Báo cáo thống kê</h3>
<div class="flex gap-2 mb-4">
    <a href="/admin/reports/export-csv" class="bg-green-500 text-white px-4 py-2 rounded">
        Xuất CSV
    </a>

    <a href="/admin/reports/export-pdf" class="bg-red-500 text-white px-4 py-2 rounded">
        Xuất PDF
    </a>
</div>
    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5>{{ $totalBooks }}</h5>
                    <small>Tổng sách</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5>{{ $totalReaders }}</h5>
                    <small>Tổng độc giả</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5>{{ $totalBorrows }}</h5>
                    <small>Tổng lượt mượn</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h5>{{ $activeBorrows }}</h5>
                    <small>Đang mượn</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h5>{{ $overdueBorrows }}</h5>
                    <small>Quá hạn</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h5>{{ $categoryStats->count() }}</h5>
                    <small>Thể loại</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ thống kê theo tháng -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Thống kê mượn/trả theo tháng</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top sách và độc giả -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book"></i> Top sách được mượn nhiều nhất</h5>
                </div>
                <div class="card-body">
                    @if($topBooks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Sách</th>
                                        <th>Số lượt mượn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topBooks as $item)
                                    <tr>
                                        <td>{{ $item->book->ten_sach }}</td>
                                        <td><span class="badge bg-primary">{{ $item->borrow_count }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Top độc giả mượn nhiều nhất</h5>
                </div>
                <div class="card-body">
                    @if($topReaders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Độc giả</th>
                                        <th>Số lượt mượn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topReaders as $item)
                                    <tr>
                                        <td>{{ $item->reader->ho_ten }}</td>
                                        <td><span class="badge bg-success">{{ $item->borrow_count }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Menu báo cáo chi tiết -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-file-alt"></i> Báo cáo chi tiết</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.borrows') }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-exchange-alt"></i> Báo cáo mượn/trả sách
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.readers') }}" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-users"></i> Báo cáo độc giả
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.books') }}" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-book"></i> Báo cáo sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Biểu đồ thống kê theo tháng
    const monthlyData = @json($monthlyStats);
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.label),
            datasets: [{
                label: 'Số lượt mượn',
                data: monthlyData.map(item => item.borrows),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Số lượt trả',
                data: monthlyData.map(item => item.returns),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
</script>

@endsection



