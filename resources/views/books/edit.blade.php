@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sửa sách</h1>
    <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Tên sách</label>
            <input type="text" name="ten_sach" value="{{ $book->ten_sach }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Thể loại</label>
            <select name="category_id" class="form-control" required>
                @foreach($categories as $cate)
                    <option value="{{ $cate->id }}" {{ $book->category_id == $cate->id ? 'selected' : '' }}>
                        {{ $cate->ten_the_loai }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Tác giả</label>
            <input type="text" name="tac_gia" value="{{ $book->tac_gia }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Năm xuất bản</label>
            <input type="number" name="nam_xuat_ban" value="{{ $book->nam_xuat_ban }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Ảnh bìa</label>
            @if($book->hinh_anh)
                <div><img src="{{ $book->image_url }}" width="80"></div>
            @endif
            <input type="file" name="hinh_anh" class="form-control">
        </div>

        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="mo_ta" class="form-control">{{ $book->mo_ta }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
    </form>
</div>
@endsection



