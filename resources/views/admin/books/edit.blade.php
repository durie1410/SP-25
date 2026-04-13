@extends('layouts.admin')

@section('title', 'Sửa Sách - Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-edit"></i>
            Chỉnh sửa sách
        </h1>
        <p class="page-subtitle">Cập nhật thông tin sách</p>
    </div>
    <div>
        <a href="{{ route('admin.books.index') }}{{ request()->has('page') ? '?page=' . request()->get('page') : '' }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Debug Info (chỉ hiển thị khi có lỗi upload ảnh) -->
@if(request()->get('debug') === '1')
<div class="card" style="margin-bottom: 20px; border: 2px solid #ffc107;">
    <div class="card-header" style="background: #fff3cd;">
        <h3 class="card-title">
            <i class="fas fa-bug"></i>
            Debug Info - Thông tin ảnh hiện tại
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <td><strong>Đường dẫn ảnh trong DB:</strong></td>
                <td><code>{{ $book->hinh_anh ?? 'NULL' }}</code></td>
            </tr>
            <tr>
                <td><strong>Full URL:</strong></td>
                <td><code>{{ $book->hinh_anh ? $book->image_url : 'N/A' }}</code></td>
            </tr>
            <tr>
                <td><strong>File tồn tại?</strong></td>
                <td>
                    @if($book->hinh_anh && \Storage::disk('public')->exists($book->hinh_anh))
                        <span class="badge badge-success">✓ CÓ</span>
                    @else
                        <span class="badge badge-danger">✗ KHÔNG</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Updated at:</strong></td>
                <td>{{ $book->updated_at }}</td>
            </tr>
        </table>
        <small class="text-muted">
            <i class="fas fa-info-circle"></i> 
            Để ẩn debug info, xóa <code>?debug=1</code> khỏi URL
        </small>
    </div>
</div>
@endif

<!-- Edit Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-book"></i>
            Thông tin sách
        </h3>
    </div>
    <div class="card-body">
        <form id="bookUpdateForm" action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            
            <!-- Lưu trang hiện tại để redirect về đúng trang sau khi update -->
            <input type="hidden" name="redirect_page" value="{{ request()->get('page', 1) }}">

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                        <input type="text" name="ten_sach" value="{{ old('ten_sach', $book->ten_sach) }}" class="form-control @error('ten_sach') is-invalid @enderror" required>
                        @error('ten_sach')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Thể loại <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cate)
                                <option value="{{ $cate->id }}" {{ old('category_id', $book->category_id) == $cate->id ? 'selected' : '' }}>
                                    {{ $cate->ten_the_loai }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                        <input type="text" name="tac_gia" value="{{ old('tac_gia', $book->tac_gia) }}" class="form-control @error('tac_gia') is-invalid @enderror" required>
                        @error('tac_gia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Năm xuất bản <span class="text-danger">*</span></label>
                        <input type="number" name="nam_xuat_ban" value="{{ old('nam_xuat_ban', $book->nam_xuat_ban) }}" class="form-control @error('nam_xuat_ban') is-invalid @enderror" required>
                        @error('nam_xuat_ban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nhà xuất bản</label>
                        <select name="nha_xuat_ban_id" class="form-control @error('nha_xuat_ban_id') is-invalid @enderror">
                            <option value="">-- Chọn nhà xuất bản --</option>
                            @foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}" {{ old('nha_xuat_ban_id', $book->nha_xuat_ban_id) == $publisher->id ? 'selected' : '' }}>
                                    {{ $publisher->ten_nha_xuat_ban }}
                                </option>
                            @endforeach
                        </select>
                        @error('nha_xuat_ban_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Ảnh bìa</label>
                <div id="image-preview-container" class="mb-2">
                    @if($book->hinh_anh)
                        @php
                            $imageUrl = $book->image_url;
                            if ($imageUrl) {
                                $imageUrl .= (strpos($imageUrl, '?') !== false ? '&' : '?') . 'v=' . $book->updated_at->timestamp . '&r=' . mt_rand();
                            }
                        @endphp
                        <img id="book-cover-image"
                             src="{{ $imageUrl }}" 
                             width="120" height="160" 
                             style="object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                             alt="{{ $book->ten_sach }}"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div id="book-cover-placeholder" style="display: none; width: 120px; height: 160px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); align-items: center; justify-content: center; border: 1px solid rgba(0, 255, 153, 0.2);">
                            <i class="fas fa-book" style="color: #666; font-size: 48px;"></i>
                        </div>
                    @else
                        <img id="book-cover-image" 
                             src="" 
                             width="120" height="160" 
                             style="object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: none;"
                             alt="Preview">
                        <div id="book-cover-placeholder" style="width: 120px; height: 160px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0, 255, 153, 0.2);">
                            <i class="fas fa-book" style="color: #666; font-size: 48px;"></i>
                        </div>
                    @endif
                </div>
                <input type="file" name="hinh_anh" id="hinh_anh_input" class="form-control @error('hinh_anh') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp">
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Chọn ảnh mới để thay thế ảnh hiện tại (tối đa 2MB, định dạng: JPG, PNG, WEBP)
                </small>
                @error('hinh_anh')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="mo_ta" class="form-control @error('mo_ta') is-invalid @enderror" rows="4" placeholder="Nhập mô tả về sách...">{{ old('mo_ta', $book->mo_ta) }}</textarea>
                @error('mo_ta')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Nội dung đọc thử</label>
                <textarea name="preview_content" class="form-control @error('preview_content') is-invalid @enderror" rows="8" placeholder="Nhập nội dung xem trước (hỗ trợ HTML)...">{!! old('preview_content', $book->preview_content) !!}</textarea>
                <small class="text-muted">Hỗ trợ HTML: &lt;p&gt;, &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h3&gt;...</small>
                @error('preview_content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Giá (VNĐ)</label>
                        <input type="number" name="gia" value="{{ old('gia', $book->gia) }}" class="form-control @error('gia') is-invalid @enderror" min="0" step="1000" placeholder="Để trống nếu miễn phí">
                        @error('gia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Số lượng sách hiện tại</label>
                        <input type="number" value="{{ $book->so_luong ?? 0 }}" class="form-control" readonly disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Số lượng hiện có trong hệ thống
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Số lượng muốn thêm vào kho</label>
                        <input type="number" name="so_luong_them" value="{{ old('so_luong_them', 0) }}" class="form-control @error('so_luong_them') is-invalid @enderror" min="0" placeholder="Nhập số lượng cần thêm">
                        <small class="form-text text-muted">
                            <i class="fas fa-warehouse text-warning"></i> Sẽ tạo phiếu nhập kho cần duyệt
                        </small>
                        @error('so_luong_them')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select name="trang_thai" class="form-control @error('trang_thai') is-invalid @enderror" required>
                            <option value="active" {{ old('trang_thai', $book->trang_thai) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('trang_thai', $book->trang_thai) == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                        @error('trang_thai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Cập nhật sách
                </button>
                <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('hinh_anh_input');
    const imagePreview = document.getElementById('book-cover-image');
    const placeholder = document.getElementById('book-cover-placeholder');
    const form = document.getElementById('bookUpdateForm');
    const submitBtn = document.getElementById('submitBtn');
    
    let hasNewImage = false;
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Kích thước file vượt quá 2MB. Vui lòng chọn file nhỏ hơn.');
                    imageInput.value = '';
                    hasNewImage = false;
                    return;
                }
                
                // Validate file type (fallback theo extension khi browser không trả MIME chính xác)
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                const fileName = (file.name || '').toLowerCase();
                const hasValidExtension = /\.(jpe?g|png|webp)$/i.test(fileName);
                const hasValidMime = !file.type || validTypes.includes(file.type);
                if (!(hasValidMime && hasValidExtension)) {
                    alert('Chỉ chấp nhận file ảnh định dạng JPEG, PNG, JPG hoặc WEBP.');
                    imageInput.value = '';
                    hasNewImage = false;
                    return;
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    // Hide placeholder if exists
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
                
                hasNewImage = true;
                
                // Show feedback to user
                console.log('✅ Ảnh đã được chọn:', file.name, 'Size:', (file.size / 1024).toFixed(2) + ' KB');
            }
        });
    }
    
    // Form submit handler để log debug info
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('📤 Đang gửi form cập nhật sách...');
            console.log('🖼️ Có ảnh mới?', hasNewImage);
            
            if (hasNewImage) {
                const file = imageInput.files[0];
                if (file) {
                    console.log('📸 Thông tin ảnh:', {
                        name: file.name,
                        size: (file.size / 1024).toFixed(2) + ' KB',
                        type: file.type,
                        lastModified: new Date(file.lastModified).toLocaleString()
                    });
                }
                
                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
            }
        });
    }
});
</script>
@endpush
@endsection
