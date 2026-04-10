@extends('layouts.app')

@section('title', 'Chính sách giá - Thư Viện Online')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .pricing-policy-page {
            padding-bottom: 100px;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .pricing-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Hero Section */
        .hero-section {
            padding: 100px 0 140px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            text-align: center;
            margin-bottom: -80px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--background-color);
            clip-path: polygon(0 100%, 100% 100%, 100% 0);
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -0.025em;
        }

        .hero-section p {
            font-size: 1.25rem;
            color: #94a3b8;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Pillars Section */
        .pillars-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            position: relative;
            z-index: 10;
        }

        .pillar-card {
            background: white;
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .pillar-card:hover { transform: translateY(-8px); }

        .pillar-icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 24px;
            background: #eff6ff;
            color: var(--primary-color);
        }

        .pillar-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-color);
        }

        .pillar-content {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 24px;
            flex-grow: 1;
            font-size: 1.05rem;
        }

        .pillar-formula {
            background: #f1f5f9;
            padding: 16px;
            border-radius: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            border-left: 4px solid var(--primary-color);
        }

        /* Policy Sections */
        .policy-group {
            margin-top: 80px;
        }

        .group-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 40px;
            text-align: center;
            color: var(--text-color);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
        }

        .group-title::before, .group-title::after {
            content: "";
            height: 2px;
            background: #e2e8f0;
            flex-grow: 1;
        }

        .policy-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
        }

        .policy-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .policy-card-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .policy-card-header i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .policy-card-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Example Box Refinement */
        .example-block {
            background: #fffbeb;
            border-left: 4px solid #eab308;
            padding: 32px;
            border-radius: 16px;
            margin-top: 40px;
            border: 1px solid #fef9c3;
        }

        .example-block h4 {
            color: #854d0e;
            margin-bottom: 16px;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .example-block p {
            color: #713f12;
            margin: 8px 0;
            font-size: 1.1rem;
        }

        /* General Rules */
        .rules-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .rule-item {
            display: flex;
            gap: 20px;
            padding: 24px;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .rule-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .rule-item i { 
            color: var(--primary-color); 
            font-size: 1.5rem; 
            margin-top: 2px;
            background: #eff6ff;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            flex-shrink: 0;
        }

        .rule-item strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .rule-item span {
            color: var(--text-muted);
            line-height: 1.6;
            font-size: 1rem;
        }

        @media (max-width: 1024px) {
            .pillars-grid { grid-template-columns: 1fr; }
            .policy-grid { grid-template-columns: 1fr; }
            .rules-grid { grid-template-columns: 1fr; }
            .hero-section h1 { font-size: 2.5rem; }
            .hero-section { padding: 80px 0 120px; }
        }
    </style>
@endpush

@section('content')
    @include('components.frontend-header')

    <div class="pricing-policy-page">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="pricing-container">
                <h1>Chính sách giá minh bạch</h1>
                <p>Chúng tôi cung cấp hệ thống tính phí công bằng, giúp bạn dễ dàng tiếp cận kho tri thức với chi phí thấp nhất.</p>
            </div>
        </div>

        <div class="pricing-container">
            <!-- The Three Pillars -->
            <div class="pillars-grid">
                <!-- Phí thuê -->
                <div class="pillar-card">
                    <div class="pillar-icon"><i class="fas fa-book-reader"></i></div>
                    <h3>Phí thuê sách</h3>
                    <div class="pillar-content">
                        {{ $pricing['rental']['description_detail'] ?? 'Phí thuê sách được tính theo số ngày mượn. Áp dụng cho mỗi ngày là 1% giá trị sách.' }}
                    </div>
                    <div class="pillar-formula">
                        Phí = 5.000 VNĐ × số ngày × số sách
                    </div>
                </div>

                <!-- Tiền cọc -->
                <div class="pillar-card">
                    <div class="pillar-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>giờ mượn sách</h3>
                    <div class="pillar-content">
thư viện mở cửa từ 8h -> 20h . yêu cầu đặt sách trước 2 tiếng . quý khách vui lòng tuân thủ giờ mượn sách để đảm bảo trải nghiệm tốt nhất.
                    </div>
               
                </div>

                <!-- Vận chuyển -->
                <div class="pillar-card">
                    <div class="pillar-icon"><i class="fas fa-truck"></i></div>
                    <h3>trả sách</h3>
                    <div class="pillar-content">
yêu cầu trả sách đúng hạn để tránh phát sinh phí trễ. mong quý khách vui lòng tuân thủ thời gian trả sách. vui lòng bảo quản sách cẩn thận và trả lại trong tình trạng tốt để tránh phí bồi thường. cảm ơn quý khách đã hợp tác!
                    </div>
                    <div class="pillar-formula">
                        Phí trả muộn : 1 ngày = 5.000 VNĐ/đơn
                    </div>
                </div>
            </div>

            <!-- Risks & Protection Policies -->
            <div class="policy-group">
                <div class="group-title"><i class="fas fa-exclamation-circle"></i> Quy định & Xử lý vi phạm</div>
                
                <div class="policy-grid">
                    <!-- Trả muộn -->
                    <div class="policy-card">
                        <div class="policy-card-header">
                            <i class="fas fa-clock"></i>
                            <h4>⛔ Phí trả muộn</h4>
                        </div>
                        <div class="pillar-content">
                            Tính từ ngày quá hạn đầu tiên. Vui lòng gia hạn trước 24h nếu bạn cần thêm thời gian để tránh phát sinh phí.
                        </div>
                        <div class="pillar-formula">
                            ⛔ Trả trễ: số ngày muộn × 5.000 VNĐ/cuốn
                        </div>
                    </div>

                    <!-- Trả sớm -->
                  
                
               

                    <!-- Làm hỏng/Mất -->
                    <div class="policy-card">
                        <div class="policy-card-header">
                            <i class="fas fa-book-dead"></i>
                            <h4>Sách hỏng hoặc mất</h4>
                        </div>
                        <div class="pillar-content">
                            Bồi thường dựa trên loại sách và tình trạng thực tế.
                        </div>
                        <div class="pillar-formula">
                            Bồi thường: 40% - 80% giá trị sách
                        </div>
                    </div>
                </div>

                <div class="example-block">
                    <h4><i class="fas fa-lightbulb"></i> Ví dụ minh họa thực tế</h4>
                    <p>Giả sử bạn mượn sách  trong <strong>10 ngày</strong> (trong khoảng 7-14 ngày)</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>💰 Phí thuê:</strong> 10 × 5,000 = 50,000 VNĐ</li>
                 
                    </ul>
    
                    <p style="margin-top: 20px;"><strong>Ví dụ 2:</strong> Nếu bạn <strong>trả trễ 3 ngày</strong>:</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>⛔ Phí trả trễ:</strong> 5,000 VNĐ/ngày × 3 ngày = 15,000 VNĐ</li>
                    </ul>
                    <p style="margin-top: 20px;"><strong>Ví dụ 4:</strong> Nếu bạn <strong>làm hỏng hoặc mất sách</strong></p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>💰 Trừ cọc:</strong>số sách + 8php0% giá trị sách</li>
                    </ul>
                </div>
            </div>

            <!-- General Regulations -->
            <div class="policy-group">
                <div class="group-title">Quy định chung</div>
                <div class="rules-grid">
                    <div class="rule-item">
                        <i class="fas fa-history"></i>
                        <div>
                            <strong>Thời gian mượn</strong>
                            <span>Mượn tối thiểu {{ config('library.borrow_min_days', 1) }} ngày, tối đa {{ config('library.borrow_max_days', 14) }} ngày. Hỗ trợ gia hạn thêm {{ $pricing['rules']['max_extend_times'] ?? 2 }} lần linh hoạt.</span>
                        </div>
                    </div>
                    <div class="rule-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Giờ nhận sách</strong>
                            <span>Nhận sách trong ngày từ {{ config('library.open_hour', '08:00') }} đến {{ config('library.close_hour', '20:00') }}.</span>
                        </div>
                    </div>
                    <div class="rule-item">
                        <i class="fas fa-book"></i>
                        <div>
                            <strong>Số lượng mỗi đơn</strong>
                            <span>Mượn tối thiểu {{ config('library.borrow_min_books', 2) }} cuốn cung 1 loại</span>
                        </div>
                    </div>

                    <div class="rule-item">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <strong>Trách nhiệm bảo quản</strong>
                            <span> Bạn chịu trách nhiệm giữ gìn sách nguyên vẹn, không viết vẽ hoặc làm mất trang sách.</span>
                        </div>
                    </div>
          
                </div>
            </div>
        </div>
    </div>
@endsection