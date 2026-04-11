@extends('account._layout')

@section('title', 'Sách yêu thích')
@section('breadcrumb', 'Sách yêu thích')

@push('styles')
<style>
    .favorite-books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
    }

    .favorite-book-card {
        position: relative;
        display: flex;
        flex-direction: column;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        text-decoration: none;
        transition: transform .2s, box-shadow .2s, border-color .2s;
        cursor: pointer;
    }

    .favorite-book-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        border-color: #d1d5db;
    }

    .favorite-book-card .card-image {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background: #f3f4f6;
    }

    .favorite-book-card .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .favorite-book-card .card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
    }

    .favorite-book-card .heart-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 2;
        transition: transform .2s, background .2s;
        color: #ef4444;
    }

    .favorite-book-card .heart-btn:hover {
        transform: scale(1.15);
        background: #fee2e2;
    }

    .favorite-book-card .card-info {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .favorite-book-card .card-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .favorite-book-card .card-author {
        font-size: 12px;
        color: #6b7280;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .favorite-book-card .card-price {
        font-size: 15px;
        font-weight: 800;
        color: #e11d48;
        margin-top: 4px;
    }

    @media (max-width: 576px) {
        .favorite-books-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="account-section">
    <h2 class="section-title">Sách yêu thích</h2>

    @if($favorites->count() > 0)
        <div class="books-grid favorite-books-grid">
            @foreach($favorites as $favorite)
                @php $book = $favorite->book; @endphp
                @if($book)
                    <a href="{{ route('books.show', $book->id) }}" class="book-card favorite-book-card" id="favorite-card-{{ $book->id }}">
                        <div class="card-image">
                            <button type="button" class="heart-btn" onclick="event.preventDefault(); toggleFavoriteFromList({{ $book->id }})">❤</button>
                            @if($book->image_url)
                                <img src="{{ $book->image_url }}" alt="{{ $book->ten_sach }}">
                            @else
                                <div class="card-placeholder">📖</div>
                            @endif
                        </div>
                        <div class="card-info">
                            <div class="card-title">{{ $book->ten_sach }}</div>
                            <div class="card-author">{{ $book->formatted_author ?? ($book->tac_gia ?? 'Chưa có tác giả') }}</div>
                            <div class="card-price">{{ number_format($book->gia ?? 0, 0, ',', '.') }}đ</div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>

        @if($favorites->hasPages())
        <div class="pagination-wrapper">
            <nav aria-label="Phân trang sách yêu thích">
                <ul class="pagination">
                    <li class="{{ $favorites->onFirstPage() ? 'disabled' : '' }}" aria-disabled="{{ $favorites->onFirstPage() ? 'true' : 'false' }}">
                        @if($favorites->onFirstPage())
                            <span>&lsaquo;</span>
                        @else
                            <a href="{{ $favorites->previousPageUrl() }}" rel="prev">&lsaquo;</a>
                        @endif
                    </li>

                    @for($page = 1; $page <= max(1, $favorites->lastPage()); $page++)
                        <li class="{{ $page === $favorites->currentPage() ? 'active' : '' }}" aria-current="{{ $page === $favorites->currentPage() ? 'page' : 'false' }}">
                            @if($page === $favorites->currentPage())
                                <span>{{ $page }}</span>
                            @else
                                <a href="{{ $favorites->url($page) }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endfor

                    <li class="{{ $favorites->hasMorePages() ? '' : 'disabled' }}" aria-disabled="{{ $favorites->hasMorePages() ? 'false' : 'true' }}">
                        @if($favorites->hasMorePages())
                            <a href="{{ $favorites->nextPageUrl() }}" rel="next">&rsaquo;</a>
                        @else
                            <span>&rsaquo;</span>
                        @endif
                    </li>
                </ul>
            </nav>
        </div>
        @endif
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
    function toggleFavoriteFromList(bookId) {
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
