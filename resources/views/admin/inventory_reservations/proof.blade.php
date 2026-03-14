@extends('layouts.admin')

@section('title', 'Chụp ảnh chứng minh')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-camera"></i>
            Chụp ảnh chứng minh
        </h1>
        <p class="page-subtitle">Tải nhiều ảnh chứng minh cho từng cuốn sách sau khi đã Ready</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-1">Thông tin yêu cầu</h3>
            <div class="text-muted">
                Mã đơn: <strong>{{ $reservation->reservation_code ?? ('#' . $reservation->id) }}</strong>
            </div>
        </div>
        <a href="{{ route('admin.inventory-reservations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="mb-2"><strong>Sách:</strong> {{ $reservation->book->ten_sach ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Độc giả:</strong> {{ $reservation->reader->ho_ten ?? ($reservation->user->name ?? 'N/A') }}</div>
                <div class="mb-2"><strong>Bản sao:</strong> {{ $reservation->inventory?->id ?? 'Chưa gán' }}</div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Ngày lấy:</strong> {{ $reservation->pickup_date ? $reservation->pickup_date->format('d/m/Y') : 'N/A' }}</div>
                <div class="mb-2"><strong>Ngày trả:</strong> {{ $reservation->return_date ? $reservation->return_date->format('d/m/Y') : 'N/A' }}</div>
                <div class="mb-2"><strong>Trạng thái:</strong> {{ $reservation->getStatusLabel() }}</div>
            </div>
        </div>

        @php
            $proofImages = is_array($reservation->proof_images) ? $reservation->proof_images : [];
        @endphp

        @if(!empty($proofImages))
            <div class="mb-4">
                <div class="fw-bold mb-2">Ảnh đã lưu</div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($proofImages as $img)
                        <div style="width: 140px;">
                            <img src="{{ asset('storage/' . $img) }}" alt="Proof image" class="img-thumbnail" style="width: 140px; height: 140px; object-fit: cover;">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('admin.inventory-reservations.proof.store', $reservation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Tải ảnh chứng minh (có thể chọn nhiều ảnh) <span class="text-danger">*</span></label>
                <input type="file" name="proof_images[]" class="form-control" accept="image/*" multiple required>
                <small class="text-muted">Hỗ trợ JPG/PNG/GIF/WebP, tối đa 4MB mỗi ảnh.</small>
            </div>

            <div id="proof-preview" class="d-flex flex-wrap gap-3 mb-3"></div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu ảnh chứng minh
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="proof_images[]"]');
    const preview = document.getElementById('proof-preview');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        const files = Array.from(this.files || []);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.width = '140px';
                img.style.height = '140px';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>
@endpush
