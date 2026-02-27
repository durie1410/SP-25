@extends('account._layout')

@section('title', 'Sách đang mượn')
@section('breadcrumb', 'Sách đang mượn')

@push('scripts')
<script src="{{ asset('js/borrow-status-flow.js') }}"></script>
<style>
    .img-preview-container {
        margin-top: 1rem;
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 0.5rem;
        text-align: center;
        position: relative;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .img-preview-container img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="account-section">
    <h2 class="section-title">Sách đang mượn</h2>
    
    @php
        $user = auth()->user();
        $hasCompleteProfile = $user->hasCompleteProfile();
    @endphp
    
    @if(!$hasCompleteProfile)
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <h3>Thông tin cá nhân chưa đầy đủ</h3>
            <p>Vui lòng cập nhật đầy đủ thông tin cá nhân để có thể mượn sách từ thư viện!</p>
            <p class="text-muted">Các trường còn thiếu: {{ implode(', ', $user->getMissingFields()) }}</p>
            <a href="{{ route('account') }}" class="btn-primary">Cập nhật thông tin</a>
        </div>
    @elseif(!$reader)
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Bạn chưa có sách đang mượn</h3>
            <p>Hãy tìm sách và thêm vào giỏ mượn để bắt đầu mượn sách!</p>
            <a href="{{ route('books.public') }}" class="btn-primary">Xem danh sách sách</a>
        </div>
    @elseif($borrows->total() > 0)
        <div class="books-grid">
            {{-- Hiển thị các Borrow đang mượn --}}
            @foreach($borrows as $borrow)
                <div class="book-card">
                    <div class="book-image">
                        @if($borrow->book && $borrow->book->hinh_anh)
                            <img src="{{ $borrow->book->image_url ?? asset('images/default-book.png') }}" alt="{{ $borrow->book->ten_sach }}">
                        @else
                            <div class="book-placeholder">📖</div>
                        @endif
                    </div>
                    <div class="book-info">
                        <h3 class="book-title">{{ $borrow->book->ten_sach ?? 'N/A' }}</h3>
                        <p class="book-author">{{ $borrow->book->tac_gia ?? '' }}</p>
                        <div class="book-meta">
                            @php
                                $firstItem = $borrow->borrowItems->first();
                                // Đảm bảo ngay_hen_tra là Carbon object để tính toán
                                if ($firstItem && $firstItem->ngay_hen_tra && !($firstItem->ngay_hen_tra instanceof \Carbon\Carbon)) {
                                    $firstItem->ngay_hen_tra = \Carbon\Carbon::parse($firstItem->ngay_hen_tra);
                                }
                                $hasOverdue = $firstItem ? $firstItem->isOverdue() : false;
                                
                                // Xử lý hiển thị trạng thái: nếu đã được duyệt (Cho duyet + don_hang_moi) thì hiển thị "Đã được duyệt"
                                if ($borrow->trang_thai === 'Cho duyet' && $borrow->trang_thai_chi_tiet === \App\Models\Borrow::STATUS_DON_HANG_MOI) {
                                    $statusLabel = 'Đã được duyệt';
                                    $statusColor = 'success';
                                } else {
                                $statusConfig = config('borrow_status.statuses.' . $borrow->trang_thai_chi_tiet, []);
                                $statusLabel = $statusConfig['label'] ?? $borrow->trang_thai_chi_tiet;
                                $statusColor = $statusConfig['color'] ?? 'secondary';
                                }
                            @endphp
                            <p><strong>Trạng thái:</strong> 
                                <span class="status-badge status-{{ $statusColor }}" style="
                                    display: inline-block;
                                    padding: 4px 10px;
                                    border-radius: 12px;
                                    font-size: 12px;
                                    font-weight: 600;
                                    background-color: {{ $statusColor === 'warning' ? '#ffc107' : ($statusColor === 'success' ? '#28a745' : ($statusColor === 'danger' ? '#dc3545' : ($statusColor === 'info' ? '#17a2b8' : ($statusColor === 'primary' ? '#007bff' : '#6c757d')))) }};
                                    color: white;
                                ">
                                    {{ $statusLabel }}
                                </span>
                            </p>
                            <p><strong>Ngày mượn:</strong> 
                                @php
                                    $ngayMuon = $borrow->ngay_muon;
                                    if ($ngayMuon && !($ngayMuon instanceof \Carbon\Carbon)) {
                                        $ngayMuon = \Carbon\Carbon::parse($ngayMuon);
                                    }
                                @endphp
                                {{ $ngayMuon ? $ngayMuon->format('d/m/Y') : $borrow->created_at->format('d/m/Y') }}
                            </p>
                            @if($firstItem)
                            <p><strong>Hạn trả:</strong> 
                                <span class="{{ $hasOverdue ? 'text-danger' : '' }}">
                                    @php
                                        $ngayHenTra = $firstItem->ngay_hen_tra;
                                        if ($ngayHenTra && !($ngayHenTra instanceof \Carbon\Carbon)) {
                                            $ngayHenTra = \Carbon\Carbon::parse($ngayHenTra);
                                        }
                                    @endphp
                                    {{ $ngayHenTra ? $ngayHenTra->format('d/m/Y') : 'Chưa xác định' }}
                                </span>
                            </p>
                            @if($hasOverdue)
                                @php
                                    $ngayHenTraForDiff = $firstItem->ngay_hen_tra;
                                    if ($ngayHenTraForDiff && !($ngayHenTraForDiff instanceof \Carbon\Carbon)) {
                                        $ngayHenTraForDiff = \Carbon\Carbon::parse($ngayHenTraForDiff);
                                    }
                                    $daysOverdue = $ngayHenTraForDiff ? \Carbon\Carbon::today()->diffInDays($ngayHenTraForDiff) : 0;
                                @endphp
                                <p class="text-danger"><strong>Quá hạn:</strong> {{ $daysOverdue }} ngày</p>
                            @endif
                            @if($firstItem->so_lan_gia_han > 0)
                                <p><strong>Số lần gia hạn:</strong> {{ $firstItem->so_lan_gia_han }}/2</p>
                            @endif
                            @endif
                            @if($borrow->ngay_yeu_cau_tra_sach)
                                @php
                                    $ngayYeuCauTra = $borrow->ngay_yeu_cau_tra_sach;
                                    if ($ngayYeuCauTra && !($ngayYeuCauTra instanceof \Carbon\Carbon)) {
                                        $ngayYeuCauTra = \Carbon\Carbon::parse($ngayYeuCauTra);
                                    }
                                @endphp
                                <p><strong>Ngày yêu cầu trả:</strong> {{ $ngayYeuCauTra ? $ngayYeuCauTra->format('d/m/Y H:i') : '' }}</p>
                            @endif
                        </div>
                        <div class="book-borrow-info">
                            @if($borrow->librarian)
                                <p><strong>Thủ thư:</strong> {{ $borrow->librarian->name }}</p>
                            @endif
                            @if($borrow->ghi_chu)
                                <p><strong>Ghi chú:</strong> {{ $borrow->ghi_chu }}</p>
                            @endif
                            @php
                                // Kiểm tra trạng thái chờ xác nhận
                                // Cho phép xác nhận khi đang giao hàng hoặc đã giao hàng thành công
                                $needsConfirmation = ($borrow->trang_thai_chi_tiet === 'dang_giao_hang' || $borrow->trang_thai_chi_tiet === 'giao_hang_thanh_cong') 
                                    && !$borrow->customer_confirmed_delivery && !$borrow->customer_rejected_delivery;
                            @endphp
                            @if($needsConfirmation)
                                <div class="alert alert-warning" style="margin-top: 10px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                                    @if($borrow->trang_thai_chi_tiet === 'dang_giao_hang')
                                        <strong>📦 Đã nhận sách:</strong> Nếu bạn đã nhận sách, vui lòng xác nhận để hoàn tất quá trình giao hàng.
                                    @else
                                        <strong>⚠️ Chờ xác nhận:</strong> Admin đã đánh dấu giao hàng thành công. Vui lòng xác nhận bạn đã nhận sách.
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if($borrow->borrowItems && $borrow->borrowItems->count() > 0)
                            <button type="button" class="btn-view-book" onclick="showBorrowDetail({{ $borrow->id }})">Xem chi tiết</button>
                            @php
                                $firstItemCanExtend = $firstItem && $firstItem->trang_thai === 'Dang muon' && !$hasOverdue && (($firstItem->so_lan_gia_han ?? 0) < 2);
                            @endphp
                            @if($firstItemCanExtend)
                                @if($borrow->customer_extension_requested)
                                    <p style="margin-top: 8px; font-size: 12px; color: #0d6efd;">
                                        🔁 Bạn đã gửi yêu cầu gia hạn (+{{ $borrow->customer_extension_days ?? 5 }} ngày). Vui lòng chờ thư viện duyệt.
                                    </p>
                                @else
                                    <form action="{{ route('account.borrows.extend', $borrow->id) }}" method="POST" style="margin-top: 10px;">
                                        @csrf
                                        <button type="submit" class="btn-extend-borrow"
                                                onclick="return confirm('Gửi yêu cầu gia hạn thêm 5 ngày cho tất cả sách trong phiếu này. Thư viện sẽ kiểm tra và duyệt nếu phù hợp. Phí gia hạn sẽ được tính cùng lúc khi bạn trả sách.');">
                                            🔁 Gửi yêu cầu gia hạn (+5 ngày)
                                        </button>
                                    </form>
                                @endif
                            @elseif($firstItem && $firstItem->so_lan_gia_han >= 2)
                                <p style="margin-top: 8px; font-size: 12px; color: #999;">Đã gia hạn tối đa 2 lần, không thể gia hạn thêm.</p>
                            @elseif($hasOverdue)
                                <p style="margin-top: 8px; font-size: 12px; color: #dc3545;">Sách đã quá hạn, vui lòng hoàn trả hoặc liên hệ thư viện để xử lý.</p>
                            @endif
                        @endif
                            @php
                                // Cho phép xác nhận khi đang giao hàng hoặc đã giao hàng thành công
                                $needsConfirmation = ($borrow->trang_thai_chi_tiet === 'dang_giao_hang' || $borrow->trang_thai_chi_tiet === 'giao_hang_thanh_cong') 
                                    && !$borrow->customer_confirmed_delivery && !$borrow->customer_rejected_delivery;
                                // Cho phép từ chối CHỈ khi đã giao hàng thành công (không cho từ chối khi đang giao hàng)
                                $canReject = ($borrow->trang_thai_chi_tiet === 'giao_hang_thanh_cong') 
                                    && !$borrow->customer_confirmed_delivery && !$borrow->customer_rejected_delivery;
                                $canReturnBook = $borrow->trang_thai_chi_tiet === 'cho_tra_sach';
                                $isReturnShipping = $borrow->trang_thai_chi_tiet === 'dang_van_chuyen_tra_ve';
                            @endphp
                            @if($needsConfirmation)
                                <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
                                    <strong style="display: block; margin-bottom: 10px; color: #856404;">📦 Chờ khách xác nhận nhận sách</strong>
                                    <p style="margin-bottom: 10px; color: #856404; font-size: 14px;">
                                        Bạn chỉ cần nhấn nút <strong>\"Xác nhận đã nhận\"</strong> sau khi sách đã được giao tới.
                                        Ảnh tình trạng sách khi giao sẽ do Thủ thư/Admin upload và dùng làm chuẩn so sánh.
                                    </p>
                                    <form id="confirmDeliveryForm{{ $borrow->id }}" action="{{ route('account.borrows.confirm-delivery', $borrow->id) }}" method="POST" style="flex: 1;">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="btn-confirm-delivery" 
                                            style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; font-weight: 600; cursor: pointer;">
                                            ✅ Xác nhận đã nhận sách
                                        </button>
                                    </form>
                                </div>
                                    {{-- Không cho từ chối khi đang giao hàng; chỉ cho khi đã giao thành công và chưa xác nhận --}}
                                    {{-- @if($canReject)
                                        <button 
                                            type="button" 
                                            class="btn-reject-delivery" 
                                            data-borrow-action="reject-delivery"
                                            data-borrow-id="{{ $borrow->id }}"
                                            data-current-status="{{ $borrow->trang_thai_chi_tiet }}"
                                            onclick="showRejectDeliveryModal({{ $borrow->id }})"
                                            style="flex: 1;">
                                            ❌ Từ chối nhận sách
                                        </button>
                                    @endif --}}
                                </div>
                            @endif
                            @if($borrow->customer_rejected_delivery)
                                <div class="alert alert-danger" style="margin-top: 10px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;">
                                    <strong>⚠️ Đã từ chối nhận sách:</strong> 
                                    @if($borrow->customer_rejection_reason)
                                        <br>{{ $borrow->customer_rejection_reason }}
                                    @endif
                                    <br><small>Admin sẽ được thông báo và liên hệ với bạn để xử lý.</small>
                                </div>
                            @endif
                        @if($canReturnBook)
                            <div class="alert alert-warning" style="margin-top: 10px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                                <strong>📦 Chờ hoàn trả:</strong> Admin đã yêu cầu bạn trả sách. Vui lòng chuẩn bị sách và xác nhận hoàn trả.
                            </div>
                            <button 
                                type="button" 
                                class="btn-return-book" 
                                data-borrow-action="return-book"
                                data-borrow-id="{{ $borrow->id }}"
                                data-current-status="{{ $borrow->trang_thai_chi_tiet }}"
                                onclick="showReturnBookModal({{ $borrow->id }})" 
                                style="margin-top: 10px;">
                                ✅ Hoàn trả sách
                            </button>
                        @endif
                        @if($isReturnShipping)
                            <div class="alert alert-success" style="margin-top: 10px; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">
                                <strong>🚚 Đang vận chuyển trả về:</strong> Sách của bạn đang được vận chuyển trả về thư viện. Vui lòng chuẩn bị sách để giao cho shipper.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($borrows->total() > 0)
            <div class="pagination-wrapper">
                {{ $borrows->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Bạn chưa mượn sách nào</h3>
            <p>Hãy khám phá và mượn sách từ thư viện của chúng tôi!</p>
            <a href="{{ route('books.public') }}" class="btn-primary">Khám phá sách</a>
        </div>
    @endif
</div>


{{-- Modal chi tiết Borrow --}}
<div id="borrowDetailModal" class="detail-modal-overlay" onclick="closeBorrowDetailModal(event)">
    <div class="detail-modal" onclick="event.stopPropagation()">
        <button class="close-modal" onclick="closeBorrowDetailModal(event)">&times;</button>
        <div class="detail-modal-header">
            <h2>Chi tiết phiếu mượn</h2>
        </div>
        <div class="detail-modal-body" id="borrowDetailContent">
            {{-- Nội dung sẽ được load bằng JavaScript --}}
        </div>
    </div>
</div>

{{-- Modal yêu cầu trả sách --}}
<div id="requestReturnModal" class="detail-modal-overlay" onclick="closeRequestReturnModal(event)">
    <div class="detail-modal" onclick="event.stopPropagation()" style="max-width: 500px;">
        <button class="close-modal" onclick="closeRequestReturnModal(event)">&times;</button>
        <div class="detail-modal-header">
            <h2>Yêu cầu trả sách</h2>
        </div>
        <div class="detail-modal-body">
            <form id="requestReturnForm" method="POST">
                @csrf
                <div style="margin-bottom: 20px;">
                    <p>Bạn có chắc chắn muốn yêu cầu trả sách? Admin sẽ liên hệ với bạn để xử lý.</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="ghi_chu" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                        Ghi chú (tùy chọn):
                    </label>
                    <textarea 
                        id="ghi_chu" 
                        name="ghi_chu" 
                        rows="4" 
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; resize: vertical;"
                        placeholder="Nhập ghi chú nếu có (ví dụ: lý do trả sách, thời gian mong muốn...)"
                    ></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-cancel" onclick="closeRequestReturnModal(event)" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Hủy
                    </button>
                    <button type="submit" class="btn-submit-return" style="padding: 10px 20px; background-color: #d82329; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        Xác nhận yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal hoàn trả sách --}}
<div id="returnBookModal" class="detail-modal-overlay" onclick="closeReturnBookModal(event)">
    <div class="detail-modal" onclick="event.stopPropagation()" style="max-width: 600px;">
        <button class="close-modal" onclick="closeReturnBookModal(event)">&times;</button>
        <div class="detail-modal-header">
            <h2>Hoàn trả sách</h2>
        </div>
        <div class="detail-modal-body">
            <form id="returnBookForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 20px;">
                    <p><strong>⚠️ Lưu ý:</strong> Sau khi xác nhận hoàn trả, sách sẽ được vận chuyển trả về thư viện. Vui lòng chuẩn bị sách để giao cho shipper.</p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="tinh_trang_sach_return" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                        Tình trạng sách <span style="color: red;">*</span>:
                    </label>
                    <select 
                        id="tinh_trang_sach_return" 
                        name="tinh_trang_sach" 
                        required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;"
                    >
                        <option value="">-- Chọn tình trạng sách --</option>
                        <option value="binh_thuong">Bình thường</option>
                        <option value="hong_nhe">Hỏng nhẹ</option>
                        <option value="hong_nang">Hỏng nặng</option>
                        <option value="mat_sach">Mất sách</option>
                    </select>
                </div>

                {{-- Theo quy định mới: Khách hàng trả sách KHÔNG cần upload ảnh, chỉ cần xác nhận đã gửi trả sách.
                     Ảnh khi nhận sách trả về sẽ do Admin upload ở màn hình kiểm tra/hoàn tất đơn. --}}

                <!-- Thông tin hư hỏng chi tiết (chỉ hiển thị khi chọn hỏng/mất) -->
                <div id="damage-details-section" style="display: none; margin-bottom: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                    <h4 style="margin-top: 0; color: #856404;">
                        <i class="fas fa-exclamation-triangle"></i> Thông tin hư hỏng/Mất sách
                    </h4>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="damage_type" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                            Loại hư hỏng:
                        </label>
                        <select 
                            id="damage_type" 
                            name="damage_type" 
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;"
                        >
                            <option value="">-- Chọn loại hư hỏng --</option>
                            <option value="trang_bi_rach">Trang bị rách</option>
                            <option value="bia_bi_hu">Bìa bị hỏng</option>
                            <option value="trang_bi_meo">Trang bị méo</option>
                            <option value="mat_trang">Mất trang</option>
                            <option value="bi_am_moc">Bị ẩm mốc</option>
                            <option value="bi_ban">Bị bẩn</option>
                            <option value="mat_sach">Mất sách</option>
                            <option value="khac">Khác</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="damage_description" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                            Mô tả chi tiết tình trạng hư hỏng:
                        </label>
                        <textarea 
                            id="damage_description" 
                            name="damage_description" 
                            rows="4" 
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; resize: vertical;"
                            placeholder="Mô tả chi tiết về tình trạng hư hỏng, vị trí hư hỏng, mức độ nghiêm trọng..."
                        ></textarea>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Vui lòng mô tả chi tiết để thư viện có thể đánh giá chính xác tình trạng sách.
                        </small>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="ghi_chu_return" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                        Ghi chú (tùy chọn):
                    </label>
                    <textarea 
                        id="ghi_chu_return" 
                        name="ghi_chu" 
                        rows="4" 
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; resize: vertical;"
                        placeholder="Nhập ghi chú nếu có (ví dụ: thời gian có thể giao, địa điểm giao...)"
                    ></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-cancel" onclick="closeReturnBookModal(event)" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Hủy
                    </button>
                    <button type="submit" class="btn-submit-return" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        Xác nhận hoàn trả
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal từ chối nhận sách --}}
<div id="rejectDeliveryModal" class="detail-modal-overlay" onclick="closeRejectDeliveryModal(event)">
    <div class="detail-modal" onclick="event.stopPropagation()" style="max-width: 500px;">
        <button class="close-modal" onclick="closeRejectDeliveryModal(event)">&times;</button>
        <div class="detail-modal-header">
            <h2>Từ chối nhận sách</h2>
        </div>
        <div class="detail-modal-body">
            <form id="rejectDeliveryForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label for="rejection_image" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                        Ảnh minh chứng (bắt buộc) <span style="color: #dc3545;">*</span>:
                    </label>
                    <input 
                        type="file" 
                        id="rejection_image" 
                        name="rejection_image" 
                        accept="image/*" 
                        required
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;">
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Vui lòng chụp lại tình trạng sách/bưu kiện (sách rách, sai sách, bao bì hư hỏng, ...). Ảnh sẽ dùng làm bằng chứng khiếu nại.
                    </small>
                </div>
                <div style="margin-bottom: 20px;">
                    <p><strong>⚠️ Lưu ý:</strong> Nếu bạn từ chối nhận sách, đơn hàng sẽ được chuyển sang trạng thái "Giao hàng Thất bại". Admin sẽ được thông báo và liên hệ với bạn để xử lý.</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="rejection_reason" style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                        Lý do từ chối nhận sách <span style="color: #dc3545;">*</span>:
                    </label>
                    <textarea 
                        id="rejection_reason" 
                        name="rejection_reason" 
                        rows="5" 
                        required
                        minlength="10"
                        maxlength="1000"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; resize: vertical;"
                        placeholder="Vui lòng nhập lý do từ chối nhận sách (tối thiểu 10 ký tự). Ví dụ: Sách bị hỏng, không đúng sách đã đặt, không nhận được hàng..."
                    ></textarea>
                    <small style="color: #666; display: block; margin-top: 5px;">Tối thiểu 10 ký tự, tối đa 1000 ký tự</small>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-cancel" onclick="closeRejectDeliveryModal(event)" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Hủy
                    </button>
                    <button type="submit" class="btn-submit-reject" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@if($borrows->total() > 0)
<script>
    const borrowsData = {
        @foreach($borrows as $borrow)
        {{ $borrow->id }}: {
            id: {{ $borrow->id }},
            reader: {
                ho_ten: {!! json_encode($borrow->reader->ho_ten ?? '') !!},
                so_the_doc_gia: {!! json_encode($borrow->reader->so_the_doc_gia ?? '') !!},
                email: {!! json_encode($borrow->reader->email ?? '') !!},
                so_dien_thoai: {!! json_encode($borrow->reader->so_dien_thoai ?? '') !!},
            },
            librarian: {
                name: {!! json_encode($borrow->librarian->name ?? '') !!},
            },
            ten_nguoi_muon: {!! json_encode($borrow->ten_nguoi_muon ?? '') !!},
            so_dien_thoai: {!! json_encode($borrow->so_dien_thoai ?? '') !!},
            tinh_thanh: {!! json_encode($borrow->tinh_thanh ?? '') !!},
            huyen: {!! json_encode($borrow->huyen ?? '') !!},
            xa: {!! json_encode($borrow->xa ?? '') !!},
            so_nha: {!! json_encode($borrow->so_nha ?? '') !!},
            ngay_muon: {!! json_encode($borrow->ngay_muon ? (\Carbon\Carbon::parse($borrow->ngay_muon)->format('d/m/Y')) : '') !!},
            trang_thai: {!! json_encode($borrow->trang_thai ?? '') !!},
            trang_thai_chi_tiet: {!! json_encode($borrow->trang_thai_chi_tiet ?? '') !!},
            ngay_yeu_cau_tra_sach: {!! json_encode($borrow->ngay_yeu_cau_tra_sach ? (\Carbon\Carbon::parse($borrow->ngay_yeu_cau_tra_sach)->format('d/m/Y H:i')) : null) !!},
            ngay_hen_tra_raw: {!! json_encode(optional($borrow->borrowItems->first())->ngay_hen_tra ? \Carbon\Carbon::parse($borrow->borrowItems->first()->ngay_hen_tra)->format('Y-m-d') : null) !!},
            ngay_hen_tra: {!! json_encode(optional($borrow->borrowItems->first())->ngay_hen_tra ? \Carbon\Carbon::parse($borrow->borrowItems->first()->ngay_hen_tra)->format('d/m/Y') : null) !!},
            trang_thai_coc: {!! json_encode($borrow->trang_thai_coc ?? '') !!},
            customer_confirmed_delivery: {{ $borrow->customer_confirmed_delivery ? 'true' : 'false' }},
            needs_confirmation: {{ ($borrow->trang_thai_chi_tiet === 'giao_hang_thanh_cong' && !$borrow->customer_confirmed_delivery) ? 'true' : 'false' }},
            @php
                // Phía bạn đọc chỉ hiển thị tiền thuê giống hệt admin:
                // dùng đúng trường borrow->tien_thue (đã được controller/biz logic tính sẵn)
                $tienThue = (float) ($borrow->tien_thue ?? 0);
            @endphp
            tong_tien: {{ $tienThue }},
            tien_coc: 0,
            tien_thue: {{ $tienThue }},
            tien_ship: 0,
            ghi_chu: {!! json_encode($borrow->ghi_chu ?? '') !!},
            @php
                // ShippingLog đã bị xóa, không còn thông tin giao hàng thất bại
                $failureReason = null;
            @endphp
            @php
                $failureProof = null;
            @endphp
            failure_reason: {!! json_encode($failureReason) !!},
            failure_reason_label: {!! json_encode($failureReason === 'loi_khach_hang' ? 'Lỗi do Khách hàng' : ($failureReason === 'loi_thu_vien' ? 'Lỗi do Sách/Thư viện' : null)) !!},
            failure_proof_image: {!! json_encode($failureProof) !!},
            borrowItems: [
                @foreach($borrow->borrowItems as $item)
                {
                    id: {{ $item->id }},
                    book: {
                        id: {{ $item->book->id ?? 0 }},
                        ten_sach: {!! json_encode($item->book->ten_sach ?? 'N/A') !!},
                        tac_gia: {!! json_encode($item->book->tac_gia ?? '') !!},
                        hinh_anh: {!! json_encode($item->book->image_url ?? null) !!},
                        isbn: {!! json_encode($item->book->isbn ?? '') !!},
                    },
                    ngay_muon: {!! json_encode($item->ngay_muon ? (\Carbon\Carbon::parse($item->ngay_muon)->format('d/m/Y')) : '') !!},
                    ngay_hen_tra: {!! json_encode($item->ngay_hen_tra ? (\Carbon\Carbon::parse($item->ngay_hen_tra)->format('d/m/Y')) : '') !!},
                    ngay_tra_thuc_te: {!! json_encode($item->ngay_tra_thuc_te ? (\Carbon\Carbon::parse($item->ngay_tra_thuc_te)->format('d/m/Y')) : null) !!},
                    trang_thai: {!! json_encode($item->trang_thai ?? '') !!},
                    so_lan_gia_han: {{ $item->so_lan_gia_han ?? 0 }},
                    ngay_gia_han_cuoi: {!! json_encode($item->ngay_gia_han_cuoi ? (\Carbon\Carbon::parse($item->ngay_gia_han_cuoi)->format('d/m/Y')) : null) !!},
                    tien_coc: {{ $item->tien_coc ?? 0 }},
                    tien_thue: {{ $item->tien_thue ?? 0 }},
                    tien_ship: {{ $item->tien_ship ?? 0 }},
                    ghi_chu: {!! json_encode($item->ghi_chu ?? '') !!},
                    inventory: {
                        barcode: {!! json_encode($item->inventory->barcode ?? '') !!},
                        location: {!! json_encode($item->inventory->location ?? '') !!},
                    },
                    isOverdue: {{ $item->isOverdue() ? 'true' : 'false' }},
                },
                @endforeach
            ],
            created_at: {!! json_encode($borrow->created_at->format('d/m/Y H:i')) !!},
        },
        @endforeach
    };
</script>
@endif

<style>
.detail-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease;
}

