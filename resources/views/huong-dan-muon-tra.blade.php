@extends('layouts.app')

@section('title', 'Hướng dẫn mượn/trả sách - Thư Viện LibNet')

@push('styles')
<style>
    .guide-page {
        background: var(--background-color);
        min-height: 100vh;
        padding-bottom: 100px;
    }
    .guide-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 80px 20px;
    }
    .guide-hero {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #fff;
        border-radius: 24px;
        padding: 36px;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 24px;
        align-items: center;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.25);
    }
    .guide-hero h1 {
        font-size: 32px;
        margin: 0 0 12px;
        font-weight: 800;
    }
    .guide-hero p {
        margin: 0 0 12px;
        line-height: 1.6;
        color: #e2e8f0;
    }
    .guide-tags {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .guide-tag {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #e2e8f0;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .guide-steps {
        margin-top: 32px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }
    .guide-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        border: 1px solid #e2e8f0;
    }
    .guide-card h3 {
        margin: 0 0 8px;
        font-size: 17px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
    }
    .guide-card p {
        margin: 0;
        color: #475569;
        line-height: 1.55;
    }
    .guide-list {
        margin: 16px 0 0;
        padding-left: 18px;
        color: #475569;
        line-height: 1.55;
    }
    .guide-section-title {
        margin: 32px 0 12px;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }
    .guide-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #ecfeff;
        color: #0ea5e9;
        border-radius: 12px;
        font-weight: 600;
        border: 1px solid #bae6fd;
        margin-top: 16px;
    }
</style>
@endpush

@section('content')
<div class="guide-page">
    <div class="guide-container">
        <div class="guide-hero">
            <div>
                <h1>Hướng dẫn mượn & trả sách</h1>
                <p>4 bước đơn giản: tìm sách → thêm vào giỏ mượn → gửi yêu cầu → nhận sách và trả sách đúng hạn.</p>
                <div class="guide-tags">
                    <span class="guide-tag">🕒 Thời gian xử lý: 5-15 phút</span>
                    <span class="guide-tag">🚚 Giao/nhận tại chỗ hoặc vận chuyển</span>
                    <span class="guide-tag">💳 COD / Chuyển khoản / VNPay</span>
                </div>
            </div>
            <div class="guide-badge">
                📚 Gợi ý: Đăng nhập trước để lưu giỏ mượn và theo dõi trạng thái dễ dàng
            </div>
        </div>

        <h2 class="guide-section-title">Quy trình mượn sách</h2>
        <div class="guide-steps">
            <div class="guide-card">
                <h3>1) Tìm & chọn sách</h3>
                <p>- Tìm theo tên sách/tác giả hoặc vào danh mục.<br>- Kiểm tra số lượng còn trong kho.</p>
            </div>
            <div class="guide-card">
                <h3>2) Thêm vào giỏ mượn</h3>
                <p>- Chọn số lượng muốn mượn.<br>- Kiểm tra phí thuê/đặt cọc hiển thị (nếu có).</p>
            </div>
            <div class="guide-card">
                <h3>3) Gửi yêu cầu</h3>
                <p>- Điền thông tin nhận sách (địa chỉ/số điện thoại).<br>- Chọn phương thức thanh toán.</p>
            </div>
            <div class="guide-card">
                <h3>4) Nhận & trả sách</h3>
                <p>- Nhận sách tại quầy hoặc giao tận nơi.<br>- Trả sách đúng hạn để tránh phí trễ/hư hỏng.</p>
            </div>
        </div>

        <h2 class="guide-section-title">Trả sách & phí liên quan</h2>
        <div class="guide-steps">
            <div class="guide-card">
                <h3>Thời hạn & gia hạn</h3>
                <p>- Xem ngày trả trong đơn mượn.<br>- Liên hệ thư viện nếu cần gia hạn (tùy chính sách).</p>
            </div>
            <div class="guide-card">
                <h3>Trả sách</h3>
                <p>- Trả trực tiếp tại quầy hoặc gửi lại qua đơn vị vận chuyển (nếu được hỗ trợ).<br>- Kiểm tra tình trạng sách trước khi trả.</p>
            </div>
            <div class="guide-card">
                <h3>Phí trễ hạn / hư hỏng</h3>
                <p>- Trễ hạn: tính theo ngày (xem mục chính sách giá nếu áp dụng).<br>- Hư hỏng/mất sách: bồi thường theo giá sách hoặc thỏa thuận của thư viện.</p>
            </div>
        </div>

        <h2 class="guide-section-title">Mẹo sử dụng nhanh</h2>
        <ul class="guide-list">
            <li>Đăng nhập để lưu giỏ mượn và theo dõi trạng thái đơn.</li>
            <li>Kiểm tra số lượng còn trong kho và tình trạng sách trước khi đặt.</li>
            <li>Chọn phương thức nhận sách phù hợp (nhận tại chỗ / giao tận nơi).</li>
            <li>Trả sách đúng hạn, bảo quản sách tránh ẩm/mốc/rách.</li>
            <li>Nếu cần hỗ trợ, liên hệ Thủ thư hoặc hotline hiển thị trên trang.</li>
        </ul>
    </div>
</div>
@endsection

