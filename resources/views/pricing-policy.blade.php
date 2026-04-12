@extends('layouts.app')

@section('title', 'Chính sách giá - Thư viện LibNet')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<style>
    :root {
        --policy-navy: #0f172a;
        --policy-teal: #0f766e;
        --policy-orange: #c2410c;
        --policy-mint: #ecfdf5;
        --policy-cream: #fffdf5;
        --policy-border: #d9e3da;
        --policy-text: #1e293b;
        --policy-muted: #64748b;
    }

    .policy-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 10% 10%, rgba(15, 118, 110, 0.12), transparent 35%),
            radial-gradient(circle at 90% 15%, rgba(194, 65, 12, 0.12), transparent 38%),
            linear-gradient(180deg, #f8fbfa 0%, #eff4f1 100%);
        padding: 28px 0 90px;
    }

    .policy-container {
        max-width: 1120px;
        margin: 0 auto;
        padding: 0 18px;
    }

    .policy-hero {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 34px;
        background: linear-gradient(145deg, #0f172a 0%, #1f2937 45%, #0f766e 100%);
        color: #f8fafc;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        margin-bottom: 22px;
    }

    .policy-hero::after {
        content: '';
        position: absolute;
        right: -70px;
        top: -70px;
        width: 210px;
        height: 210px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.24), transparent 65%);
        pointer-events: none;
    }

    .policy-hero h1 {
        margin: 0 0 10px;
        font-size: 34px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -0.02em;
        max-width: 760px;
    }

    .policy-hero p {
        margin: 0;
        max-width: 760px;
        color: #dbeafe;
        line-height: 1.7;
        font-size: 15px;
    }

    .policy-badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .policy-badge {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #f8fafc;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .policy-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .policy-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid var(--policy-border);
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(6px);
    }

    .policy-card.full {
        grid-column: 1 / -1;
    }

    .policy-card h3 {
        margin: 0 0 10px;
        font-size: 19px;
        line-height: 1.3;
        color: var(--policy-text);
        font-weight: 800;
    }

    .policy-list {
        margin: 0;
        padding-left: 18px;
        color: #334155;
        line-height: 1.72;
        font-size: 14px;
    }

    .policy-note {
        margin-top: 16px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--policy-mint), var(--policy-cream));
        border: 1px solid #a7f3d0;
        color: #14532d;
        padding: 14px 16px;
        font-size: 14px;
        line-height: 1.7;
    }

    .policy-note strong {
        color: #166534;
    }

    @media (max-width: 900px) {
        .policy-hero {
            padding: 26px 20px;
            border-radius: 22px;
        }

        .policy-hero h1 {
            font-size: 27px;
        }

        .policy-grid {
            grid-template-columns: 1fr;
        }

        .policy-card.full {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
@include('components.frontend-header')

<div class="policy-page">
    <div class="policy-container">
        <div class="policy-hero">
            <h1>Chính sách giá và điều khoản mượn sách</h1>
            <p>Biểu phí được áp dụng minh bạch trong toàn bộ quá trình đặt mượn, nhận và trả sách.</p>
            <div class="policy-badges">
                <span class="policy-badge">Phí thuê cố định</span>
                <span class="policy-badge">Không áp dụng vận chuyển</span>
                <span class="policy-badge">Trả trễ tính theo ngày</span>
            </div>
        </div>

        <div class="policy-grid">
            <article class="policy-card">
                <h3>1. Phí thuê và tiền cọc</h3>
                <ul class="policy-list">
                    <li>Phí thuê: 5.000 VND/ngày/cuốn.</li>
                    <li>Tiền cọc: 100% giá trị sách.</li>
                </ul>
            </article>

            <article class="policy-card">
                <h3>2. Phí vận chuyển</h3>
                <ul class="policy-list">
                    <li>Không áp dụng.</li>
                    <li>Người dùng nhận sách trực tiếp tại quầy.</li>
                </ul>
            </article>

            <article class="policy-card">
                <h3>3. Trả trễ hạn</h3>
                <ul class="policy-list">
                    <li>Phí trễ hạn: 5.000 VND/ngày/cuốn.</li>
                    <li>Trả trễ nhiều lần có thể bị hạn chế hoặc khóa tài khoản.</li>
                </ul>
            </article>

            <article class="policy-card">
                <h3>4. Hư hỏng và mất sách</h3>
                <ul class="policy-list">
                    <li>Sách quý: bồi thường tối đa 100% giá trị sách.</li>
                    <li>Sách thường: bồi thường theo tình trạng thực tế.</li>
                    <li>Mức bồi thường do thư viện xác nhận tại thời điểm xử lý.</li>
                </ul>
            </article>

            <article class="policy-card full">
                <h3>5. Điều khoản chung</h3>
                <ul class="policy-list">
                    <li>Người dùng cần đến nhận sách tại thư viện theo thời gian đã đặt.</li>
                    <li>Khi gửi yêu cầu mượn sách, người dùng đồng ý tuân thủ các quy định của hệ thống.</li>
                    <li>Chính sách có thể được cập nhật theo từng thời kỳ.</li>
                    <li>Nếu cần hỗ trợ, vui lòng liên hệ bộ phận thủ thư.</li>
                </ul>
            </article>
        </div>

        <div class="policy-note">
            <strong>Lưu ý:</strong> Chính sách và biểu phí được công bố công khai để đảm bảo minh bạch trong quá trình sử dụng dịch vụ.
        </div>
    </div>
</div>
@endsection
