<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ request('category_id') ? ($categories->where('id', request('category_id'))->first()->ten_the_loai ?? 'Sách') : 'Tất cả sách' }} - LibNet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <style>
        /* Page-specific styles - Synced with system design */
        .books-page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px;
        }

        .books-layout {
            display: flex;
            gap: 28px;
            align-items: flex-start;
        }

        /* Sidebar */
        .books-sidebar {
            width: 280px;
            flex-shrink: 0;
        }

        .sidebar-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.5);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .sidebar-card-header {
            background: linear-gradient(135deg, var(--primary-50), #ffffff);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 0.95em;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-card-header i {
            color: var(--primary-color);
        }

        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-list li {
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
        }

        .category-list li:last-child {
            border-bottom: none;
        }

        .category-list a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9em;
            transition: all var(--transition-normal);
        }

        .category-list a i {
            width: 18px;
            color: var(--text-light);
            transition: color var(--transition-fast);
        }

        .category-list a:hover {
            background: var(--primary-50);
            color: var(--primary-color);
            padding-left: 28px;
        }

        .category-list a:hover i {
            color: var(--primary-color);
        }

        .category-list a.active-category {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            font-weight: 500;
        }

        .category-list a.active-category i {
            color: white;
        }

        .category-list a.active-category:hover {
            padding-left: 28px;
        }

        /* Main content */
        .books-content {
            flex: 1;
            min-width: 0;
        }

        /* Breadcrumbs */
        .breadcrumbs-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: var(--text-muted);
        }

        .breadcrumbs-nav a {
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }

        .breadcrumbs-nav a:hover {
            background: var(--primary-50);
            color: var(--primary-color);
        }

        .breadcrumbs-nav .separator {
            color: var(--text-light);
        }

        .breadcrumbs-nav .current {
            color: var(--text-color);
            font-weight: 500;
        }

        /* Page title */
        .page-title {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--text-color);
            margin: 0 0 24px 0;
            padding-bottom: 16px;
            border-bottom: 3px solid var(--primary-color);
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }

        /* Filter bar */
        .filter-bar {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226, 232, 240, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-size: 0.9em;
            font-weight: 500;
            color: var(--text-color);
        }

        .sort-select {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-select select {
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9em;
            font-weight: 500;
            color: var(--text-color);
            background: white;
            cursor: pointer;
            transition: all var(--transition-fast);
            min-width: 150px;
        }

        .sort-select select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        /* Book grid - Match homepage style */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .book-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 14px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .book-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform var(--transition-normal);
        }

        .book-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .book-card:hover::after {
            transform: scaleX(1);
        }

        .book-card a {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .book-card-cover {
            width: 100%;
            height: 220px;
            background: linear-gradient(145deg, #f1f5f9, #e2e8f0);
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
        }

        .book-card:hover .book-card-cover {
            box-shadow: var(--shadow-md);
        }

        .book-card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }

        .book-card:hover .book-card-cover img {
            transform: scale(1.05);
        }

        .book-card-cover .placeholder {
            color: var(--text-light);
            font-size: 0.85em;
            text-align: center;
            padding: 20px;
        }

        .book-card-cover .placeholder i {
            font-size: 2.5em;
            margin-bottom: 10px;
            display: block;
            opacity: 0.5;
        }

        .book-card-title {
            font-weight: 600;
            font-size: 0.95em;
            color: var(--text-color);
            line-height: 1.4;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 44px;
            transition: color var(--transition-fast);
        }

        .book-card:hover .book-card-title {
            color: var(--primary-color);
        }

        .book-card-author {
            font-size: 0.8em;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .empty-state i {
            font-size: 4em;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 1.1em;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 32px;
            gap: 6px;
        }

        .pagination-wrapper a,
        .pagination-wrapper span {
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-color);
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition-fast);
            font-size: 0.9em;
        }

        .pagination-wrapper a:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: var(--primary-50);
        }

        .pagination-wrapper .current-page {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .pagination-wrapper .disabled {
            color: var(--text-light);
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .books-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .books-layout {
                flex-direction: column;
            }

            .books-sidebar {
                width: 100%;
            }

            .sidebar-card {
                margin-bottom: 16px;
            }

            .category-list {
                display: flex;
                flex-wrap: wrap;
                padding: 12px;
                gap: 8px;
            }

            .category-list li {
                border-bottom: none;
            }

            .category-list a {
                padding: 10px 16px;
                border-radius: var(--radius-full);
                border: 2px solid var(--border-color);
                background: white;
            }

            .category-list a.active-category {
                border-color: transparent;
            }

            .books-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .books-page-container {
                padding: 16px;
            }

            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .books-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .book-card-cover {
                height: 180px;
            }

            .book-card-title {
                font-size: 0.9em;
                min-height: 38px;
            }
        }

        @media (max-width: 480px) {
            .books-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .book-card-cover {
                height: 220px;
            }
        }
    </style>
</head>
<body>
    @include('components.frontend-header')

    <div class="books-page-container">
        <div class="books-layout">
            <!-- Sidebar -->
            <aside class="books-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <i class="fas fa-tags"></i>
                        CHỦ ĐỀ TIÊU BIỂU
                    </div>
                    <ul class="category-list">
                        @php
                            $activeCategoryId = request('category_id');
                        @endphp
                        <li>
                            <a href="{{ route('books.public') }}" class="{{ !$activeCategoryId ? 'active-category' : '' }}">
                                <i class="fas fa-th-large"></i>
                                <span>Tất cả</span>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('books.public', ['category_id' => $category->id]) }}" 
                                   class="{{ $activeCategoryId == $category->id ? 'active-category' : '' }}">
                                    <i class="fas fa-book"></i>
                                    <span>{{ $category->ten_the_loai }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="books-content">
                <!-- Breadcrumbs -->
                <nav class="breadcrumbs-nav">
                    <a href="{{ route('home') }}">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <span class="separator">/</span>
                    <span class="current">
                        @if($activeCategoryId && $categories->where('id', $activeCategoryId)->first())
                            {{ $categories->where('id', $activeCategoryId)->first()->ten_the_loai }}
                        @else
                            Tất cả sách
                        @endif
                    </span>
                </nav>

                <!-- Page Title -->
                <h1 class="page-title">
                    @if($activeCategoryId && $categories->where('id', $activeCategoryId)->first())
                        {{ $categories->where('id', $activeCategoryId)->first()->ten_the_loai }}
                    @else
                        Tất cả sách
                    @endif
                </h1>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="sort-select">
                        <span class="filter-label">Sắp xếp:</span>
                        <select id="sort-by" onchange="
                            var url = new URL(window.location.href);
                            url.searchParams.set('sort', this.value);
                            @if(request('keyword'))
                                url.searchParams.set('keyword', '{{ request('keyword') }}');
                            @endif
                            @if(request('category_id'))
                                url.searchParams.set('category_id', '{{ request('category_id') }}');
                            @endif
                            window.location.href = url.toString();
                        ">
                            <option value="new" {{ request('sort') == 'new' || !request('sort') ? 'selected' : '' }}>Mới nhất</option>
                            <option value="name-asc" {{ request('sort') == 'name-asc' ? 'selected' : '' }}>Tên A-Z</option>
                            <option value="name-desc" {{ request('sort') == 'name-desc' ? 'selected' : '' }}>Tên Z-A</option>
                        </select>
                    </div>
                </div>

                <!-- Book Grid -->
                @if($books->count() > 0)
                    <div class="books-grid">
                        @foreach($books as $book)
                            <article class="book-card">
                                <a href="{{ route('books.show', $book->id) }}">
                                    <div class="book-card-cover">
                                        @if($book->hinh_anh && $book->image_url)
                                            <img src="{{ $book->image_url }}" alt="{{ $book->ten_sach }}" loading="lazy">
                                        @else
                                            <div class="placeholder">
                                                <i class="fas fa-book"></i>
                                                <span>Chưa có ảnh</span>
                                            </div>
                                        @endif
                                    </div>
                                    <h3 class="book-card-title">{{ $book->ten_sach }}</h3>
                                    @if($book->tac_gia)
                                        <p class="book-card-author">{{ $book->tac_gia }}</p>
                                    @endif
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Không tìm thấy sách nào phù hợp với bộ lọc của bạn.</p>
                    </div>
                @endif

                <!-- Pagination -->
                @if($books->hasPages())
                    <div class="pagination-wrapper">
                        @if($books->onFirstPage())
                            <span class="disabled">&laquo;</span>
                            <span class="disabled">&lsaquo;</span>
                        @else
                            <a href="{{ $books->url(1) }}">&laquo;</a>
                            <a href="{{ $books->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @foreach($books->getUrlRange(max(1, $books->currentPage() - 2), min($books->lastPage(), $books->currentPage() + 2)) as $page => $url)
                            @if($page == $books->currentPage())
                                <span class="current-page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($books->hasMorePages())
                            <a href="{{ $books->nextPageUrl() }}">&rsaquo;</a>
                            <a href="{{ $books->url($books->lastPage()) }}">&raquo;</a>
                        @else
                            <span class="disabled">&rsaquo;</span>
                            <span class="disabled">&raquo;</span>
                        @endif
                    </div>
                @endif
            </main>
        </div>
    </div>

    @include('components.footer')

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadBorrowCartCount();
        });

        function loadBorrowCartCount() {
            fetch('{{ route('reservation-cart.count') }}')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('borrow-cart-count');
                    if (cartCountElement) {
                        const count = data.count || 0;
                        cartCountElement.textContent = count;
                        cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }

        // Book Comparison Feature
        function updateCompareCount() {
            const checkboxes = document.querySelectorAll('.book-card-checkbox:checked');
            const count = checkboxes.length;
            const compareBtn = document.getElementById('compare-btn');
            const compareCount = document.getElementById('compare-count');
            
            if (count > 0) {
                compareBtn.style.display = 'flex';
                compareCount.textContent = count;
            } else {
                compareBtn.style.display = 'none';
            }
        }

        function openComparisonModal() {
            const checkboxes = document.querySelectorAll('.book-card-checkbox:checked');
            const books = [];
            
            checkboxes.forEach(checkbox => {
                books.push({
                    id: checkbox.getAttribute('data-book-id'),
                    name: checkbox.getAttribute('data-book-name'),
                    author: checkbox.getAttribute('data-book-author'),
                    image: checkbox.getAttribute('data-book-image'),
                    price: parseInt(checkbox.getAttribute('data-book-price'))
                });
            });

            if (books.length === 0) {
                alert('Vui lòng chọn ít nhất một cuốn sách để so sánh');
                return;
            }

            displayComparisonModal(books);
        }

        function displayComparisonModal(books) {
            // Remove existing modal if present
            const existingModal = document.getElementById('comparison-modal');
            if (existingModal) existingModal.remove();

            // Create modal
            const modal = document.createElement('div');
            modal.id = 'comparison-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                padding: 20px;
            `;

            const content = document.createElement('div');
            content.style.cssText = `
                background: white;
                border-radius: 12px;
                max-width: 1200px;
                width: 100%;
                max-height: 90vh;
                overflow-y: auto;
                padding: 24px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            `;

            let html = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2 style="margin: 0; font-size: 1.5em; color: #0d9488;">So sánh sách (${books.length} cuốn)</h2>
                    <button onclick="document.getElementById('comparison-modal').remove()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #999;">✕</button>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            `;

            books.forEach(book => {
                const bookUrl = `/books/${book.id}`;
                html += `
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; transition: box-shadow 0.3s;">
                        <img src="${book.image}" alt="${book.name}" style="width: 100%; height: 280px; object-fit: cover; background: #f0f0f0;">
                        <div style="padding: 16px;">
                            <h3 style="margin: 0 0 8px 0; font-size: 0.95em; line-height: 1.4; color: #1f2937; height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${book.name}</h3>
                            <p style="margin: 0 0 10px 0; font-size: 0.85em; color: #6b7280;">${book.author || 'Không xác định'}</p>
                            <p style="margin: 0 0 16px 0; font-weight: 700; color: #0d9488;">${book.price > 0 ? (book.price.toLocaleString('vi-VN') + '₫') : 'Liên hệ'}</p>
                            <a href="${bookUrl}" style="display: inline-block; background: #0d9488; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85em; font-weight: 500; transition: background 0.3s; width: 100%; text-align: center;" onmouseover="this.style.background='#099268'" onmouseout="this.style.background='#0d9488'">Xem chi tiết</a>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            content.innerHTML = html;
            modal.appendChild(content);
            document.body.appendChild(modal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
    </script>
    @endauth
</body>
</html>
