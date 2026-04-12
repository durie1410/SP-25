@extends('layouts.app')

@section('title', 'Hướng dẫn mượn/trả sách - Thuê Sách LibNet')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
@include('components.frontend-header')

<div class="guide-page">
    <div class="guide-container">
        <div class="guide-hero">
            <div>
                <h1>Hướng dẫn mượn & trả sách</h1>
                <p>Quy trình rõ ràng, thao tác nhanh và minh bạch để bạn đặt mượn, nhận sách và trả sách đúng quy định.</p>
                <div class="guide-tags">
                    <span class="guide-tag">🕒 Giờ đặt sách: 08:00 - 18:00</span>
                    <span class="guide-tag">🏢 Giờ nhận sách: 08:00 - 20:00</span>
                </div>
            </div>
            <div class="guide-badge">
                📚 Vui lòng đọc kỹ quy định trước khi gửi yêu cầu để tránh vi phạm không cần thiết.
            </div>
        </div>

        <h2 class="guide-section-title">1. Quy định mượn sách</h2>
        <div class="guide-steps" style="margin-top: 16px;">
            <div class="guide-card">
                <h3>Thời gian áp dụng</h3>
                <p>- Giờ đặt sách: 08:00 - 18:00.<br>- Giờ nhận sách tại quầy: 08:00 - 20:00 (giờ đóng cửa).</p>
            </div>
            <div class="guide-card">
                <h3>Yêu cầu nhận sách đúng hẹn</h3>
                <p>- Người dùng cần đến nhận sách đúng thời gian đã đặt.<br>- Được phép đến trễ tối đa 2 giờ, nhưng không vượt quá 20:00.<br>- Quá thời gian trên, đơn tự động hủy và được tính là vi phạm.</p>
            </div>
        </div>

        <h2 class="guide-section-title">2. Đặt trước & hàng chờ</h2>
        <div class="guide-steps" style="margin-top: 16px;">
            <div class="guide-card">
                <h3>Khi sách tạm hết</h3>
                <p>Nếu sách không còn sẵn, yêu cầu của bạn sẽ được đưa vào hàng chờ theo thứ tự.</p>
            </div>
            <div class="guide-card">
                <h3>Khi có sách trở lại</h3>
                <p>Hệ thống giữ sách trong một khoảng thời gian theo quy định. Nếu bạn không đến nhận, quyền nhận sẽ chuyển cho người kế tiếp.</p>
            </div>
        </div>

        <h2 class="guide-section-title">3. Quy trình mượn sách</h2>
        <div class="guide-steps">
            <div class="guide-card">
                <h3>Bước 1: Tìm & chọn sách</h3>
                <p>Tìm theo tên sách, tác giả hoặc danh mục và kiểm tra tình trạng còn sách trước khi đặt.</p>
            </div>
            <div class="guide-card">
                <h3>Bước 2: Thêm vào giỏ</h3>
                <p>Chọn sách cần mượn, thêm vào giỏ đặt trước và kiểm tra lại thông tin đơn.</p>
            </div>
            <div class="guide-card">
                <h3>Bước 3: Gửi yêu cầu</h3>
                <p>Điền đầy đủ thông tin cần thiết, xác nhận quy định mượn trả và gửi yêu cầu.</p>
            </div>
            <div class="guide-card">
                <h3>Bước 4: Nhận & trả sách</h3>
                <p>Nhận sách tại quầy theo lịch hẹn và trả sách đúng hạn theo thông tin đơn mượn.</p>
            </div>
        </div>

        <h2 class="guide-section-title">4. Trả sách & phí</h2>
        <div class="guide-steps">
            <div class="guide-card">
                <h3>Hình thức trả sách</h3>
                <p>Bạn có thể trả trực tiếp tại quầy hoặc qua vận chuyển (nếu hệ thống đang hỗ trợ).</p>
            </div>
            <div class="guide-card">
                <h3>Phí trễ hạn</h3>
                <p>Trả sách quá hạn sẽ phát sinh phí theo số ngày trễ theo chính sách hiện hành của thư viện.</p>
            </div>
            <div class="guide-card">
                <h3>Hư hỏng hoặc mất sách</h3>
                <p>Người dùng có trách nhiệm bồi thường theo giá trị sách và quy định bồi thường của thư viện.</p>
            </div>
        </div>

        <h2 class="guide-section-title">5. Quy định vi phạm</h2>
        <ul class="guide-list">
            <li>Không đến nhận sách và không hủy đơn đúng cách sẽ bị ghi nhận là vi phạm.</li>
            <li>Vi phạm lặp lại nhiều lần có thể dẫn đến giới hạn quyền mượn hoặc khóa tài khoản theo chính sách hệ thống.</li>
        </ul>

        <h2 class="guide-section-title">6. Mẹo sử dụng</h2>
        <ul class="guide-list">
            <li>Đăng nhập để lưu giỏ mượn và theo dõi trạng thái đơn.</li>
            <li>Kiểm tra số lượng còn trong kho và tình trạng sách trước khi đặt.</li>
            <li>Đến nhận sách đúng hẹn để tránh bị hủy đơn và phát sinh vi phạm.</li>
            <li>Trả sách đúng hạn, bảo quản sách cẩn thận để không phát sinh phí.</li>
        </ul>
        
    </div>

</div>
@endsection

