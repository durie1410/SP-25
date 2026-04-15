@extends('layouts.admin')

@section('title', 'Quản Lý Banner - Admin')

@section('content')
@php
    $allBannerGroups = [
        $banners ?? [],
        $panels ?? [],
        $services ?? [],
        $authors ?? [],
        $contacts ?? [],
        $news ?? [],
        $diemSach ?? [],
    ];
    $totalSlots = 0;
    $uploadedSlots = 0;
    foreach ($allBannerGroups as $group) {
        foreach ($group as $item) {
            $totalSlots++;
            if (!empty($item['exists'])) {
                $uploadedSlots++;
            }
        }
    }
    $emptySlots = max(0, $totalSlots - $uploadedSlots);
@endphp

<div class="banner-admin-hero">
    <div>
        <div class="banner-kicker">Homepage Media Studio</div>
        <h1 class="page-title">
            <i class="fas fa-images"></i>
            Quản Lý Banner Trang Chủ
        </h1>
        <p class="page-subtitle">Quản lý tập trung toàn bộ ảnh banner theo từng khu vực hiển thị trên website.</p>
    </div>
    <div class="banner-hero-actions">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-light">
            <i class="fas fa-external-link-alt me-2"></i>
            Xem Trang Chủ
        </a>
    </div>
</div>

