@extends('account._layout')

@section('title', 'Sách yêu thích')
@section('breadcrumb', 'Sách yêu thích')

@push('styles')
<style>
    .favorite-card-actions {
        display: flex;
        gap: 10px;
        margin-top: 14px;
    }

    .btn-remove-favorite {
        border: 1px solid #ef4444;
        background: #fff5f5;
        color: #dc2626;
        border-radius: 10px;
        padding: 10px 14px;
        cursor: pointer;
        font-weight: 600;
        transition: all .2s ease;
    }

    .btn-remove-favorite:hover {
        background: #fee2e2;
    }

    .book-meta-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        color: #64748b;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="account-section">
    <h2 class="section-title">Sách yêu thích</h2>

    @if($favorites->count() > 0)
        <div class="books-grid">
            @foreach($favorites as $favorite)
                @php $book = $favorite->book; @endphp
                @if($book)
                    <div class="book-card" id="favorite-card-{{ $book->id }}">
                        <div class="book-image">
                            @if($book->image_url)
                                <img src="{{ $book->image_url }}" alt="{{ $book->ten_sach }}">
                            @else
                                <div class="book-placeholder">📖</div>
                            @endif
                        </div>
                        <div class="book-info">
                            <h3 class="book-title">{{ $book->ten_sach }}</h3>
                            <p class="book-author">{{ $book->formatted_author ?? ($book->tac_gia ?? 'Chưa có tác giả') }}</p>

                            <div class="book-meta-inline">
                                <span>❤️ {{ $book->favorites_count ?? 0 }} lượt thích</span>
                                <span>👁️ {{ number_format($book->so_luot_xem ?? 0) }} lượt xem</span>
                            </div>

                            <div class="favorite-card-actions">
                                <a href="{{ route('books.show', $book->id) }}" class="btn-view-book">Xem chi tiết</a>
                                <button type="button"
                                        class="btn-remove-favorite"
                                        onclick="toggleFavoriteFromList({{ $book->id }}, this)">
                                    Bỏ tim
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $favorites->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">💖</div>
            <h3>Bạn chưa có sách yêu thích</h3>
            <p>Hãy bấm biểu tượng trái tim ở trang chi tiết sách để lưu lại sách bạn quan tâm.</p>
            <a href="{{ route('books.public') }}" class="btn-primary">Khám phá sách</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function toggleFavoriteFromList(bookId, button) {
        fetch('{{ route('account.favorites.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ book_id: bookId })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Không thể cập nhật sách yêu thích.');
                return;
            }

            const card = document.getElementById(`favorite-card-${bookId}`);
            if (card && data.is_favorited === false) {
                card.remove();
            }

            if (document.querySelectorAll('.book-card[id^="favorite-card-"]').length === 0) {
                window.location.reload();
            }
        })
        .catch(() => {
            alert('Có lỗi xảy ra khi cập nhật sách yêu thích.');
        });
    }
</script>
@endpush
