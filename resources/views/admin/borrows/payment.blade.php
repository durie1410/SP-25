@extends('layouts.admin')

@section('title', 'Thanh toán phiếu mượn')

@section('content')
<div class="admin-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-credit-card"></i> Thanh toán phiếu mượn #{{ $borrow->id }}</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.borrows.show', $borrow->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Thông tin thanh toán
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Độc giả:</strong>
                        <span>{{ optional($borrow->reader)->ho_ten ?? ($borrow->ten_nguoi_muon ?? 'N/A') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Số sách:</strong>
                        <span>{{ $borrow->items->count() }} sách</span>
                    </div>
                    <div class="mb-2">
                        <strong>Tiền thuê:</strong>
                        <span class="fw-bold text-danger">{{ number_format($borrow->tien_thue ?? 0) }}₫</span>
                    </div>

                    @if(!empty($momoQrUrl) && !empty($momoPayUrl))
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="fw-bold mb-2"><i class="fas fa-qrcode me-1"></i> Mã thanh toán MoMo</div>
                            <div class="d-flex flex-column align-items-center gap-2">
                                <img src="{{ $momoQrUrl }}" alt="MoMo QR" style="width: 220px; height: 220px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; padding: 6px;">
                                <div class="small text-muted">Mã đơn: <strong>{{ $momoOrderId }}</strong></div>
                                <a href="{{ $momoPayUrl }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-external-link-alt me-1"></i> Mở trang thanh toán MoMo
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($successPayment)
                        <div class="alert alert-success mt-3 mb-0">
                            <div class="fw-bold"><i class="fas fa-check-circle me-1"></i> Đã thanh toán</div>
                            <div class="small text-muted">
                                Số tiền: {{ number_format($successPayment->amount ?? 0) }}₫
                                @if($successPayment->updated_at)
                                    - {{ $successPayment->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('admin.borrows.index') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-list"></i> Về danh sách
                                </a>
                            </div>
                        </div>
                    @elseif(!$pendingPayment)
                        <div class="alert alert-warning mt-3 mb-0">
                            Không có giao dịch thanh toán đang chờ. Vui lòng quay lại và duyệt lại phiếu (hoặc tạo thanh toán).
                        </div>
                    @else
                        <div class="alert alert-info mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Giao dịch đang chờ thanh toán</div>
                                    <div class="small text-muted">Số tiền cần thu: {{ number_format($pendingPayment->amount ?? 0) }}₫</div>
                                </div>
                                <span class="badge bg-warning text-dark">PENDING</span>
                            </div>
                        </div>

                        <form id="paymentWithImagesForm" action="{{ route('admin.borrows.confirm-cash-payment', $borrow->id) }}" method="POST" class="mt-3" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="payment_method" name="payment_method" value="online">

                            <div class="mb-3 form-text">
                                Chọn phương thức thanh toán: <strong>Tiền mặt</strong> hoặc <strong>MoMo</strong>.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary w-100"
                                        onclick="document.getElementById('payment_method').value='offline'; return confirm('Xác nhận đã thu TIỀN MẶT cho phiếu mượn #{{ $borrow->id }}?')">
                                    <i class="fas fa-money-bill-wave me-1"></i> Xác nhận thanh toán tiền mặt
                                </button>

                                <button type="submit" class="btn btn-success w-100"
                                        onclick="document.getElementById('payment_method').value='online'; return confirm('Tạo mã MoMo cho phiếu mượn #{{ $borrow->id }}?')">
                                    <i class="fas fa-qrcode me-1"></i> Tạo mã thanh toán MoMo
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <i class="fas fa-book me-2"></i> Danh sách sách (tóm tắt)
                </div>
                <div class="card-body">
                    @if($successPayment)
                        <div id="upload-evidence-section" class="border border-success rounded p-3 mb-3">
                            <div class="fw-bold text-success mb-2">
                                <i class="fas fa-camera me-1"></i> Đã thanh toán, bấm nút dưới để lưu ảnh và ghi chú
                            </div>
                            <p class="mb-3">Sau khi thanh toán thành công, bạn có thể lưu/ cập nhật ảnh và ghi chú xác nhận cho từng cuốn sách.</p>

                            <form id="saveReceiveEvidenceForm" action="{{ route('admin.borrows.save-receive-evidence', $borrow->id) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                @csrf
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 72px;">Ảnh</th>
                                            <th>Tên sách</th>
                                            <th style="width: 120px;">Trạng thái</th>
                                            <th style="width: 110px;">Thuê</th>
                                            <th style="width: 150px;">Bìa trước <span class="text-danger">*</span></th>
                                            <th style="width: 150px;">Bìa sau <span class="text-danger">*</span></th>
                                            <th style="width: 150px;">Gáy sách <span class="text-danger">*</span></th>
                                            <th style="width: 260px;">Ghi chú xác nhận <span class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($borrow->items as $item)
                                            <tr>
                                                <td>
                                                    <div style="width:40px; height:55px; background:#f1f5f9; border-radius:6px; overflow:hidden;">
                                                        @if(optional($item->book)->hinh_anh)
                                                            <img src="{{ asset('storage/' . $item->book->hinh_anh) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center" style="width:100%; height:100%; color:#94a3b8;">📘</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $item->book->ten_sach ?? 'N/A' }}</td>
                                                <td><span class="small text-muted">{{ $item->trang_thai }}</span></td>
                                                <td>{{ number_format($item->tien_thue ?? 0) }}₫</td>
                                                <td>
                                                    <input type="file"
                                                        class="form-control form-control-sm book-image-input"
                                                        name="book_images_front[{{ $item->id }}]"
                                                        form="saveReceiveEvidenceForm"
                                                        data-preview-id="book_image_front_preview_{{ $item->id }}"
                                                        accept="image/*"
                                                        {{ empty($item->anh_bia_truoc) ? 'required' : '' }}>
                                                    <img id="book_image_front_preview_{{ $item->id }}" src="{{ $item->anh_bia_truoc ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_bia_truoc) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh bìa trước">
                                                </td>
                                                <td>
                                                    <input type="file"
                                                        class="form-control form-control-sm book-image-input"
                                                        name="book_images_back[{{ $item->id }}]"
                                                        form="saveReceiveEvidenceForm"
                                                        data-preview-id="book_image_back_preview_{{ $item->id }}"
                                                        accept="image/*"
                                                        {{ empty($item->anh_bia_sau) ? 'required' : '' }}>
                                                    <img id="book_image_back_preview_{{ $item->id }}" src="{{ $item->anh_bia_sau ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_bia_sau) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh bìa sau">
                                                </td>
                                                <td>
                                                    <input type="file"
                                                        class="form-control form-control-sm book-image-input"
                                                        name="book_images_spine[{{ $item->id }}]"
                                                        form="saveReceiveEvidenceForm"
                                                        data-preview-id="book_image_spine_preview_{{ $item->id }}"
                                                        accept="image/*"
                                                        {{ empty($item->anh_gay_sach) ? 'required' : '' }}>
                                                    <img id="book_image_spine_preview_{{ $item->id }}" src="{{ $item->anh_gay_sach ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_gay_sach) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh gáy sách">
                                                </td>
                                                <td>
                                                    <textarea name="book_notes[{{ $item->id }}]" rows="2" class="form-control form-control-sm" placeholder="Ghi chú tình trạng cuốn sách này..." form="saveReceiveEvidenceForm" required>{{ old('book_notes.' . $item->id, $item->ghi_chu_nhan_sach ?? '') }}</textarea>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-muted text-center">Không có sách trong phiếu.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3 w-100" form="saveReceiveEvidenceForm"
                                    onclick="return confirm('Lưu ảnh và ghi chú xác nhận nhận sách cho phiếu #{{ $borrow->id }}?')">
                                <i class="fas fa-save me-1"></i> Lưu ảnh và ghi chú sau thanh toán
                            </button>
                        </div>
                    @elseif(!$borrow->anh_bia_truoc || !$borrow->anh_bia_sau || !$borrow->anh_gay_sach)
                        <div class="border border-success rounded p-3 mb-3">
                            <div class="fw-bold text-success mb-2">
                                <i class="fas fa-camera me-1"></i> Mỗi cuốn sách bắt buộc tải 3 ảnh và ghi chú
                            </div>
                            <p class="mb-3">Bạn có thể tạo mã MoMo trước. Ảnh và ghi chú nếu đã nhập sẽ được lưu lại ngay.</p>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 72px;">Ảnh</th>
                                                <th>Tên sách</th>
                                                <th style="width: 120px;">Trạng thái</th>
                                                <th style="width: 110px;">Thuê</th>
                                                <th style="width: 150px;">Bìa trước</th>
                                                <th style="width: 150px;">Bìa sau</th>
                                                <th style="width: 150px;">Gáy sách</th>
                                                <th style="width: 260px;">Ghi chú xác nhận</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($borrow->items as $item)
                                                <tr>
                                                    <td>
                                                        <div style="width:40px; height:55px; background:#f1f5f9; border-radius:6px; overflow:hidden;">
                                                            @if(optional($item->book)->hinh_anh)
                                                                <img src="{{ asset('storage/' . $item->book->hinh_anh) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                            @else
                                                                <div class="d-flex align-items-center justify-content-center" style="width:100%; height:100%; color:#94a3b8;">📘</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $item->book->ten_sach ?? 'N/A' }}</td>
                                                    <td><span class="small text-muted">{{ $item->trang_thai }}</span></td>
                                                    <td>{{ number_format($item->tien_thue ?? 0) }}₫</td>
                                                    <td>
                                                        <input type="file"
                                                            class="form-control form-control-sm book-image-input"
                                                            name="book_images_front[{{ $item->id }}]"
                                                            form="paymentWithImagesForm"
                                                            data-preview-id="book_image_front_preview_{{ $item->id }}"
                                                            accept="image/*">
                                                        <img id="book_image_front_preview_{{ $item->id }}" src="{{ $item->anh_bia_truoc ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_bia_truoc) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh bìa trước">
                                                    </td>
                                                    <td>
                                                        <input type="file"
                                                            class="form-control form-control-sm book-image-input"
                                                            name="book_images_back[{{ $item->id }}]"
                                                            form="paymentWithImagesForm"
                                                            data-preview-id="book_image_back_preview_{{ $item->id }}"
                                                            accept="image/*">
                                                        <img id="book_image_back_preview_{{ $item->id }}" src="{{ $item->anh_bia_sau ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_bia_sau) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh bìa sau">
                                                    </td>
                                                    <td>
                                                        <input type="file"
                                                            class="form-control form-control-sm book-image-input"
                                                            name="book_images_spine[{{ $item->id }}]"
                                                            form="paymentWithImagesForm"
                                                            data-preview-id="book_image_spine_preview_{{ $item->id }}"
                                                            accept="image/*">
                                                        <img id="book_image_spine_preview_{{ $item->id }}" src="{{ $item->anh_gay_sach ?? '' }}" class="img-thumbnail mt-1 {{ empty($item->anh_gay_sach) ? 'd-none' : '' }}" style="height: 70px; width: 70px; object-fit: cover;" alt="Preview ảnh gáy sách">
                                                    </td>
                                                    <td>
                                                        <textarea name="book_notes[{{ $item->id }}]" rows="2" class="form-control form-control-sm" placeholder="Ghi chú tình trạng cuốn sách này..." form="paymentWithImagesForm">{{ old('book_notes.' . $item->id, $item->ghi_chu_nhan_sach ?? '') }}</textarea>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-muted text-center">Không có sách trong phiếu.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 72px;">Ảnh</th>
                                        <th>Tên sách</th>
                                        <th style="width: 120px;">Trạng thái</th>
                                        <th style="width: 110px;">Thuê</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($borrow->items as $item)
                                        <tr>
                                            <td>
                                                <div style="width:40px; height:55px; background:#f1f5f9; border-radius:6px; overflow:hidden;">
                                                    @if(optional($item->book)->hinh_anh)
                                                        <img src="{{ asset('storage/' . $item->book->hinh_anh) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center" style="width:100%; height:100%; color:#94a3b8;">📘</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $item->book->ten_sach ?? 'N/A' }}</td>
                                            <td><span class="small text-muted">{{ $item->trang_thai }}</span></td>
                                            <td>{{ number_format($item->tien_thue ?? 0) }}₫</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Không có sách trong phiếu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('open_upload_evidence'))
            const uploadSection = document.getElementById('upload-evidence-section');
            if (uploadSection) {
                uploadSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        @endif

        const bindImagePreview = (input, preview) => {
            if (!input || !preview) return;

            input.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) {
                    preview.src = '';
                    preview.classList.add('d-none');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        };

        document.querySelectorAll('.book-image-input').forEach((input) => {
            const previewId = input.dataset.previewId;
            const preview = previewId ? document.getElementById(previewId) : null;
            bindImagePreview(input, preview);
        });
    });
</script>
@endpush

