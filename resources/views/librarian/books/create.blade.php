@extends('layouts.admin')

@section('title', 'Thêm sách - Librarian')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-plus-circle"></i>
        Thêm sách mới
    </h1>
    <p class="page-subtitle">Librarian có thể tạo sách mới và cập nhật thông tin sách (không được xóa).</p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-book"></i> Thông tin sách</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('librarian.books.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Tiêu đề (title)</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" required>
                @error('title') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tác giả (author_id)</label>
                <select name="author_id" class="form-select @error('author_id') is-invalid @enderror" required>
                    <option value="">-- Chọn tác giả --</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ (string)old('author_id') === (string)$author->id ? 'selected' : '' }}>
                            {{ $author->ten_tac_gia }}
                        </option>
                    @endforeach
                </select>
                @error('author_id') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Thể loại (category_id)</label>
                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">-- Chọn thể loại --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)old('category_id') === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->ten_the_loai ?? $cat->name ?? ('#' . $cat->id) }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Năm xuất bản (publish_year)</label>
                <input type="number" name="publish_year" class="form-control @error('publish_year') is-invalid @enderror"
                       value="{{ old('publish_year') }}" min="1000" max="{{ now()->year }}" required>
                @error('publish_year') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả (description)</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="5">{{ old('description') }}</textarea>
                @error('description') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