<div class="banner-stats-grid">
    <div class="banner-stat-card">
        <div class="stat-label">Tổng vị trí banner</div>
        <div class="stat-value">{{ $totalSlots }}</div>
    </div>
    <div class="banner-stat-card stat-ok">
        <div class="stat-label">Đã có ảnh</div>
        <div class="stat-value">{{ $uploadedSlots }}</div>
    </div>
    <div class="banner-stat-card stat-wait">
        <div class="stat-label">Chưa có ảnh</div>
        <div class="stat-value">{{ $emptySlots }}</div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="banners-container">
    <!-- Banner chính (Carousel) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-images me-2"></i>Banner Chính (Carousel)</h2>
        <p class="section-subtitle">Quản lý ảnh banner cho carousel trang chủ</p>
    </div>
    <div class="row mb-5">
        @foreach($banners as $banner)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">Banner {{ $banner['number'] }}</span>
                            <span class="banner-status {{ $banner['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $banner['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $banner['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $banner['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($banner['exists'])
                        <img src="{{ $banner['path'] }}" alt="Banner {{ $banner['number'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $banner['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $banner['size'] }}</span>
                            </div>
                            @if($banner['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $banner['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', $banner['number']) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="banner">
                        <input type="file" name="banner" id="banner{{ $banner['number'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('banner{{ $banner['number'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $banner['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($banner['exists'])
                    <form action="{{ route('admin.banners.delete', $banner['number']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="banner">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Panel Boxes -->
    <div class="section-title-admin">
        <h2><i class="fas fa-th me-2"></i>Các Ô Banner (Panel Boxes)</h2>
        <p class="section-subtitle">Quản lý ảnh cho các ô banner bên phải và dưới banner chính</p>
    </div>
    <div class="row">
        @foreach($panels as $panel)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $panel['title'] }}</span>
                            <span class="banner-status {{ $panel['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $panel['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $panel['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $panel['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($panel['exists'])
                        <img src="{{ $panel['path'] }}" alt="{{ $panel['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $panel['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $panel['size'] }}</span>
                            </div>
                            @if($panel['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $panel['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', $panel['key']) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="panel">
                        <input type="file" name="banner" id="panel{{ $panel['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('panel{{ $panel['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $panel['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($panel['exists'])
                    <form action="{{ route('admin.banners.delete', $panel['key']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa panel này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="panel">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Service Banners (Trân trọng phục vụ) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-handshake me-2"></i>Banner Trân trọng phục vụ</h2>
        <p class="section-subtitle">Quản lý ảnh cho phần "Trân trọng phục vụ" - 7 banner dịch vụ</p>
    </div>
    <div class="row mb-5">
        @foreach($services as $service)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $service['title'] }}</span>
                            <span class="banner-status {{ $service['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $service['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $service['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $service['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($service['exists'])
                        <img src="{{ $service['path'] }}" alt="{{ $service['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $service['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $service['size'] }}</span>
                            </div>
                            @if($service['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $service['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', str_replace('service-', '', $service['key'])) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="service">
                        <input type="file" name="banner" id="service{{ $service['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('service{{ $service['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $service['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($service['exists'])
                    <form action="{{ route('admin.banners.delete', str_replace('service-', '', $service['key'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="service">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Author Banners (Tác giả) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-user-edit me-2"></i>Banner Tác giả</h2>
        <p class="section-subtitle">Quản lý ảnh cho phần "Tác giả" - 5 banner tác giả</p>
    </div>
    <div class="row mb-5">
        @foreach($authors as $author)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $author['title'] }}</span>
                            <span class="banner-status {{ $author['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $author['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $author['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $author['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($author['exists'])
                        <img src="{{ $author['path'] }}" alt="{{ $author['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $author['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $author['size'] }}</span>
                            </div>
                            @if($author['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $author['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', str_replace('author-', '', $author['key'])) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="author">
                        <input type="file" name="banner" id="author{{ $author['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('author{{ $author['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $author['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($author['exists'])
                    <form action="{{ route('admin.banners.delete', str_replace('author-', '', $author['key'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="author">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Contact Banners (Liên hệ - Hợp tác) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-phone-alt me-2"></i>Banner Liên hệ - Hợp tác</h2>
        <p class="section-subtitle">Quản lý ảnh cho phần "Liên hệ - Hợp tác" - 4 banner liên hệ</p>
    </div>
    <div class="row mb-5">
        @foreach($contacts as $contact)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $contact['title'] }}</span>
                            <span class="banner-status {{ $contact['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $contact['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $contact['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $contact['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($contact['exists'])
                        <img src="{{ $contact['path'] }}" alt="{{ $contact['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $contact['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $contact['size'] }}</span>
                            </div>
                            @if($contact['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $contact['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', str_replace('contact-', '', $contact['key'])) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="contact">
                        <input type="file" name="banner" id="contact{{ $contact['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('contact{{ $contact['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $contact['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($contact['exists'])
                    <form action="{{ route('admin.banners.delete', str_replace('contact-', '', $contact['key'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="contact">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- News Banners (Tin tức) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-newspaper me-2"></i>Banner Tin tức</h2>
        <p class="section-subtitle">Quản lý ảnh cho phần "Tin tức" - 1 banner nổi bật và 3 banner nhỏ</p>
    </div>
    <div class="row mb-5">
        @foreach($news as $newsItem)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $newsItem['title'] }}</span>
                            <span class="banner-status {{ $newsItem['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $newsItem['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $newsItem['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $newsItem['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($newsItem['exists'])
                        <img src="{{ $newsItem['path'] }}" alt="{{ $newsItem['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $newsItem['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $newsItem['size'] }}</span>
                            </div>
                            @if($newsItem['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $newsItem['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', str_replace('news-', '', $newsItem['key'])) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="news">
                        <input type="file" name="banner" id="news{{ $newsItem['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('news{{ $newsItem['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $newsItem['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($newsItem['exists'])
                    <form action="{{ route('admin.banners.delete', str_replace('news-', '', $newsItem['key'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="news">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Diem Sach Banners (Điểm sách) -->
    <div class="section-title-admin">
        <h2><i class="fas fa-book-open me-2"></i>Banner Điểm sách</h2>
        <p class="section-subtitle">Quản lý ảnh cho phần "Điểm sách" - 1 banner nổi bật và 3 banner nhỏ</p>
    </div>
    <div class="row mb-5">
        @foreach($diemSach as $diemSachItem)
        <div class="col-md-6 mb-4">
            <div class="banner-card">
                <div class="banner-card-header">
                    <div>
                        <h3 class="banner-title">
                            <span class="banner-number">{{ $diemSachItem['title'] }}</span>
                            <span class="banner-status {{ $diemSachItem['exists'] ? 'status-active' : 'status-inactive' }}">
                                <i class="fas {{ $diemSachItem['exists'] ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $diemSachItem['exists'] ? 'Đã tải lên' : 'Chưa có ảnh' }}
                            </span>
                        </h3>
                        <p class="banner-description">{{ $diemSachItem['description'] }}</p>
                    </div>
                </div>
                
                <div class="banner-preview">
                    @if($diemSachItem['exists'])
                        <img src="{{ $diemSachItem['path'] }}" alt="{{ $diemSachItem['title'] }}" class="banner-preview-img">
                        <div class="banner-info">
                            <div class="info-item">
                                <i class="fas fa-file me-2"></i>
                                <span>{{ $diemSachItem['filename'] }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight me-2"></i>
                                <span>{{ $diemSachItem['size'] }}</span>
                            </div>
                            @if($diemSachItem['updated_at'])
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>Cập nhật: {{ $diemSachItem['updated_at'] }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="banner-placeholder-upload">
                            <i class="fas fa-image"></i>
                            <p>Chưa có ảnh banner</p>
                        </div>
                    @endif
                </div>
                
                <div class="banner-actions">
                    <form action="{{ route('admin.banners.upload', str_replace('diem-sach-', '', $diemSachItem['key'])) }}" method="POST" enctype="multipart/form-data" class="banner-upload-form">
                        @csrf
                        <input type="hidden" name="type" value="diem-sach">
                        <input type="file" name="banner" id="diemSach{{ $diemSachItem['key'] }}" accept="image/jpeg,image/jpg,image/png,image/webp" class="d-none" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('diemSach{{ $diemSachItem['key'] }}').click()" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>
                            {{ $diemSachItem['exists'] ? 'Thay đổi ảnh' : 'Tải lên ảnh' }}
                        </button>
                    </form>
                    
                    @if($diemSachItem['exists'])
                    <form action="{{ route('admin.banners.delete', str_replace('diem-sach-', '', $diemSachItem['key'])) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="diem-sach">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Xóa
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.banner-admin-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 14px;
    background: radial-gradient(circle at 8% 14%, #1f2937 0%, #0a3f37 48%, #0f172a 100%);
    color: #f8fafc;
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.22);
}

.banner-kicker {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 700;
    color: rgba(241, 245, 249, 0.76);
    margin-bottom: 6px;
}

.page-title {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 30px;
    font-weight: 800;
}

.page-subtitle {
    margin: 8px 0 0;
    color: rgba(241, 245, 249, 0.92);
    max-width: 720px;
}

.banner-hero-actions .btn {
    border-radius: 11px;
    font-weight: 700;
    border: 0;
    color: #0f172a;
}

.banner-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.banner-stat-card {
    border: 1px solid #dbe6f2;
    background: #fff;
    border-radius: 14px;
    padding: 12px 14px;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
}

.banner-stat-card .stat-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    font-weight: 700;
}

.banner-stat-card .stat-value {
    margin-top: 2px;
    font-size: 28px;
    line-height: 1.1;
    font-weight: 800;
    color: #0f172a;
}

.banner-stat-card.stat-ok {
    background: linear-gradient(140deg, #f0fdf4 0%, #dcfce7 100%);
    border-color: #86efac;
}

.banner-stat-card.stat-ok .stat-value {
    color: #166534;
}

.banner-stat-card.stat-wait {
    background: linear-gradient(140deg, #fff7ed 0%, #ffedd5 100%);
    border-color: #fdba74;
}

.banner-stat-card.stat-wait .stat-value {
    color: #9a3412;
}

.banners-container {
    padding: 8px 0;
}

.section-title-admin {
    margin: 12px 0 18px;
    padding: 12px 14px;
    border: 1px solid #d8e4ee;
    border-radius: 14px;
    background: linear-gradient(145deg, #f8fbff 0%, #eef6f1 100%);
}

.section-title-admin h2 {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 6px;
}

.section-title-admin .section-subtitle {
    color: #475569;
    font-size: 14px;
    margin: 0;
}

.banner-card {
    background: #fff;
    border: 1px solid #dce6ef;
    border-radius: 16px;
    padding: 18px;
    transition: all 0.22s ease;
    height: 100%;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}

.banner-card:hover {
    border-color: #0f766e;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
    transform: translateY(-2px);
}

.banner-card-header {
    margin-bottom: 14px;
}

.banner-title {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 8px;
    font-size: 18px;
    font-weight: 800;
}

.banner-number {
    color: #0f172a;
}

.banner-status {
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.banner-description {
    color: #64748b;
    font-size: 14px;
    margin: 0;
}

.banner-preview {
    margin-bottom: 14px;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px dashed #c7d6e5;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.banner-preview-img {
    max-width: 100%;
    max-height: 300px;
    object-fit: contain;
    border-radius: 12px;
}

.banner-placeholder-upload {
    padding: 60px 20px;
    text-align: center;
    color: #94a3b8;
}

.banner-placeholder-upload i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.banner-info {
    width: 100%;
    background: #f8fbff;
    border-top: 1px solid #dde8f2;
    padding: 10px 12px;
    margin-top: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    flex-wrap: wrap;
    gap: 8px 10px;
}

.info-item {
    font-size: 12px;
    color: #334155;
    display: flex;
    align-items: center;
}

.banner-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.banner-actions .btn {
    flex: 1;
    min-width: 120px;
    border-radius: 10px;
    font-weight: 700;
}

.alert {
    margin-bottom: 24px;
    border-radius: 12px;
    border: none;
    padding: 16px 20px;
    display: flex;
    align-items: center;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border-left: 4px solid #16a34a;
}

.alert-danger {
    background: #fee2e2;
    color: #b91c1c;
    border-left: 4px solid #dc2626;
}

@media (max-width: 992px) {
    .banner-stats-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .banner-admin-hero {
        padding: 14px;
    }

    .page-title {
        font-size: 24px;
    }

    .banner-card {
        padding: 16px;
    }
    
    .banner-title {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .banner-actions {
        flex-direction: column;
    }
    
    .banner-actions .btn {
        width: 100%;
    }
}
</style>

@endsection



