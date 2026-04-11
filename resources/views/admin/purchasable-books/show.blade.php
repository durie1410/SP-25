@extends('layouts.admin')

@section('title', 'Chi tiết sách có thể mua')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.purchasable-books.index') }}">Quản lý sách có thể mua</a></li>
            <li class="breadcrumb-item active">Chi tiết sách</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-book"></i> {{ $book->ten_sach }}</h2>
                <div class="btn-group">
                    <a href="{{ route('admin.purchasable-books.edit', $book->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="{{ route('admin.purchasable-books.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Book Details -->
    <div class="row">
        <!-- Book Image -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($book->hinh_anh)
                        <img src="{{ $book->image_url }}"
                             class="img-fluid rounded shadow"
                             style="max-height: 400px; object-fit: cover;"
                             alt="{{ $book->ten_sach }}">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                             style="height: 400px;">
                            <i class="fas fa-book fa-4x text-muted"></i>
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        @if($book->trang_thai === 'active')
                            <span class="badge bg-success fs-6">Hoạt động</span>
                        @else
                            <span class="badge bg-secondary fs-6">Tạm dừng</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin chi tiết</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Tên sách:</strong></td>
                                    <td>{{ $book->ten_sach }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tác giả:</strong></td>
                                    <td>{{ $book->tac_gia }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nhà xuất bản:</strong></td>
                                    <td>{{ $book->nha_xuat_ban }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Năm xuất bản:</strong></td>
                                    <td>{{ $book->nam_xuat_ban }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ISBN:</strong></td>
                                    <td>{{ $book->isbn ?: 'Chưa có' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số trang:</strong></td>
                                    <td>{{ $book->so_trang ?: 'Chưa có' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Giá:</strong></td>
                                    <td><span class="badge bg-success fs-6">{{ $book->formatted_price }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Định dạng:</strong></td>
                                    <td><span class="badge bg-info">{{ $book->dinh_dang }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Ngôn ngữ:</strong></td>
                                    <td>{{ $book->ngon_ngu ?: 'Chưa có' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kích thước file:</strong></td>
                                    <td>{{ $book->formatted_file_size ?: 'Chưa có' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lượt bán:</strong></td>
                                    <td><span class="badge bg-primary">{{ $book->so_luong_ban }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Lượt xem:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $book->so_luot_xem }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($book->mo_ta)
                        <div class="mt-4">
                            <h6><strong>Mô tả:</strong></h6>
                            <p class="text-muted">{{ $book->mo_ta }}</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6><strong>Thông tin hệ thống:</strong></h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Ngày tạo:</strong></td>
                                <td>{{ $book->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cập nhật lần cuối:</strong></td>
                                <td>{{ $book->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                                <h4>{{ $book->so_luong_ban }}</h4>
                                <p class="text-muted mb-0">Lượt bán</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-eye fa-2x text-info mb-2"></i>
                                <h4>{{ $book->so_luot_xem }}</h4>
                                <p class="text-muted mb-0">Lượt xem</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <h4>{{ number_format($book->danh_gia_trung_binh, 1) }}</h4>
                                <p class="text-muted mb-0">Đánh giá TB</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-money-bill-wave fa-2x text-primary mb-2"></i>
                                <h4>{{ $book->formatted_price }}</h4>
                                <p class="text-muted mb-0">Giá bán</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
