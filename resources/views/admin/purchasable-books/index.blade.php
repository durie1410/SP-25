@extends('layouts.admin')

@section('title', 'Quản lý sách có thể mua')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Quản lý sách có thể mua</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-shopping-cart"></i> Quản lý sách có thể mua</h2>
                <a href="{{ route('admin.purchasable-books.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Thêm sách mới
                </a>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Books Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($books->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>Giá</th>
                                        <th>Định dạng</th>
                                        <th>Trạng thái</th>
                                        <th>Lượt bán</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($books as $book)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary fw-bold">{{ $book->id }}</span>
                                            </td>
                                            <td>
                                                @if($book->hinh_anh)
                                                    <img src="{{ $book->image_url }}"
                                                         width="50" height="70"
                                                         style="object-fit: cover; border-radius: 4px;"
                                                         alt="{{ $book->ten_sach }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 70px; border-radius: 4px;">
                                                        <i class="fas fa-book text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $book->ten_sach }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $book->nha_xuat_ban }} - {{ $book->nam_xuat_ban }}</small>
                                            </td>
                                            <td>{{ $book->tac_gia }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $book->formatted_price }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $book->dinh_dang }}</span>
                                            </td>
                                            <td>
                                                @if($book->trang_thai === 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $book->so_luong_ban }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.purchasable-books.show', $book->id) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.purchasable-books.edit', $book->id) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.purchasable-books.destroy', $book->id) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa sách này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $books->appends(request()->query())->links('vendor.pagination.admin') }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có sách nào</h5>
                            <p class="text-muted">Hãy thêm sách đầu tiên để bắt đầu quản lý.</p>
                            <a href="{{ route('admin.purchasable-books.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Thêm sách đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

