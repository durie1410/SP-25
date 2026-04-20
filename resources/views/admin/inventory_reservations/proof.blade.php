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
                {{-- ảnh sách  --}}
                <div>
                    @if($reservation->book && $reservation->book->hinh_anh)
                        <img src="{{ asset('storage/' . $reservation->book->hinh_anh) }}" alt="Ảnh bìa" style="width: 130px; height: 180px; object-fit: cover; border-radius: 4px;">
                    @else
                        <div style="width: 40px; height: 60px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                            <i class="fas fa-book" style="color: #94a3b8;"></i>
                        </div>
                    @endif
                </div>
                <div class="mb-2"><strong>Sách:</strong> {{ $reservation->book->ten_sach ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Độc giả:</strong> {{ $reservation->reader->ho_ten ?? ($reservation->user->name ?? 'N/A') }}</div>
                <div class="mb-2"><strong>Bản sao:</strong> {{ $reservation->inventory?->id ?? 'Chưa gán' }}</div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Ngày lấy:</strong> {{ $reservation->pickup_display }}</div>
                <div class="mb-2">
                    <strong>Hạn nhận:</strong>
                    <span style="{{ $reservation->is_pickup_overdue ? 'color:#dc2626;font-weight:700;' : '' }}">
                        {{ $reservation->pickup_deadline_display }}
                    </span>
                </div>
                <div class="mb-2"><strong>Ngày trả:</strong> {{ $reservation->return_date ? $reservation->return_date->format('d/m/Y') : 'N/A' }}</div>
                <div class="mb-2"><strong>Trạng thái:</strong> {{ $reservation->getStatusLabel() }}</div>
                @if($reservation->is_pickup_overdue)
                    <div class="mb-2" style="color:#dc2626;font-weight:700;">⛔ Đã quá hạn nhận sách</div>
                @endif
            </div>
        </div>

        @php
            $proofImages = $reservation->getProofImages();
            $lockedStatuses = ['cancelled', 'overdue', 'fulfilled'];
            $isLocked = in_array($reservation->status, $lockedStatuses);
        @endphp

        @if($isLocked)
            <div class="alert alert-secondary mb-3">
                <i class="fas fa-lock"></i> Đơn đặt trước đã {{ $reservation->getStatusLabel() }} — không thể tải ảnh chứng minh.
            </div>
        @endif

        @if(!empty($proofImages))
            <div class="mb-4">
                <div class="fw-bold mb-2">Ảnh đã lưu ({{ count($proofImages) }} ảnh)</div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($proofImages as $idx => $img)
                        <div style="width: 140px; text-align: center;">
                            <img src="{{ asset("storage/{$img}") }}"
                                 alt="Ảnh {{ $idx + 1 }}"
                                 class="img-thumbnail"
                                 style="width: 140px; height: 140px; object-fit: cover; cursor: pointer;"
                                 onclick="showGallery({{ json_encode($proofImages) }}, {{ $idx }})">
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $idx + 1 }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(empty($proofImages) && !$isLocked)
            <form action="{{ route('admin.inventory-reservations.proof.store', $reservation->id) }}" method="POST" enctype="multipart/form-data" class="proof-upload-form" data-require-file="1">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tải ảnh chứng minh (có thể chọn nhiều ảnh) <span class="text-danger">*</span></label>
                    <input type="file" name="proof_images[]" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" multiple required>
                    <small class="text-muted">Hỗ trợ JPG/PNG/GIF/WebP, tối đa 4MB mỗi ảnh.</small>
                    <div class="text-danger mt-2 proof-client-error" style="display: none;"></div>
                </div>

                <div id="proof-preview" class="d-flex flex-wrap gap-3 mb-3"></div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu ảnh chứng minh
                </button>
            </form>
        @elseif(!empty($proofImages) && !$isLocked)
            <form action="{{ route('admin.inventory-reservations.proof.store', $reservation->id) }}" method="POST" enctype="multipart/form-data" class="proof-upload-form" data-require-file="0">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Thêm ảnh chứng minh</label>
                    <input type="file" name="proof_images[]" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" multiple>
                    <small class="text-muted">Hỗ trợ JPG/PNG/GIF/WebP, tối đa 4MB mỗi ảnh.</small>
                    <div class="text-danger mt-2 proof-client-error" style="display: none;"></div>
                </div>
                <div id="proof-preview" class="d-flex flex-wrap gap-3 mb-3"></div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm ảnh
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="proof_images[]"]');
    const preview = document.getElementById('proof-preview');
    const forms = document.querySelectorAll('.proof-upload-form');
    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const maxSizeBytes = 4 * 1024 * 1024;

    forms.forEach(function (form) {
        const fileInput = form.querySelector('input[name="proof_images[]"]');
        const errorBox = form.querySelector('.proof-client-error');
        const requireFile = form.dataset.requireFile === '1';

        if (!fileInput || !errorBox) {
            return;
        }

        form.addEventListener('submit', function (event) {
            const files = Array.from(fileInput.files || []);
            errorBox.style.display = 'none';
            errorBox.textContent = '';

            if (requireFile && files.length < 1) {
                event.preventDefault();
                errorBox.textContent = 'Vui lòng tải lên ít nhất 1 ảnh chứng minh.';
                errorBox.style.display = 'block';
                return;
            }

            for (const file of files) {
                const parts = file.name ? file.name.split('.') : [];
                const extension = parts.length > 1 ? parts.pop().toLowerCase() : '';
                const hasValidMime = file.type && allowedMimeTypes.includes(file.type);
                const hasValidExtension = extension && allowedExtensions.includes(extension);

                if (!hasValidMime && !hasValidExtension) {
                    event.preventDefault();
                    errorBox.textContent = 'File "' + file.name + '" không đúng định dạng. Chỉ chấp nhận JPG, PNG, GIF, WebP.';
                    errorBox.style.display = 'block';
                    return;
                }

                if (file.size > maxSizeBytes) {
                    event.preventDefault();
                    errorBox.textContent = 'File "' + file.name + '" vượt quá 4MB.';
                    errorBox.style.display = 'block';
                    return;
                }
            }
        });
    });

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

