@extends('layouts.admin')

@section('title', 'Quản Lý Sách - LibNet Admin')

@push('styles')
<style>
    /* Highlight animation for updated book */
    .book-updated-highlight {
        animation: highlightFade 3s ease-in-out;
        background: linear-gradient(90deg, rgba(0, 255, 153, 0.2) 0%, rgba(0, 255, 153, 0.05) 100%) !important;
    }
    
    @keyframes highlightFade {
        0% {
            background: rgba(0, 255, 153, 0.3);
            transform: scale(1.01);
        }
        50% {
            background: rgba(0, 255, 153, 0.2);
        }
        100% {
            background: transparent;
            transform: scale(1);
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-book-open"></i>
            Quản lý sách
        </h1>
        <p class="page-subtitle">Quản lý và theo dõi tất cả sách trong thư viện</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-success" style="font-size: 12px; padding: 6px 12px;">
                <i class="fas fa-check-circle"></i> Có trong kho: {{ $booksWithInventory ?? 0 }}
            </span>
            <span class="badge badge-warning" style="font-size: 12px; padding: 6px 12px;">
                <i class="fas fa-exclamation-triangle"></i> Chưa có trong kho: {{ $booksWithoutInventory ?? 0 }}
            </span>
            <a href="{{ route('admin.books.create') }}" class="btn btn-success btn-sm" title="Thêm sách mới">
                <i class="fas fa-plus"></i> Thêm sách
            </a>
            <form action="{{ route('admin.books.reset-ids') }}" 
                  method="POST" 
                  style="display: inline;"
                  onsubmit="return confirm('⚠️ CẢNH BÁO: Lệnh này sẽ sắp xếp lại ID của tất cả sách để liên tục từ 1 đến {{ $totalBooks ?? 0 }}.\\n\\nHãy đảm bảo bạn đã backup database trước!\\n\\nBạn có chắc chắn muốn tiếp tục?');">
                @csrf
                <button type="submit" 
                        class="btn btn-info btn-sm" 
                        title="Sắp xếp lại ID sách để liên tục từ 1">
                    <i class="fas fa-sort-numeric-up"></i> Sắp xếp lại ID
                </button>
            </form>
        </div>
        <span class="text-muted" style="font-size: 12px;">
            <i class="fas fa-info-circle"></i> Sách mới sẽ được tạo tự động khi nhập vào kho
        </span>
    </div>
</div>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i>
            Tìm kiếm & Lọc
        </h3>
    </div>
    <form action="{{ route('admin.books.index') }}" method="GET" style="padding: 25px; display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" 
                   name="keyword" 
                   value="{{ request('keyword') }}" 
                   class="form-control" 
                   placeholder="Tìm theo tên sách hoặc tác giả...">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="category_id" class="form-select">
                <option value="">-- Tất cả thể loại --</option>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ request('category_id') == $cate->id ? 'selected' : '' }}>
                        {{ $cate->ten_the_loai }}
                    </option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="inventory_status" class="form-select">
                <option value="">-- Tất cả sách --</option>
                <option value="has_inventory" {{ request('inventory_status') == 'has_inventory' ? 'selected' : '' }}>
                    Có trong kho
                </option>
                <option value="no_inventory" {{ request('inventory_status') == 'no_inventory' ? 'selected' : '' }}>
                    Chưa có trong kho
                </option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i>
            Lọc
        </button>
        <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
            <i class="fas fa-redo"></i>
            Reset
        </a>
    </form>
</div>

<!-- Books List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách sách
        </h3>
        <span class="badge badge-info">Tổng: {{ $books->total() }} sách</span>
    </div>
    
    @if($books->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Thông tin sách</th>
                        <th>Tác giả</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Loại</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                        <tr id="book-row-{{ $book->id }}" class="{{ session('updated_book_id') == $book->id ? 'book-updated-highlight' : '' }}">
                            <td>
                                <span class="badge badge-info">{{ $book->id }}</span>
                            </td>
                            <td>
                                @if($book->hinh_anh)
                                    @php
                                        // Clean path and build URL directly
                                        $imagePath = ltrim(str_replace('\\', '/', $book->hinh_anh), '/');
                                        // Strong cache busting: timestamp + milliseconds + random
                                        // This ensures the image URL is different every time
                                        $cacheBuster = $book->updated_at->timestamp . substr((string)microtime(true), -6) . mt_rand(1000, 9999);
                                        $imageUrl = asset('storage/' . $imagePath) . '?v=' . $cacheBuster;
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         width="50" 
                                         height="70" 
                                         style="object-fit: cover; border-radius: 8px; border: 1px solid rgba(0, 255, 153, 0.2);"
                                         alt="{{ $book->ten_sach }}"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 50px; height: 70px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); align-items: center; justify-content: center; border: 1px solid rgba(0, 255, 153, 0.2);">
                                        <i class="fas fa-book" style="color: #666;"></i>
                                    </div>
                                @else
                                    <div style="width: 50px; height: 70px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0, 255, 153, 0.2);">
                                        <i class="fas fa-book" style="color: #666;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 300px;">
                                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">
                                        {{ $book->ten_sach }}
                                    </div>
                                    <div style="font-size: 12px; color: #888; margin-bottom: 3px;">
                                        <i class="fas fa-tags"></i>
                                        {{ $book->category->ten_the_loai ?? 'N/A' }} • {{ $book->nam_xuat_ban }}
                                    </div>
                                    @if($book->mo_ta)
                                        <div style="font-size: 12px; color: #666;">
                                            {{ Str::limit($book->mo_ta, 60) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td style="color: var(--primary-color);">{{ $book->tac_gia }}</td>
                            <td>
                                <span class="badge badge-success">{{ $book->formatted_price }}</span>
                            </td>
                            <td>
                                @if($book->trang_thai === 'active')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i>
                                        Tạm dừng
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $hasInventory = $book->inventories()->exists();
                                @endphp
                                @if($hasInventory)
                                    <span class="badge badge-success">
                                        <i class="fas fa-warehouse"></i>
                                        Có trong kho
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Chưa có trong kho
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('admin.books.show', $book->id) }}" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.books.edit', $book->id) }}?page={{ request()->get('page', 1) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($book->trang_thai === 'active')
                                        <form action="{{ route('admin.books.hide', $book->id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn ẩn sách này?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Ẩn">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.books.unhide', $book->id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn hiển thị sách này?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-success" 
                                                    title="Hiện">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="padding: 20px;">
            {{ $books->appends(request()->query())->links('vendor.pagination.admin') }}
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0, 255, 153, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-book" style="font-size: 36px; color: var(--primary-color);"></i>
            </div>
            <h3 style="color: var(--text-primary); margin-bottom: 10px;">Chưa có sách nào</h3>
            <p style="color: #888; margin-bottom: 25px;">Hãy thêm sách đầu tiên để bắt đầu quản lý thư viện của bạn.</p>
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i>
                Thêm sách đầu tiên
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Scroll to updated book and add visual feedback
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('updated_book_id'))
            const updatedBookRow = document.getElementById('book-row-{{ session('updated_book_id') }}');
            if (updatedBookRow) {
                // Scroll to the updated book with smooth behavior
                setTimeout(() => {
                    updatedBookRow.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 100);
            }
        @endif
    });
</script>
@endpush

@endsection
