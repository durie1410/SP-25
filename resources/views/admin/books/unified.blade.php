@extends('layouts.admin')

@section('title', 'Quản lý sách tổng hợp')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Quản lý sách tổng hợp</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-books"></i> Quản lý sách tổng hợp</h2>
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

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="bookTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="borrow-books-tab" data-bs-toggle="tab" data-bs-target="#borrow-books" type="button" role="tab">
                <i class="fas fa-book-reader"></i> Sách mượn ({{ $borrowBooks->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="purchasable-books-tab" data-bs-toggle="tab" data-bs-target="#purchasable-books" type="button" role="tab">
                <i class="fas fa-shopping-cart"></i> Sách mua ({{ $purchasableBooks->total() }})
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="bookTabsContent">
        <!-- Sách mượn -->
        <div class="tab-pane fade show active" id="borrow-books" role="tabpanel">
            <div class="card mt-3">
                <div class="card-body">
                    @if($borrowBooks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh</th>
                                        <th>Tên sách</th>
                                        <th>Thể loại</th>
                                        <th>Tác giả</th>
                                        <th>Năm XB</th>
                                        <th>Loại</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowBooks as $book)
                                        <tr>
                                            <td>{{ $book->id }}</td>
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
                                                @if($book->mo_ta)
                                                    <br><small class="text-muted">{{ Str::limit($book->mo_ta, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $book->category->ten_the_loai ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $book->tac_gia }}</td>
                                            <td>{{ $book->nam_xuat_ban }}</td>
                                            <td>
                                                <span class="badge bg-success">Mượn</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.books.show', $book->id) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form action="{{ route('admin.books.destroy', $book->id) }}" 
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
                        <div class="d-flex justify-content-center">
                            {{ $borrowBooks->appends(request()->query())->links('vendor.pagination.admin') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có sách mượn nào</h5>
                            <p class="text-muted">Hãy thêm sách đầu tiên để bắt đầu quản lý.</p>
                            <a href="{{ route('admin.books.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Thêm sách đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sách mua -->
        <div class="tab-pane fade" id="purchasable-books" role="tabpanel">
            <div class="card mt-3">
                <div class="card-body">
                    @if($purchasableBooks->count() > 0)
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
                                        <th>Loại</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchasableBooks as $book)
                                        <tr>
                                            <td>{{ $book->id }}</td>
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
                                                @if($book->mo_ta)
                                                    <br><small class="text-muted">{{ Str::limit($book->mo_ta, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $book->tac_gia }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $book->formatted_price }}</span>
                                            </td>
                                            <td>
                                                @if($book->trang_thai === 'active')
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">Mua</span>
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
                        <div class="d-flex justify-content-center">
                            {{ $purchasableBooks->appends(request()->query())->links('vendor.pagination.admin') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có sách mua nào</h5>
                            <p class="text-muted">Hãy thêm sách đầu tiên để bắt đầu quản lý.</p>
                            <a href="{{ route('admin.purchasable-books.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm sách đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    border-radius: 0.375rem 0.375rem 0 0;
}

.nav-tabs .nav-link.active {
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endsection