.detail-modal-overlay.active {
    display: flex;
}

.detail-modal {
    background: white;
    border-radius: 12px;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close-modal {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 32px;
    color: #666;
    cursor: pointer;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
    z-index: 10;
}

.close-modal:hover {
    background: #f0f0f0;
    color: #333;
}

.detail-modal-header {
    padding: 25px 30px;
    border-bottom: 2px solid #f0f0f0;
    position: sticky;
    top: 0;
    background: white;
    z-index: 5;
    border-radius: 12px 12px 0 0;
}

.detail-modal-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.detail-modal-body {
    padding: 30px;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-section-title {
    font-size: 18px;
    font-weight: 600;
    color: #d82329;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #d82329;
}

.detail-row {
    display: flex;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 150px;
    margin-right: 10px;
}

.detail-value {
    color: #333;
    flex: 1;
}

.detail-book-info {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.detail-book-image {
    flex-shrink: 0;
    width: 120px;
    height: 160px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.detail-book-info-text {
    flex: 1;
}

.detail-book-title {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.detail-book-author {
    color: #666;
    margin-bottom: 15px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-badge.pending {
    background-color: #ff9800;
    color: white;
}

.status-badge.dang-muon {
    background-color: #4caf50;
    color: white;
}

.status-badge.qua-han {
    background-color: #f44336;
    color: white;
}

.detail-items-list {
    margin-top: 15px;
}

.detail-item-card {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid #d82329;
}

.detail-item-card:last-child {
    margin-bottom: 0;
}

.text-danger {
    color: #f44336;
}

.text-success {
    color: #4caf50;
}

.detail-address {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
}

.btn-view-book {
    width: 100%;
    margin-top: 15px;
    padding: 10px;
    background-color: #d82329;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-view-book:hover {
    background-color: #b71c1c;
}

.btn-confirm-delivery {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-confirm-delivery:hover {
    background-color: #218838;
}

.btn-confirm-delivery:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.btn-reject-delivery {
    padding: 12px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-reject-delivery:hover {
    background-color: #c82333;
}


.btn-cancel {
    transition: background-color 0.3s;
}

.btn-cancel:hover {
    background-color: #5a6268;
}

.btn-submit-return {
    transition: background-color 0.3s;
}

.btn-submit-return:hover {
    background-color: #b71c1c;
}

.btn-return-book {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-return-book:hover {
    background-color: #218838;
}

.btn-extend-borrow {
    width: 100%;
    margin-top: 8px;
    padding: 10px;
    background-color: #0d6efd;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-extend-borrow:hover {
    background-color: #0b5ed7;
}
</style>

<script>

    // Hàm hiển thị chi tiết Borrow
    function showBorrowDetail(borrowId) {
        const borrow = borrowsData[borrowId];
        if (!borrow) return;

        const modal = document.getElementById('borrowDetailModal');
        const content = document.getElementById('borrowDetailContent');

        let html = `
            <div class="detail-section">
                <h3 class="detail-section-title">Thông tin phiếu mượn</h3>
                <div class="detail-row">
                    <span class="detail-label">Mã phiếu:</span>
                    <span class="detail-value">#${borrow.id}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày mượn:</span>
                    <span class="detail-value">${borrow.ngay_muon || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Trạng thái:</span>
                    <span class="detail-value">
                        ${borrow.trang_thai === 'Cho duyet' && borrow.trang_thai_chi_tiet === 'don_hang_moi' 
                            ? getStatusBadge('da_duyet') 
                            : getStatusBadge(borrow.trang_thai_chi_tiet)}
                    </span>
                </div>
                ${borrow.trang_thai_chi_tiet === 'cho_tra_sach' && borrow.ngay_yeu_cau_tra_sach ? `
                <div class="detail-row">
                    <span class="detail-label">Ngày yêu cầu trả:</span>
                    <span class="detail-value">${borrow.ngay_yeu_cau_tra_sach || 'N/A'}</span>
                </div>
                ` : ''}
                ${borrow.ghi_chu ? `
                <div class="detail-row">
                    <span class="detail-label">Ghi chú:</span>
                    <span class="detail-value">${borrow.ghi_chu}</span>
                </div>
                ` : ''}
                ${borrow.trang_thai_chi_tiet === 'giao_hang_that_bai' && borrow.failure_reason ? `
                <div class="detail-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #dc3545;">
                    <div style="width: 100%;">
                        <span class="detail-label" style="color: #dc3545; font-weight: 600; display: block; margin-bottom: 10px;">Lý do giao hàng thất bại:</span>
                        <div style="padding: 12px; background: ${borrow.failure_reason === 'loi_khach_hang' ? '#fff3cd' : '#d4edda'}; border-radius: 6px; border-left: 4px solid ${borrow.failure_reason === 'loi_khach_hang' ? '#ffc107' : '#28a745'};">
                            <strong style="color: ${borrow.failure_reason === 'loi_khach_hang' ? '#856404' : '#155724'};">
                                ${borrow.failure_reason_label}
                            </strong>
                            ${borrow.failure_reason === 'loi_khach_hang' ? `
                            <div style="margin-top: 8px; font-size: 0.9em; color: #856404;">
                                <p style="margin: 4px 0;">• Lý do: Đổi ý, không nghe máy, từ chối nhận hàng...</p>
                                <p style="margin: 4px 0;">• Hoàn: Phí thuê (100%)</p>
                            </div>
                            ` : `
                            <div style="margin-top: 8px; font-size: 0.9em; color: #155724;">
                                <p style="margin: 4px 0;">• Lý do: Sách rách, bẩn, sai tên sách, thiếu sách...</p>
                                <p style="margin: 4px 0;">• Hoàn: 100% phí thuê</p>
                                <p style="margin: 4px 0; font-weight: 600;">→ Khách được hoàn toàn bộ 100%</p>
                            </div>
                            `}
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">Thông tin người mượn</h3>
                <div class="detail-row">
                    <span class="detail-label">Họ tên:</span>
                    <span class="detail-value">${borrow.reader.ho_ten || borrow.ten_nguoi_muon || 'N/A'}</span>
                </div>
                ${borrow.reader.so_the_doc_gia ? `
                <div class="detail-row">
                    <span class="detail-label">Số thẻ độc giả:</span>
                    <span class="detail-value">${borrow.reader.so_the_doc_gia}</span>
                </div>
                ` : ''}
                ${borrow.reader.email ? `
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">${borrow.reader.email}</span>
                </div>
                ` : ''}
                ${(borrow.so_dien_thoai || borrow.reader.so_dien_thoai) ? `
                <div class="detail-row">
                    <span class="detail-label">Số điện thoại:</span>
                    <span class="detail-value">${borrow.so_dien_thoai || borrow.reader.so_dien_thoai}</span>
                </div>
                ` : ''}
                ${(borrow.so_nha || borrow.xa || borrow.huyen || borrow.tinh_thanh) ? `
                <div class="detail-row">
                    <span class="detail-label">Địa chỉ:</span>
                    <div class="detail-address">
                        ${borrow.so_nha ? `${borrow.so_nha}, ` : ''}
                        ${borrow.xa ? `${borrow.xa}, ` : ''}
                        ${borrow.huyen ? `${borrow.huyen}, ` : ''}
                        ${borrow.tinh_thanh || ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        // Hiển thị thông tin thủ thư nếu có
        if (borrow.librarian && borrow.librarian.name) {
            html += `
                <div class="detail-section">
                    <h3 class="detail-section-title">Thông tin xử lý</h3>
                    <div class="detail-row">
                        <span class="detail-label">Thủ thư:</span>
                        <span class="detail-value">${borrow.librarian.name}</span>
                    </div>
                </div>
            `;
        }

        // Hiển thị thông tin tài chính (đã bỏ tiền cọc/tiền ship)
        html += `
            <div class="detail-section">
                <h3 class="detail-section-title">Thông tin tài chính</h3>
                <div class="detail-row">
                    <span class="detail-label">Tiền thuê:</span>
                    <span class="detail-value">${new Intl.NumberFormat('vi-VN').format((function () {
                        // Dùng đúng tiền thuê của borrow, giống admin
                        return parseFloat(borrow.tien_thue) || 0;
                    })())} đ</span>
                </div>
                ${borrow.trang_thai_chi_tiet === 'giao_hang_that_bai' && borrow.failure_reason === 'loi_khach_hang' ? `
                <div class="detail-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ffc107;">
                    <div style="width: 100%;">
                        <div style="color: #dc3545; font-weight: 600; margin-bottom: 10px; font-size: 14px;">Chi tiết hoàn tiền (Lỗi khách hàng):</div>
                        <div style="padding: 12px; background: #fff3cd; border-radius: 6px; margin-bottom: 10px;">
                            ${(function() {
                                const tienCoc = borrow.tien_coc || 0;
                                const tienThue = borrow.tien_thue || 0;
                                let tienShip = borrow.tien_ship || 0;
                                
                                if (tienShip == 0 && borrow.borrowItems && borrow.borrowItems.length > 0) {
                                    tienShip = borrow.borrowItems.reduce((sum, item) => sum + (parseFloat(item.tien_ship) || 0), 0);
                                }
                                // Không dùng phí ship ở luồng bạn đọc
                                tienShip = 0;
                                
                                const phiPhat = tienCoc * 0.20;
                                const tienCocHoan = tienCoc * 0.80;
                                const tongTienKhachMat = phiPhat + tienShip;
                                const tongTienHoan = tienThue + tienCocHoan;
                                const tongTienGoc = tienCoc + tienThue + tienShip;
                                const tongTienCuoi = tongTienGoc - tongTienKhachMat;
                                
                                return `
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn phí thuê:</span>
                                        <span style="float: right; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tienThue)} đ</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn tiền cọc (80%):</span>
                                        <span style="float: right; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tienCocHoan)} đ</span>
                                    </div>
                                    <div style="margin-bottom: 8px; color: #dc3545;">
                                        <span>✗ Trừ phí phạt (20% cọc):</span>
                                        <span style="float: right; font-weight: 600;">- ${new Intl.NumberFormat('vi-VN').format(phiPhat)} đ</span>
                                    </div>
                                    <div style="margin-bottom: 8px; color: #dc3545;">
                                        <span>✗ Không hoàn phí ship:</span>
                                        <span style="float: right; font-weight: 600;">- ${new Intl.NumberFormat('vi-VN').format(tienShip)} đ</span>
                                    </div>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                                        <span style="font-weight: 600;">Tổng khách mất:</span>
                                        <span style="float: right; color: #dc3545; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tongTienKhachMat)} đ</span>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <span style="font-weight: 600;">Tổng hoàn lại:</span>
                                        <span style="float: right; color: #28a745; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tongTienHoan)} đ</span>
                                    </div>
                                `;
                            })()}
                        </div>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef;">
                            <div style="margin-bottom: 5px;">
                                <span style="text-decoration: line-through; color: #999;">Tổng tiền ban đầu:</span>
                                <span style="text-decoration: line-through; color: #999; float: right;">${new Intl.NumberFormat('vi-VN').format((function() {
                                    const tienCoc = borrow.tien_coc || 0;
                                    const tienThue = borrow.tien_thue || 0;
                                    let tienShip = borrow.tien_ship || 0;
                                    if (tienShip == 0 && borrow.borrowItems && borrow.borrowItems.length > 0) {
                                        tienShip = borrow.borrowItems.reduce((sum, item) => sum + (parseFloat(item.tien_ship) || 0), 0);
                                    }
                                    // Không dùng phí ship ở luồng bạn đọc
                                    tienShip = 0;
                                    return tienCoc + tienThue + tienShip;
                                })())} đ</span>
                            </div>
                            <div>
                                <span style="font-weight: 600; color: #dc3545;">Tổng tiền sau khi trừ:</span>
                                <span style="font-weight: 600; color: #dc3545; float: right;">${new Intl.NumberFormat('vi-VN').format((function() {
                                    const tienCoc = borrow.tien_coc || 0;
                                    const tienThue = borrow.tien_thue || 0;
                                    let tienShip = borrow.tien_ship || 0;
                                    if (tienShip == 0 && borrow.borrowItems && borrow.borrowItems.length > 0) {
                                        tienShip = borrow.borrowItems.reduce((sum, item) => sum + (parseFloat(item.tien_ship) || 0), 0);
                                    }
                                    // Không dùng phí ship ở luồng bạn đọc
                                    tienShip = 0;
                                    const phiPhat = tienCoc * 0.20;
                                    const tongTienGoc = tienCoc + tienThue + tienShip;
                                    return tongTienGoc - phiPhat - tienShip;
                                })())} đ</span>
                            </div>
                        </div>
                        ${borrow.failure_proof_image ? `
                        <div style="margin-top: 10px;">
                            <span class="detail-label" style="display: block; margin-bottom: 6px;">Ảnh minh chứng:</span>
                            <img src="${borrow.failure_proof_image}" alt="Ảnh minh chứng giao hàng thất bại" style="max-width: 240px; border-radius: 6px; border: 1px solid #ddd;">
                </div>
                ` : ''}
                    </div>
                </div>
                ` : ''}
                ${borrow.trang_thai_chi_tiet === 'giao_hang_that_bai' && borrow.failure_reason === 'loi_thu_vien' ? `
                <div class="detail-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #28a745;">
                    <div style="width: 100%;">
                        <div style="color: #28a745; font-weight: 600; margin-bottom: 10px; font-size: 14px;">Chi tiết hoàn tiền (Lỗi thư viện):</div>
                        <div style="padding: 12px; background: #d4edda; border-radius: 6px;">
                            ${(function() {
                                const tienCoc = borrow.tien_coc || 0;
                                const tienThue = borrow.tien_thue || 0;
                                let tienShip = borrow.tien_ship || 0;
                                
                                if (tienShip == 0 && borrow.borrowItems && borrow.borrowItems.length > 0) {
                                    tienShip = borrow.borrowItems.reduce((sum, item) => sum + (parseFloat(item.tien_ship) || 0), 0);
                                }
                                // Không dùng phí ship ở luồng bạn đọc
                                tienShip = 0;
                                
                                const tongTienHoan = tienCoc + tienThue + tienShip;
                                
                                return `
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% phí thuê:</span>
                                        <span style="float: right; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tienThue)} đ</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% tiền cọc:</span>
                                        <span style="float: right; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tienCoc)} đ</span>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="color: #28a745;">✓ Hoàn 100% phí ship:</span>
                                        <span style="float: right; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tienShip)} đ</span>
                                    </div>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e0e0e0;">
                                        <span style="font-weight: 600;">Tổng hoàn lại:</span>
                                        <span style="float: right; color: #28a745; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(tongTienHoan)} đ</span>
                                    </div>
                                `;
                            })()}
                        </div>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e9ecef;">
                            <span style="font-weight: 600; color: #28a745;">Tổng tiền hoàn lại:</span>
                            <span style="font-weight: 600; color: #28a745; float: right;">${new Intl.NumberFormat('vi-VN').format((function() {
                                const tienCoc = borrow.tien_coc || 0;
                                const tienThue = borrow.tien_thue || 0;
                                let tienShip = borrow.tien_ship || 0;
                                if (tienShip == 0 && borrow.borrowItems && borrow.borrowItems.length > 0) {
                                    tienShip = borrow.borrowItems.reduce((sum, item) => sum + (parseFloat(item.tien_ship) || 0), 0);
                                }
                                // Không dùng phí ship ở luồng bạn đọc
                                tienShip = 0;
                                return tienCoc + tienThue + tienShip;
                            })())} đ</span>
                        </div>
                    </div>
                </div>
                ` : ''}
                ${borrow.trang_thai_chi_tiet !== 'giao_hang_that_bai' ? `
                <div class="detail-row">
                    <span class="detail-label">Tổng tiền:</span>
                    <span class="detail-value" style="font-weight: 600; color: #d82329;">${new Intl.NumberFormat('vi-VN').format((function() {
                        // Tổng tiền = tiền thuê (giống admin, không cọc/ship)
                        return parseFloat(borrow.tien_thue) || 0;
                    })())} đ</span>
                </div>
            </div>
        `;

        // Hiển thị danh sách sách mượn
        if (borrow.borrowItems && borrow.borrowItems.length > 0) {
            html += `
                <div class="detail-section">
                    <h3 class="detail-section-title">Danh sách sách mượn (${borrow.borrowItems.length})</h3>
                    <div class="detail-items-list">
            `;

            borrow.borrowItems.forEach((item, index) => {
                const isOverdue = item.isOverdue;
                html += `
                    <div class="detail-item-card">
                        <div class="detail-book-info">
                            ${item.book.hinh_anh ? 
                                `<img src="${item.book.hinh_anh}" alt="${item.book.ten_sach}" class="detail-book-image">` : 
                                '<div class="detail-book-image" style="background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 48px;">📖</div>'
                            }
                            <div class="detail-book-info-text">
                                <h4 class="detail-book-title">${item.book.ten_sach}</h4>
                                <p class="detail-book-author">Tác giả: ${item.book.tac_gia || 'N/A'}</p>
                                ${item.book.isbn ? `<p><strong>ISBN:</strong> ${item.book.isbn}</p>` : ''}
                                ${item.inventory && item.inventory.barcode ? `<p><strong>Mã vạch:</strong> ${item.inventory.barcode}</p>` : ''}
                                ${item.inventory && item.inventory.location ? `<p><strong>Vị trí:</strong> ${item.inventory.location}</p>` : ''}
                            </div>
                        </div>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                            <div class="detail-row">
                                <span class="detail-label">Trạng thái:</span>
                                <span class="detail-value">
                                    <span class="status-badge ${isOverdue ? 'qua-han' : 'dang-muon'}">${item.trang_thai}</span>
                                    ${isOverdue ? ' <span class="text-danger">(Quá hạn)</span>' : ''}
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Ngày mượn:</span>
                                <span class="detail-value">${item.ngay_muon || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Hạn trả:</span>
                                <span class="detail-value ${isOverdue ? 'text-danger' : ''}">${item.ngay_hen_tra || 'N/A'}</span>
                            </div>
                            ${item.ngay_tra_thuc_te ? `
                            <div class="detail-row">
                                <span class="detail-label">Ngày trả thực tế:</span>
                                <span class="detail-value text-success">${item.ngay_tra_thuc_te}</span>
                            </div>
                            ` : ''}
                            ${item.so_lan_gia_han > 0 ? `
                            <div class="detail-row">
                                <span class="detail-label">Số lần gia hạn:</span>
                                <span class="detail-value">${item.so_lan_gia_han}/2</span>
                            </div>
                            ` : ''}
                            ${item.ngay_gia_han_cuoi ? `
                            <div class="detail-row">
                                <span class="detail-label">Ngày gia hạn cuối:</span>
                                <span class="detail-value">${item.ngay_gia_han_cuoi}</span>
                            </div>
                            ` : ''}
                            <div class="detail-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e0e0e0;">
                                <span class="detail-label">Chi phí:</span>
                                <div style="flex: 1;">
                                    <p style="margin: 5px 0;">Thuê: ${new Intl.NumberFormat('vi-VN').format(item.tien_thue || 0)} đ</p>
                                </div>
                            </div>
                            ${item.ghi_chu ? `
                            <div class="detail-row" style="margin-top: 10px;">
                                <span class="detail-label">Ghi chú:</span>
                                <span class="detail-value">${item.ghi_chu}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        content.innerHTML = html;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeBorrowDetailModal(event) {
        event.preventDefault();
        event.stopPropagation();
        const modal = document.getElementById('borrowDetailModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Hàm tạo status badge
    function getStatusBadge(status) {
        const statusConfig = {
            'da_duyet': { label: 'Đã được duyệt', color: '#28a745' },
            'don_hang_moi': { label: 'Đơn hàng Mới', color: '#17a2b8' },
            'dang_chuan_bi_sach': { label: 'Đang Chuẩn bị Sách', color: '#ffc107' },
            'cho_ban_giao_van_chuyen': { label: 'Chờ Bàn giao Vận chuyển', color: '#17a2b8' },
            'dang_giao_hang': { label: 'Đang Giao hàng', color: '#007bff' },
            'giao_hang_thanh_cong': { label: 'Giao hàng Thành công', color: '#ffc107' },
            'giao_hang_that_bai': { label: 'Giao hàng Thất bại', color: '#dc3545' },
            'dang_van_chuyen_tra_ve': { label: 'Vận chuyển trả về', color: '#055160' },
            'da_nhan_va_kiem_tra': { label: 'Đã nhận & Kiểm tra', color: '#664d03' },
            'hoan_tat_don_hang': { label: 'Đã hoàn tiền', color: '#155724' },
            'da_muon_dang_luu_hanh': { label: 'Đã Mượn (Đang Lưu hành)', color: '#007bff' },
            'cho_tra_sach': { label: 'Chờ Trả sách', color: '#ffc107' },
        };
        
        const config = statusConfig[status] || { label: status, color: '#6c757d' };
        return `<span class="status-badge" style="
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background-color: ${config.color};
            color: white;
        ">${config.label}</span>`;
    }


    // Hàm hiển thị modal hoàn trả sách
    function showReturnBookModal(borrowId) {
        // Validate trước khi hiển thị modal
        if (window.borrowStatusFlow) {
            const borrow = borrowsData[borrowId];
            if (borrow) {
                const validation = window.borrowStatusFlow.validateAction(
                    borrowId,
                    borrow.trang_thai_chi_tiet,
                    'return-book'
                );
                
                if (!validation.valid) {
                    window.borrowStatusFlow.showError(validation.message);
                    return;
                }

                // Nếu trả sách sớm (chưa đến hạn), hỏi xác nhận và thông báo hoàn 30% phí thuê
                if (borrow.ngay_hen_tra_raw) {
                    const dueDate = new Date(borrow.ngay_hen_tra_raw + 'T23:59:59');
                    const today = new Date();
                    if (today < dueDate) {
                        const refundAmount = Math.round((borrow.tien_thue || 0) * 0.3);
                        const confirmEarly = confirm(
                            `Bạn đang trả sách sớm trước hạn (${borrow.ngay_hen_tra || borrow.ngay_hen_tra_raw}). ` +
                            `Bạn sẽ được hoàn 30% phí thuê (~${new Intl.NumberFormat('vi-VN').format(refundAmount)} đ).\\n` +
                            `Bạn có chắc chắn muốn trả sách sớm không?`
                        );
                        if (!confirmEarly) {
                            return;
                        }
                    }
                }
            }
        }
        
        const modal = document.getElementById('returnBookModal');
        const form = document.getElementById('returnBookForm');
        
        // Set action URL
        form.action = `/account/borrows/${borrowId}/return-book`;
        
        // Reset form
        form.reset();
        
        // Reset image preview
        const previewDiv = document.getElementById('returnImagePreview');
        const previewImg = document.getElementById('returnImagePreviewImg');
        if (previewDiv) previewDiv.style.display = 'none';
        if (previewImg) previewImg.src = '';
        
        // Hiển thị modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeReturnBookModal(event) {
        event.preventDefault();
        event.stopPropagation();
        const modal = document.getElementById('returnBookModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        // Reset image preview
        const previewDiv = document.getElementById('returnImagePreview');
        const previewImg = document.getElementById('returnImagePreviewImg');
        if (previewDiv) previewDiv.style.display = 'none';
        if (previewImg) previewImg.src = '';
    }

    // Hàm preview ảnh hoàn trả
    // Hiển thị/ẩn form hư hỏng chi tiết khi chọn tình trạng sách
    const tinhTrangSachSelect = document.getElementById('tinh_trang_sach_return');
    const damageDetailsSection = document.getElementById('damage-details-section');
    
    if (tinhTrangSachSelect && damageDetailsSection) {
        tinhTrangSachSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue === 'hong_nhe' || selectedValue === 'hong_nang' || selectedValue === 'mat_sach') {
                damageDetailsSection.style.display = 'block';
            } else {
                damageDetailsSection.style.display = 'none';
            }
        });
    }

    function previewReturnImages(input) {
        const previewDiv = document.getElementById('returnImagePreview');
        previewDiv.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.style.width = '100px';
                    imgContainer.style.height = '100px';
                    imgContainer.style.overflow = 'hidden';
                    imgContainer.style.borderRadius = '4px';
                    imgContainer.style.border = '1px solid #ddd';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    
                    imgContainer.appendChild(img);
                    previewDiv.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            });
            previewDiv.style.display = 'flex';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    // Hàm hiển thị modal từ chối nhận sách
    function showRejectDeliveryModal(borrowId) {
        const modal = document.getElementById('rejectDeliveryModal');
        const form = document.getElementById('rejectDeliveryForm');
        
        // Set action URL
        form.action = `/account/borrows/${borrowId}/reject-delivery`;
        
        // Reset form
        form.reset();
        
        // Hiển thị modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeRejectDeliveryModal(event) {
        event.preventDefault();
        event.stopPropagation();
        const modal = document.getElementById('rejectDeliveryModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Đóng modal khi nhấn ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeBorrowDetailModal(event);
            closeReturnBookModal(event);
            closeRejectDeliveryModal(event);
        }
    });
</script>
@endsection

