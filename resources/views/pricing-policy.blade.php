@extends('layouts.app')

@section('title', 'Chính sách giá - Thư Viện Online')

@push('styles')
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
                        Phí = Giá sách × {{ ($pricing['rental']['daily_rate'] ?? 0.01) * 100 }}% × Ngày
                    </div>
                </div>

                <!-- Tiền cọc -->
                <div class="pillar-card">
                    <div class="pillar-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Tiền cọc sách</h3>
                    <div class="pillar-content">
                        Hoàn trả 100% khi trả sách nguyên vẹn và đúng hạn. Đảm bảo trách nhiệm bảo quản sách bền lâu.
                    </div>
                    <div class="pillar-formula">
                        Cọc = Giá sách × {{ ($pricing['deposit']['rate'] ?? 1.0) * 100 }}%
                    </div>
                </div>

                <!-- Vận chuyển -->
                <div class="pillar-card">
                    <div class="pillar-icon"><i class="fas fa-truck"></i></div>
                    <h3>Phí vận chuyển</h3>
                    <div class="pillar-content">
                        Phí vận chuyển mặc định là 20.000 VNĐ cho mỗi đơn hàng. Miễn phí trong {{ $pricing['shipping']['free_km'] ?? 5 }}km đầu tiên.
                    </div>
                    <div class="pillar-formula">
                        Phí ship mặc định: 20.000 VNĐ/đơn
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
                            ⛔ Trả trễ: 3.000 - 5.000 VNĐ/ngày/cuốn
                        </div>
                    </div>

                    <!-- Trả sớm -->
                    <div class="policy-card">
                        <div class="policy-card-header">
                            <i class="fas fa-gift"></i>
                            <h4>🎁 Trả sớm</h4>
                        </div>
                        <div class="pillar-content">
                            Nếu bạn trả sách sớm hơn thời hạn, bạn sẽ được hoàn lại một phần phí thuê vào ví của mình.
                        </div>
                        <div class="pillar-formula">
                            🎁 Trả sớm: hoàn 20% - 30% vào ví
                        </div>
                    </div>
                </div>
                
                <div class="policy-grid" style="margin-top: 32px;">
                    <!-- Trễ lâu -->
                    <div class="policy-card">
                        <div class="policy-card-header">
                            <i class="fas fa-lock"></i>
                            <h4>🔒 Trễ lâu</h4>
                        </div>
                        <div class="pillar-content">
                            Nếu trả sách quá trễ nhiều ngày, tài khoản sẽ bị khóa mượn và hệ thống sẽ tự động trừ cọc cùng phí ship.
                        </div>
                        <div class="pillar-formula">
                            🔒 Trễ lâu: khóa mượn / trừ cọc + phí ship (20.000 VNĐ)
                        </div>
                    </div>

                    <!-- Làm hỏng/Mất -->
                    <div class="policy-card">
                        <div class="policy-card-header">
                            <i class="fas fa-book-dead"></i>
                            <h4>Sách hỏng hoặc mất</h4>
                        </div>
                        <div class="pillar-content">
                            Bồi thường dựa trên loại sách và tình trạng thực tế. Sách quý bồi thường 100% giá trị niêm yết.
                        </div>
                        <div class="pillar-formula">
                            Bồi thường: 70% - 100% giá trị sách
                        </div>
                    </div>
                </div>

                <div class="example-block">
                    <h4><i class="fas fa-lightbulb"></i> Ví dụ minh họa thực tế</h4>
                    <p>Giả sử bạn mượn sách <strong>100,000 VNĐ</strong> trong <strong>14 ngày</strong> (trong khoảng 7-30 ngày), giao hàng trong <strong>7km</strong>:</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>💰 Phí thuê:</strong> 100,000 × 1% × 14 = 14,000 VNĐ</li>
                        <li style="margin-bottom: 8px;"><strong>🚚 Phí ship:</strong> 20,000 VNĐ (mặc định)</li>
                        <li style="margin-bottom: 8px;"><strong>💵 Tiền cọc (Hoàn lại khi trả đúng hạn):</strong> 100,000 VNĐ</li>
                    </ul>
                    <p style="margin-top: 20px;"><strong>Ví dụ 2:</strong> Nếu bạn <strong>trả sớm</strong> (ví dụ: mượn 14 ngày nhưng trả sau 10 ngày):</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>🎁 Hoàn lại vào ví:</strong> 14,000 × 25% = 3,500 VNĐ</li>
                        <li style="margin-bottom: 8px;"><strong>💵 Tiền cọc:</strong> Hoàn lại 100%</li>
                    </ul>
                    <p style="margin-top: 20px;"><strong>Ví dụ 3:</strong> Nếu bạn <strong>trả trễ 3 ngày</strong>:</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>⛔ Phí trả trễ:</strong> 4,000 VNĐ/ngày × 3 ngày = 12,000 VNĐ</li>
                        <li style="margin-bottom: 8px;"><strong>💵 Tiền cọc:</strong> Hoàn lại sau khi trừ phí trễ</li>
                    </ul>
                    <p style="margin-top: 20px;"><strong>Ví dụ 4:</strong> Nếu bạn <strong>trả trễ quá lâu</strong> (≥15 ngày):</p>
                    <ul style="margin: 15px 0; padding-left: 20px;">
                        <li style="margin-bottom: 8px;"><strong>🔒 Hệ thống sẽ:</strong> Khóa tài khoản mượn sách</li>
                        <li style="margin-bottom: 8px;"><strong>💰 Trừ cọc:</strong> Trừ tiền cọc + phí ship (20,000 VNĐ)</li>
                    </ul>
                    <p style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #fef9c3;"><strong>Tổng chi phí đơn hàng mẫu (Ví dụ 1):</strong> <strong style="color: var(--primary-color); font-size: 1.25rem;">34,000 VNĐ</strong> (chưa tính tiền cọc - sẽ được hoàn lại khi trả sách đúng hạn)</p>
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
                            <span>Mượn tối thiểu 7 ngày, tối đa 30 ngày. Hỗ trợ gia hạn thêm {{ $pricing['rules']['max_extend_times'] ?? 2 }} lần linh hoạt.</span>
                        </div>
                    </div>
                    <div class="rule-item">
                        <i class="fas fa-wallet"></i>
                        <div>
                            <strong>Hoàn tiền cọc</strong>
                            <span>Thực hiện trong 3-5 ngày làm việc sau khi hệ thống xác nhận tình trạng sách trả về.</span>
                        </div>
                    </div>
                    <div class="rule-item">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <strong>Trách nhiệm bảo quản</strong>
                            <span> Bạn chịu trách nhiệm giữ gìn sách nguyên vẹn, không viết vẽ hoặc làm mất trang sách.</span>
                        </div>
                    </div>
                    <div class="rule-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <div>
                            <strong>Thanh toán phí phạt</strong>
                            <span>Các khoản phí phát sinh cần được quyết toán trong vòng {{ $pricing['fines']['payment_deadline_days'] ?? 30 }} ngày.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection