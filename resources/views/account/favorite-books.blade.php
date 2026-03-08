@extends('account._layout')

@section('title', 'Sách yêu thích')
@section('breadcrumb', 'Sách yêu thích')

@push('styles')
<style>
    .favorite-books-grid {
        align-items: stretch;
        gap: 24px;
    }

    .favorite-book-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 18px;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.85);
        background:
            radial-gradient(circle at top right, rgba(244, 63, 94, 0.12), transparent 36%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        box-shadow:
            0 18px 40px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        overflow: hidden;
        transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
    }

    .favorite-book-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.24), transparent 42%, rgba(244, 63, 94, 0.05));
        pointer-events: none;
    }

    .favorite-book-card::after {
        content: '';
        position: absolute;
        top: -35%;
        left: -120%;
        width: 68%;
        height: 170%;
        transform: rotate(18deg);
        background: linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.55), transparent);
        opacity: 0;
        pointer-events: none;
        transition: left .8s ease, opacity .4s ease;
    }

    .favorite-book-card:hover {
        transform: translateY(-8px);
        border-color: rgba(251, 191, 36, 0.35);
        box-shadow:
            0 24px 50px rgba(15, 23, 42, 0.12),
            0 10px 28px rgba(244, 63, 94, 0.12);
    }

    .favorite-book-card:hover::after {
        left: 145%;
        opacity: 1;
    }

    .favorite-book-card .book-image {
        position: relative;
        height: 270px;
        border-radius: 18px;
        margin-bottom: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #f8fafc, #e5e7eb);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
    }

    .favorite-book-card .book-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.02), rgba(15, 23, 42, 0.36));
        pointer-events: none;
    }

    .favorite-book-card .book-image::before {
        content: '';
        position: absolute;
        inset: auto -10% -35% auto;
        width: 150px;
        height: 150px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.28), transparent 70%);
        filter: blur(8px);
        z-index: 1;
        pointer-events: none;
    }

    .favorite-book-card .book-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scale(1.01);
        transition: transform .6s ease;
    }

    .favorite-book-card:hover .book-image img {
        transform: scale(1.06);
    }

    .favorite-book-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        color: #fff;
        background: linear-gradient(135deg, #e11d48, #7c2d12);
    }

    .favorite-book-card .book-info {
        position: relative;
        display: flex;
        flex-direction: column;
        flex: 1;
        z-index: 1;
    }

    .favorite-book-card .book-title {
        min-height: 52px;
        margin-bottom: 10px;
        font-size: 18px;
        line-height: 1.45;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .favorite-book-card .book-author {
        min-height: 30px;
        margin-bottom: 16px;
        font-size: 15px;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .favorite-card-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: #be123c;
        border: 1px solid rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.14);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .02em;
    }

    .favorite-card-badge::after {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f59e0b, #f43f5e);
        box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.08);
    }

    .favorite-card-badge span {
        font-size: 14px;
        animation: favoritePulse 2.4s ease-in-out infinite;
    }

    .favorite-card-note {
        margin: 0 0 14px;
        font-size: 13px;
        font-weight: 600;
        color: #b45309;
        letter-spacing: .01em;
    }

    .favorite-book-meta-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }

    .favorite-book-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 14px;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.95);
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.95);
    }

    .favorite-book-meta-pill strong {
        color: #0f172a;
    }

    .favorite-card-actions {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: auto;
        padding-top: 18px;
        align-items: stretch;
    }

    .favorite-card-actions > * {
        flex: 1 1 0;
    }

    .favorite-card-actions .btn-view-book,
    .btn-remove-favorite {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-align: center;
        padding: 13px 14px;
        border-radius: 16px;
        font-size: 15px;
        font-weight: 700;
        margin-top: 0;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background .22s ease, color .22s ease;
    }

    .favorite-card-actions .btn-view-book {
        color: #fff;
        background: linear-gradient(135deg, #ef4444, #be123c);
        box-shadow: 0 14px 26px rgba(190, 24, 93, 0.22);
    }

    .favorite-card-actions .btn-view-book::before,
    .btn-remove-favorite::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.32), transparent);
        transform: translateX(-120%);
        transition: transform .55s ease;
    }

    .favorite-card-actions .btn-view-book:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 30px rgba(190, 24, 93, 0.28);
    }

    .favorite-card-actions .btn-view-book:hover::before,
    .btn-remove-favorite:hover::before {
        transform: translateX(120%);
    }

    .btn-remove-favorite {
        border: 1px solid rgba(239, 68, 68, 0.18);
        background: rgba(255, 255, 255, 0.78);
        color: #be123c;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(10px);
        cursor: pointer;
        transition: all .2s ease;
    }

    .btn-remove-favorite:hover {
        background: linear-gradient(135deg, #fff1f2, #ffe4e6);
        border-color: rgba(244, 63, 94, 0.32);
        color: #9f1239;
        transform: translateY(-1px);
    }

    .book-meta-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 4px;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 14px;
    }

    .book-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 12px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 999px;
        white-space: nowrap;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    .favorite-library-empty {
        position: relative;
        overflow: hidden;
        padding: 44px 28px;
        border-radius: 28px;
        border: 1px solid rgba(244, 63, 94, 0.1);
        background:
            radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 28%),
            linear-gradient(180deg, #fff, #fff7ed 100%);
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
    }

    .favorite-library-empty .empty-icon {
        width: 96px;
        height: 96px;
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 24px;
        background: linear-gradient(135deg, #fb7185, #be123c);
        color: #fff;
        box-shadow: 0 18px 28px rgba(190, 24, 93, 0.26);
    }

    .favorite-library-empty h3 {
        font-size: 28px;
        font-weight: 900;
        color: #111827;
        margin-bottom: 10px;
    }

    .favorite-library-empty p {
        max-width: 560px;
        margin: 0 auto 22px;
        color: #6b7280;
        line-height: 1.8;
    }

    .favorite-library-empty .btn-primary {
        min-height: 50px;
        padding: 14px 22px;
        border-radius: 16px;
        background: linear-gradient(135deg, #ef4444, #be123c);
        box-shadow: 0 16px 26px rgba(190, 24, 93, 0.22);
    }

    .favorite-action-icon {
        font-size: 15px;
        line-height: 1;
        position: relative;
        z-index: 1;
        transition: transform .25s ease;
    }

    .favorite-card-actions .btn-view-book span:last-child,
    .btn-remove-favorite span:last-child {
        position: relative;
        z-index: 1;
    }

    .favorite-card-actions .btn-view-book:hover .favorite-action-icon {
        transform: rotate(-8deg) scale(1.08);
    }

    .btn-remove-favorite:hover .favorite-action-icon {
        transform: scale(1.08);
    }

    @keyframes favoritePulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.14);
            opacity: .88;
        }
    }

    @media (max-width: 576px) {
        .favorite-book-card .book-image {
            height: 220px;
        }

        .favorite-card-actions {
            flex-direction: column;
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
                    <div class="book-card favorite-book-card" id="favorite-card-{{ $book->id }}">
                        <div class="book-image">
                            <div class="favorite-card-badge"><span>❤</span> Premium yêu thích</div>
                            @if($book->image_url)
                                <img src="{{ $book->image_url }}" alt="{{ $book->ten_sach }}">
                            @else
                                <div class="favorite-book-placeholder">📖</div>
                            @endif
                        </div>
                        <div class="book-info">
                            <h3 class="book-title">{{ $book->ten_sach }}</h3>
                            <p class="book-author">{{ $book->formatted_author ?? ($book->tac_gia ?? 'Chưa có tác giả') }}</p>
                            <p class="favorite-card-note">Đã lưu trong bộ sưu tập yêu thích của bạn</p>

                            <div class="favorite-book-meta-stack">
                                <span class="favorite-book-meta-pill">
                                    📚
                                    <strong>{{ $book->category->ten_danh_muc ?? 'Chưa phân loại' }}</strong>
                                </span>
                                <span class="favorite-book-meta-pill">
                                    🏷️
                                    <strong>{{ $book->publisher->ten_nha_xuat_ban ?? 'NXB cập nhật sau' }}</strong>
                                </span>
                            </div>

                            <div class="book-meta-inline">
                                <span class="book-meta-chip">❤️ {{ $book->favorites_count ?? 0 }} lượt thích</span>
                                <span class="book-meta-chip">👁️ {{ number_format($book->so_luot_xem ?? 0) }} lượt xem</span>
                            </div>

                            <div class="favorite-card-actions">
                                <a href="{{ route('books.show', $book->id) }}" class="btn-view-book">
                                    <span class="favorite-action-icon">✨</span>
                                    <span>Xem chi tiết</span>
                                </a>
                                <button type="button"
                                        class="btn-remove-favorite"
                                        onclick="toggleFavoriteFromList({{ $book->id }}, this)">
                                    <span class="favorite-action-icon">💔</span>
                                    <span>Bỏ yêu thích</span>
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
        <div class="empty-state favorite-library-empty">
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