function showGallery(images, startIndex) {
    const existing = document.getElementById('gallery-modal');
    if (existing) existing.remove();

    const imagesList = Array.isArray(images) ? images : [images];
    let current = startIndex ?? 0;

    const overlay = document.createElement('div');
    overlay.id = 'gallery-modal';
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:99999;display:flex;flex-direction:column;align-items:center;justify-content:center;';

    const counter = document.createElement('div');
    counter.style.cssText = 'position:absolute;top:16px;left:50%;transform:translateX(-50%);color:#fff;font-size:16px;font-weight:600;';

    const img = document.createElement('img');
    img.style.cssText = 'max-width:90%;max-height:80vh;object-fit:contain;border-radius:8px;';

    function render() {
        img.src = '/storage/' + imagesList[current];
        counter.textContent = (current + 1) + ' / ' + imagesList.length;
    }
    render();

    function prev() { current = (current - 1 + imagesList.length) % imagesList.length; render(); }
    function next() { current = (current + 1) % imagesList.length; render(); }

    const closeBtn = document.createElement('button');
    closeBtn.textContent = '✕ Đóng';
    closeBtn.style.cssText = 'position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;padding:8px 20px;border-radius:6px;cursor:pointer;';
    closeBtn.onclick = () => overlay.remove();

    const prevBtn = document.createElement('button');
    prevBtn.textContent = '‹';
    prevBtn.style.cssText = 'position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;width:50px;height:50px;border-radius:50%;font-size:28px;cursor:pointer;';
    prevBtn.onclick = prev;

    const nextBtn = document.createElement('button');
    nextBtn.textContent = '›';
    nextBtn.style.cssText = 'position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;width:50px;height:50px;border-radius:50%;font-size:28px;cursor:pointer;';
    nextBtn.onclick = next;

    overlay.appendChild(counter);
    overlay.appendChild(img);
    overlay.appendChild(closeBtn);
    overlay.appendChild(prevBtn);
    overlay.appendChild(nextBtn);
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });

    document.addEventListener('keydown', function handler(e) {
        if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', handler); }
        if (e.key === 'ArrowLeft') prev();
        if (e.key === 'ArrowRight') next();
    });

    document.body.appendChild(overlay);
}
</script>
@endpush
