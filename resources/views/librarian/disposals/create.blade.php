@extends('layouts.admin')

@section('title', 'Tạo yêu cầu thanh lý sách')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-recycle"></i>
        Tạo yêu cầu thanh lý / đề xuất xóa sách
    </h1>
    <p class="page-subtitle">
        Librarian chỉ được <strong>tạo yêu cầu</strong>, không xóa trực tiếp. Admin sẽ phê duyệt và xử lý.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle"></i> Thông tin yêu cầu</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('librarian.disposals.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Loại yêu cầu</label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" id="disposal-type-select" required>
                    <option value="book" {{ old('type') === 'copy' ? '' : 'selected' }}>Thanh lý theo sách (cả đầu sách)</option>
                    <option value="copy" {{ old('type') === 'copy' ? 'selected' : '' }}>Thanh lý bản sao cụ thể (bản in)</option>
                </select>
                @error('type') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group disposal-target disposal-target-book">
                <label class="form-label">Chọn sách (book)</label>
                <select name="book_id" class="form-select @error('book_id') is-invalid @enderror">
                    <option value="">-- Chọn sách cần thanh lý --</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}" {{ (string)old('book_id') === (string)$book->id ? 'selected' : '' }}>
                            [#{{ $book->id }}] {{ $book->ten_sach }}
                        </option>
                    @endforeach
                </select>
                @error('book_id') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group disposal-target disposal-target-copy" style="display:none;">
                <label class="form-label">Chọn bản sao (copy / inventory)</label>
                <select name="inventory_id" class="form-select @error('inventory_id') is-invalid @enderror">
                    <option value="">-- Chọn bản sao cần thanh lý --</option>
                    @foreach($inventories as $item)
                        <option value="{{ $item->id }}" {{ (string)old('inventory_id') === (string)$item->id ? 'selected' : '' }}>
                            [#{{ $item->id }}] {{ $item->book->ten_sach ?? 'Không rõ sách' }}
                            - Barcode: {{ $item->barcode ?? 'N/A' }}
                            - Tình trạng: {{ $item->condition ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('inventory_id') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Lý do đề xuất thanh lý</label>
                <textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
                          placeholder="Ví dụ: Sách hư hỏng nặng, mất trang; nội dung đã lạc hậu; bản sao bị hỏng...">{{ old('reason') }}</textarea>
                @error('reason') <div class="text-danger" style="margin-top:6px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <a href="{{ route('librarian.disposals.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('disposal-type-select');
        const targetBook = document.querySelector('.disposal-target-book');
        const targetCopy = document.querySelector('.disposal-target-copy');

        function toggleTargets() {
            if (typeSelect.value === 'copy') {
                targetBook.style.display = 'none';
                targetCopy.style.display = '';
            } else {
                targetBook.style.display = '';
                targetCopy.style.display = 'none';
            }
        }

        typeSelect.addEventListener('change', toggleTargets);
        toggleTargets();
    });
</script>
@endsection

