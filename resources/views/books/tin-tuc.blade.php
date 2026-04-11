<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu {{ $document->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Thiết lập cơ bản */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5; /* Màu nền xám nhạt */
            color: #333;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }


        /* --- MAIN CONTAINER (Bố cục 2 cột) --- */
        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
        }

        .main-content-area {
            flex: 3; /* Chiếm khoảng 65-70% chiều rộng */
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar {
            flex: 1; /* Chiếm khoảng 30-35% chiều rộng */
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            min-width: 250px;
        }

        /* --- CONTENT STYLES --- */
        .breadcrumb {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .book-intro-section h1 {
            font-size: 1.5em;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .intro-banner img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .intro-content {
            padding: 20px;
            line-height: 1.8;
            color: #333;
        }

        .intro-content p {
            margin: 10px 0;
        }

        .intro-content ul {
            margin: 10px 0;
            padding-left: 30px;
        }

        .intro-content li {
            margin: 5px 0;
        }

        /* Bảng thông tin tin tức */
        .book-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .book-details th, .book-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .book-details th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .book-details td:nth-child(2) img { /* Hình ảnh tin tức */
            width: 80px;
            height: auto;
            display: block;
        }

        /* Thông tin Nhà xuất bản */
        .publisher-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
        }

        .contact-details p {
            line-height: 1.5;
            margin: 5px 0;
        }

        /* --- SIDEBAR STYLES --- */
        .sidebar h2 {
            font-size: 1.2em;
            padding: 10px 0;
            margin-bottom: 10px;
            border-bottom: 2px solid #dc3545; /* Đường viền đỏ dưới tiêu đề */
            color: #333;
        }

        .topic-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .topic-list li {
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
            cursor: pointer;
        }

        .topic-list li:hover {
            background-color: #f9f9f9;
        }

        .topic-list li i {
            color: #dc3545; /* Icon màu đỏ */
            margin-right: 10px;
        }

        .hot-news {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .hot-news h2 {
            border-bottom-color: #ffc107; /* Đường viền vàng dưới TIN NỔI BẬT */
        }

        .news-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dotted #ccc;
        }

        .news-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .view-all-btn {
            display: block;
            text-align: right;
            color: #dc3545;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
    <script>
        // Load cart count on page load
        function loadCartCount() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        if (data.count > 0) {
                            cartCountElement.textContent = data.count;
                            cartCountElement.style.display = 'flex';
                        } else {
                            cartCountElement.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount();
        });
    </script>
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="logo-section">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #e51d2e 0%, #c41e2f 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-right: 8px;">
                    📚
                </div>
                <div class="logo-text">
                    <span class="logo-part1">THUÊ SÁCH</span>
                    <span class="logo-part2">LibNet</span>
                </div>
            </div>
            <div class="hotline-section">
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách lẻ:</span>
                    <a href="tel:0327888669" class="hotline-number">0327888669</a>
                </div>
                <div class="hotline-item">
                    <span class="hotline-label">Hotline khách sỉ:</span>
                    <a href="tel:02439741791" class="hotline-number">02439741791 - 0327888669</a>
                </div>
            </div>
            <div class="user-actions">
                @auth
                    <a href="{{ route('account') }}" class="auth-link">Tài khoản của tôi</a>
                @else
                    <a href="{{ route('login') }}" class="auth-link">Đăng nhập</a>
                @endauth
            </div>
        </div>
        <div class="header-nav">
            <div class="search-bar">
                <form action="{{ route('books.public') }}" method="GET" class="search-form">
                    <input type="text" name="keyword" placeholder="Tìm sách, tác giả, sản phẩm mong muốn..." value="{{ request('keyword') }}" class="search-input">
                    <button type="submit" class="search-button">🔍 Tìm kiếm</button>
                </form>
            </div>
        </div>
    </header>
    <div class="container">
        <main class="main-content-area">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a> / {{ Str::limit($document->title, 50) }}
            </div>

            <section class="book-intro-section">
                <h1>Giới thiệu {{ $document->title }}</h1>
                <div class="intro-banner">
                    <div class="intro-content">
                        <p><strong>📒 {{ $document->title }}</strong></p>
                        <p><strong>📑 {{ strtoupper($document->title) }}</strong></p>
                        <p>&nbsp;</p>
                        @if($document->description)
                            <div>{!! nl2br(e($document->description)) !!}</div>
                        @else
                            <p><strong>👉 Căn cứ Hiến pháp nước Cộng hòa xã hội chủ nghĩa Việt Nam;</strong></p>
                            <p>&nbsp;</p>
                            <p><strong>✍️ Quốc hội ban hành Luật Quản lý thuế.</strong></p>
                            <p>&nbsp;</p>
                            <p><strong>📄 Luật này quy định việc quản lý các loại thuế, các khoản thu khác thuộc ngân sách nhà nước.</strong></p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="book-details">
                <h2>Thông tin tin tức:</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Stt</th>
                            <th>Hình ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Ngày xuất bản</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                @if($document->image && file_exists(public_path('storage/'.$document->image)))
                                    <img src="{{ asset('storage/'.$document->image) }}" alt="{{ $document->title }}">
                                @endif
                            </td>
                            <td>
                                <a href="{{ $document->link_url ?? '#' }}"><strong>{{ $document->title }}</strong></a>
                            </td>
                            <td>
                                {{ $document->published_date ? $document->published_date->format('d/m/Y') : 'Đang cập nhật' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="publisher-info">
                <p>Bạn đọc có thể liên hệ đặt mua trực tiếp trên website: <a href="http://nxbxaydung.com.vn">nxbxaydung.com.vn</a> hoặc ứng dụng phát hành sách của <strong>Nhà xuất bản Xây dựng</strong>.</p>
                <div class="contact-details">
                    <p>Trân trọng</p>
                    <p>Nhà xuất bản Xây dựng</p>
                    <p>-----------------------------------</p>
                    <p>🏠 Nhà xuất bản Xây dựng</p>
                    <p>🌐 Website: <a href="https://nxbxaydung.com.vn">https://nxbxaydung.com.vn</a></p>
                    <p>▶ Fanpage: <a href="https://fb.com/nxbxaydung">https://fb.com/nxbxaydung</a></p>
                    <p>🛍 Cửa hàng giới thiệu sản phẩm: Số 5 Hoa Lư, Hai Bà Trưng, Hà Nội</p>
                    <p>☎️ Hotline: 0965.1111.97</p>
                    <p>📧 Email: banhang@nxbxaydung.com.vn</p>
                </div>
            </section>
        </main>

        <aside class="sidebar">
            <h2>Chủ đề tiêu biểu</h2>
            <ul class="topic-list">
                @foreach($categories->take(7) as $category)
                    <li>
                        <i class="fa fa-book"></i>
                        <a href="{{ route('books.public', ['category_id' => $category->id]) }}">{{ $category->ten_the_loai }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="hot-news">
                <h2>TIN NỔI BẬT</h2>
                @foreach($hotNews as $news)
                    <div class="news-item">
                        @if($news->image && file_exists(public_path('storage/'.$news->image)))
                            <img src="{{ asset('storage/'.$news->image) }}" alt="{{ $news->title }}">
                        @endif
                        <p>
                            <a href="{{ route('tin-tuc.show', $news->id) }}">{{ Str::limit($news->title, 80) }}</a>
                        </p>
                    </div>
                @endforeach
                <a href="{{ route('books.public') }}" class="view-all-btn">Xem toàn bộ</a>
            </div>
        </aside>
    </div>

    @include('components.footer')
</body>
</html>




