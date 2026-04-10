@extends('layouts.admin')

@section('title', 'Chi Tiết Nhà Xuất Bản - Admin')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.publishers.index') }}">Quản lý nhà xuất bản</a></li>
            <li class="breadcrumb-item active">{{ $publisher->ten_nha_xuat_ban }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-building"></i> {{ $publisher->ten_nha_xuat_ban }}</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.publishers.edit', $publisher->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="{{ route('admin.publishers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <!-- Publisher Info -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Thông tin nhà xuất bản</h5>
                        {!! $publisher->status_badge !!}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Tên nhà xuất bản:</strong></td>
                                    <td>{{ $publisher->ten_nha_xuat_ban }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày thành lập:</strong></td>
                                    <td>{{ $publisher->formatted_founded_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số điện thoại:</strong></td>
                                    <td>{{ $publisher->so_dien_thoai ?: 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        @if($publisher->email)
                                            <a href="mailto:{{ $publisher->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope"></i> {{ $publisher->email }}
                                            </a>
                                        @else
                                            Chưa cập nhật
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Website:</strong></td>
                                    <td>
                                        @if($publisher->website)
                                            <a href="{{ $publisher->website }}" target="_blank" class="text-decoration-none">
                                                <i class="fas fa-globe"></i> {{ $publisher->website }}
                                            </a>
                                        @else
                                            Chưa cập nhật
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Địa chỉ:</strong></td>
                                    <td>{{ $publisher->dia_chi ?: 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $publisher->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật cuối:</strong></td>
                                    <td>{{ $publisher->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($publisher->mo_ta)
                        <div class="mt-3">
                            <h6><strong>Mô tả:</strong></h6>
                            <p class="text-muted">{{ $publisher->mo_ta }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Books Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Sách của nhà xuất bản ({{ $publisher->books->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($publisher->books->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hình ảnh</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>Thể loại</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($publisher->books as $book)
                                        <tr>
                                            <td>
                                                @if($book->hinh_anh)
                                                    <img src="{{ $book->image_url }}" alt="{{ $book->ten_sach }}"
                                                         class="img-thumbnail" style="width: 50px; height: 70px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 70px;">
                                                        <i class="fas fa-book text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $book->ten_sach }}</strong>
                                                <br><small class="text-muted">{{ $book->ma_sach }}</small>
                                            </td>
                                            <td>
                                                @if($book->authors->count() > 0)
                                                    {{ $book->authors->pluck('ten_tac_gia')->join(', ') }}
                                                @else
                                                    <span class="text-muted">Chưa có tác giả</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($book->category)
                                                    <span class="badge bg-info">{{ $book->category->ten_the_loai }}</span>
                                                @else
                                                    <span class="text-muted">Chưa phân loại</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($book->trang_thai === 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.books.show', $book->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có sách nào</h5>
                            <p class="text-muted">Nhà xuất bản này chưa có sách nào trong hệ thống.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Logo -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Logo</h5>
                </div>
                <div class="card-body text-center">
                    @if($publisher->logo)
                        <img src="{{ asset('storage/' . $publisher->logo) }}" alt="{{ $publisher->ten_nha_xuat_ban }}" 
                             class="img-fluid rounded" style="max-height: 200px;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                            <div class="text-center">
                                <i class="fas fa-building fa-3x text-muted"></i>
                                <p class="mt-2 text-muted">Chưa có logo</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $publisher->books_count }}</h4>
                                <small class="text-muted">Tổng số sách</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $publisher->books->where('trang_thai', 'active')->count() }}</h4>
                            <small class="text-muted">Sách hoạt động</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Thao tác</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.publishers.edit', $publisher->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                        </a>
                        
                        <form action="{{ route('admin.publishers.toggle-status', $publisher->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ $publisher->trang_thai === 'active' ? 'secondary' : 'success' }} w-100"
                                    onclick="return confirm('Bạn có chắc chắn muốn {{ $publisher->trang_thai === 'active' ? 'tạm dừng' : 'kích hoạt' }} nhà xuất bản này?')">
                                <i class="fas fa-{{ $publisher->trang_thai === 'active' ? 'pause' : 'play' }}"></i> 
                                {{ $publisher->trang_thai === 'active' ? 'Tạm dừng' : 'Kích hoạt' }}
                            </button>
                        </form>

                        @if($publisher->canDelete())
                            <form action="{{ route('admin.publishers.destroy', $publisher->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa nhà xuất bản này? Hành động này không thể hoàn tác!')">
                                    <i class="fas fa-trash"></i> Xóa nhà xuất bản
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger w-100" disabled title="Không thể xóa vì đang có sách">
                                <i class="fas fa-trash"></i> Xóa nhà xuất bản
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
