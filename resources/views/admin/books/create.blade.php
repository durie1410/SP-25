@extends('layouts.admin')

@section('title', 'Thêm Sách Mới - Admin')

@section('content')
<div class="admin-table">
    <h3><i class="fas fa-plus"></i> Thêm sách mới</h3>
    
    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tên sách</label>
                    <input type="text" name="ten_sach" class="form-control" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Thể loại</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Chọn thể loại --</option>
                        @foreach($categories as $cate)
                            <option value="{{ $cate->id }}">{{ $cate->ten_the_loai }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Tác giả</label>
                    <input type="text" name="tac_gia" class="form-control" required>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nhà xuất bản</label>
                    <select name="nha_xuat_ban_id" class="form-control">
                        <option value="">-- Chọn nhà xuất bản --</option>
                        @if(isset($publishers))
                            @foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}">{{ $publisher->ten_nha_xuat_ban }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Năm xuất bản</label>
                    <input type="number" name="nam_xuat_ban" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh bìa</label>
            <div id="image-preview-container" class="mb-2" style="display: none;">
                <img id="book-cover-preview" 
                     src="" 
                     width="120" height="160" 
                     style="object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                     alt="Preview">
            </div>
            <input type="file" name="hinh_anh" id="hinh_anh_input" class="form-control @error('hinh_anh') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg">
            <small class="form-text text-muted">Chọn ảnh bìa sách (tối đa 2MB, định dạng: JPG, PNG)</small>
            @error('hinh_anh')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="mo_ta" class="form-control" rows="4"></textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Giá (VNĐ)</label>
                    <input type="number" name="gia" class="form-control" min="0" step="1000" placeholder="Để trống nếu miễn phí">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-control" required>
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Tạm dừng</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Lưu
            </button>
            <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('hinh_anh_input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewImage = document.getElementById('book-cover-preview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Vui lòng chọn file ảnh định dạng JPG hoặc PNG');
                    imageInput.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2048 * 1024) {
                    alert('Kích thước ảnh không được vượt quá 2MB');
                    imageInput.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
@endsection


