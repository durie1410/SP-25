@extends('layouts.admin')

@section('title', 'Chỉnh sửa sách có thể mua')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.purchasable-books.index') }}">Quản lý sách có thể mua</a></li>
            <li class="breadcrumb-item active">Chỉnh sửa sách</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit"></i> Chỉnh sửa sách: {{ $book->ten_sach }}</h2>
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

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.purchasable-books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Thông tin cơ bản -->
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_sach" class="form-control" value="{{ old('ten_sach', $book->ten_sach) }}" required>
                                    @error('ten_sach')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                                    <input type="text" name="tac_gia" class="form-control" value="{{ old('tac_gia', $book->tac_gia) }}" required>
                                    @error('tac_gia')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="mo_ta" class="form-control" rows="4">{{ old('mo_ta', $book->mo_ta) }}</textarea>
                                    @error('mo_ta')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                            <input type="number" name="gia" class="form-control" value="{{ old('gia', $book->gia) }}" min="0" step="1000" required>
                                            @error('gia')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Định dạng <span class="text-danger">*</span></label>
                                            <select name="dinh_dang" class="form-control" required>
                                                <option value="">Chọn định dạng</option>
                                                <option value="PDF" {{ old('dinh_dang', $book->dinh_dang) == 'PDF' ? 'selected' : '' }}>PDF</option>
                                                <option value="EPUB" {{ old('dinh_dang', $book->dinh_dang) == 'EPUB' ? 'selected' : '' }}>EPUB</option>
                                                <option value="MOBI" {{ old('dinh_dang', $book->dinh_dang) == 'MOBI' ? 'selected' : '' }}>MOBI</option>
                                                <option value="AZW" {{ old('dinh_dang', $book->dinh_dang) == 'AZW' ? 'selected' : '' }}>AZW</option>
                                            </select>
                                            @error('dinh_dang')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nhà xuất bản <span class="text-danger">*</span></label>
                                            <input type="text" name="nha_xuat_ban" class="form-control" value="{{ old('nha_xuat_ban', $book->nha_xuat_ban) }}" required>
                                            @error('nha_xuat_ban')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Năm xuất bản <span class="text-danger">*</span></label>
                                            <input type="number" name="nam_xuat_ban" class="form-control" value="{{ old('nam_xuat_ban', $book->nam_xuat_ban) }}" min="1900" max="{{ date('Y') }}" required>
                                            @error('nam_xuat_ban')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">ISBN</label>
                                            <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}">
                                            @error('isbn')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Số trang</label>
                                            <input type="number" name="so_trang" class="form-control" value="{{ old('so_trang', $book->so_trang) }}" min="1">
                                            @error('so_trang')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Ngôn ngữ</label>
                                            <input type="text" name="ngon_ngu" class="form-control" value="{{ old('ngon_ngu', $book->ngon_ngu) }}">
                                            @error('ngon_ngu')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kích thước file (KB)</label>
                                            <input type="number" name="kich_thuoc_file" class="form-control" value="{{ old('kich_thuoc_file', $book->kich_thuoc_file) }}" min="0">
                                            @error('kich_thuoc_file')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="trang_thai" class="form-control" required>
                                        <option value="active" {{ old('trang_thai', $book->trang_thai) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('trang_thai', $book->trang_thai) == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                    </select>
                                    @error('trang_thai')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Ảnh bìa -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Ảnh bìa</label>
                                    @if($book->hinh_anh)
                                        <div class="mb-2">
                                            <img src="{{ $book->image_url }}" 
                                                 width="200" height="280" 
                                                 style="object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                                                 alt="{{ $book->ten_sach }}">
                                        </div>
                                    @endif
                                    <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                                    <small class="form-text text-muted">Chọn ảnh mới để thay thế ảnh hiện tại (JPG, PNG, tối đa 2MB)</small>
                                    @error('hinh_anh')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.purchasable-books.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

