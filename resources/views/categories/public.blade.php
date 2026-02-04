@extends('layouts.app')

@section('title', 'Thể loại sách - Thư Viện Online 4.0')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-tags text-primary"></i>
                    Thể loại sách
                </h1>
                <p class="lead text-muted">
                    Khám phá các thể loại sách đa dạng trong thư viện của chúng tôi
                </p>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="row">
        @forelse($categories as $category)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-body text-center p-4">
                    <div class="category-icon mb-3">
                        <i class="fas fa-folder-open fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title mb-3">{{ $category->ten_the_loai }}</h3>
                    <p class="text-muted mb-3">
                        <i class="fas fa-book me-2"></i>
                        {{ $category->books_count }} cuốn sách
                    </p>
                    <a href="{{ route('books.public', ['category_id' => $category->id]) }}" 
                       class="btn-modern">
                        <i class="fas fa-eye me-2"></i>
                        Xem sách
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">Chưa có thể loại nào</h3>
                <p class="text-muted">Hãy thêm thể loại vào hệ thống</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Back to Home -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-home me-2"></i>
                Về trang chủ
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.category-icon {
    transition: transform 0.3s ease;
}

.card-modern:hover .category-icon {
    transform: scale(1.1);
}

.card-modern {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}
</style>
@endpush















