@once
<style>
    .main-footer {
        background: #ffffff;
        margin-top: 64px;
        border-top: 1px solid rgba(226, 232, 240, 0.95);
        position: relative;
        overflow: hidden;
        width: 100%;
        isolation: isolate;
    }

    .main-footer::before {
        content: none;
        pointer-events: none;
    }

    .main-footer .footer-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 54px 20px 0;
        position: relative;
        z-index: 1;
    }

    .main-footer .footer-top-section {
        margin-bottom: 28px;
    }

    .main-footer .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 26px 28px;
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(12px);
    }

    .main-footer .footer-logo-link {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .main-footer .footer-logo-icon {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
    }

    .main-footer .footer-logo-icon svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .main-footer .footer-brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .main-footer .footer-brand-label {
        font-size: 12px;
        letter-spacing: 0.08em;
        color: #0f172a;
        font-weight: 700;
    }

    .main-footer .footer-brand-name {
        font-size: 24px;
        font-weight: 800;
        color: #0d9488;
    }

    .main-footer .footer-tagline {
        margin: 0;
        color: #64748b;
        max-width: 560px;
        line-height: 1.75;
    }

    .main-footer .footer-social-links {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .main-footer .footer-social-links .social-link {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid #e2e8f0;
        color: #334155;
        transition: all .2s ease;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
    }

    .main-footer .footer-social-links .social-link .social-icon {
        width: 16px;
        height: 16px;
        fill: currentColor;
        display: block;
    }

    .main-footer .footer-social-links .social-link:hover {
        color: #0d9488;
        border-color: #0d9488;
        transform: translateY(-3px);
    }

    .main-footer .footer-main-content {
        padding: 12px 0 30px;
        display: block;
        width: 100%;
    }

    .main-footer .footer-middle {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 22px;
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .main-footer .footer-column {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.92);
        border-radius: 22px;
        padding: 22px 20px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.06);
        display: block;
    }

    .main-footer .footer-column-title {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .main-footer .footer-links-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .main-footer .footer-links-list a,
    .main-footer .footer-links-list span {
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
    }

    .main-footer .footer-links-list a:hover {
        color: #0d9488;
        text-decoration: underline;
    }

    .main-footer .footer-contact-list li {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .main-footer .footer-contact-list i {
        color: #0d9488;
        width: 14px;
        text-align: center;
        font-size: 13px;
    }

    .main-footer .footer-copyright {
        border-top: 1px solid #e2e8f0;
        padding: 18px 0 24px;
        margin: 0;
        width: 100%;
        background: transparent;
    }

    .main-footer .copyright-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 13px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(226, 232, 240, 0.92);
        border-radius: 18px;
        padding: 14px 18px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .main-footer .copyright-content p {
        margin: 0;
    }

    .main-footer .copyright-content strong {
        color: #0f172a;
    }

    .main-footer .copyright-links {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .main-footer .copyright-links a {
        color: #64748b;
        text-decoration: none;
    }

    .main-footer .copyright-links a:hover {
        color: #0d9488;
        text-decoration: underline;
    }

    @media (max-width: 992px) {
        .main-footer .footer-middle {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .main-footer .footer-container {
            padding: 42px 16px 0;
        }

        .main-footer .footer-brand,
        .main-footer .footer-column,
        .main-footer .copyright-content {
            border-radius: 20px;
        }

        .main-footer .footer-middle {
            grid-template-columns: 1fr;
        }

        .main-footer .copyright-content {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endonce

<footer class="main-footer">
    <div class="footer-container">
        <!-- Footer Top - Logo & Info -->
        <div class="footer-top-section">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="footer-logo-link">
                    <div class="footer-logo-icon">
                        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="url(#footer-logo-gradient)"/>
                            <path d="M12 14h6v20h-6v-20zm9 0h6v20h-6v-20zm9 0h6v20h-6v-20z" fill="rgba(255,255,255,0.3)"/>
                            <path d="M14 16h20c1 0 2 1 2 2v12c0 1-1 2-2 2H14c-1 0-2-1-2-2V18c0-1 1-2 2-2z" fill="white"/>
                            <path d="M16 20h8v2h-8v-2zm0 4h12v2H16v-2zm0 4h6v2h-6v-2z" fill="url(#footer-logo-gradient)"/>
                            <defs>
                                <linearGradient id="footer-logo-gradient" x1="0" y1="0" x2="48" y2="48">
                                    <stop offset="0%" stop-color="#0d9488"/>
                                    <stop offset="100%" stop-color="#14b8a6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="footer-brand-text">
                        <span class="footer-brand-label">THƯ VIỆN</span>
                        <span class="footer-brand-name">LibNet</span>
                    </div>
                </a>
                <p class="footer-tagline">Hệ thống thư viện trực tuyến hiện đại, kết nối tri thức không giới hạn.</p>
                <div class="footer-social-links">
                    <a href="#" class="social-link" title="Facebook" aria-label="Facebook">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.3-1.5 1.6-1.5h1.7V5c-.3 0-1.4-.1-2.6-.1-2.6 0-4.3 1.6-4.3 4.5V11H7v3h3v8h3.5z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" title="YouTube" aria-label="YouTube">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M23.5 7.2a3 3 0 0 0-2.1-2.1C19.6 4.5 12 4.5 12 4.5s-7.6 0-9.4.6A3 3 0 0 0 .5 7.2 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.8.6 9.4.6 9.4.6s7.6 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-4.8zM9.6 15.1V8.9L15.8 12l-6.2 3.1z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" title="Email" aria-label="Email">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 6.5A2.5 2.5 0 0 1 4.5 4h15A2.5 2.5 0 0 1 22 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 17.5v-11zm2.7-.5L12 11l7.3-5H4.7zM20 8l-7.4 5a1 1 0 0 1-1.2 0L4 8v9.5a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5V8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-main-content">
            <!-- Phần các cột link -->
            <div class="footer-middle">
                <div class="footer-column">
                    <h4 class="footer-column-title">
                        <i class="fas fa-users"></i>
                        CHÚNG TÔI PHỤC VỤ
                    </h4>
                    <ul class="footer-links-list">
                        <li><a href="#">Trường đại học</a></li>
                        <li><a href="#">Doanh nghiệp/ Tổ chức</a></li>
                        <li><a href="#">Quản lý thư viện</a></li>
                        <li><a href="#">Sinh viên</a></li>
                        <li><a href="#">Viện Nghiên cứu</a></li>
                        <li><a href="#">Tác giả</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4 class="footer-column-title">
                        <i class="fas fa-info-circle"></i>
                        VỀ LIBNET
                    </h4>
                    <ul class="footer-links-list">
                        <li><a href="#">Giới thiệu</a></li>
                        <li><a href="#">Liên hệ</a></li>
                        <li><a href="#">Các đối tác</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4 class="footer-column-title">
                        <i class="fas fa-file-alt"></i>
                        CHÍNH SÁCH & ĐIỀU KHOẢN
                    </h4>
                    <ul class="footer-links-list">
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                        <li><a href="#">Chính sách hoàn trả</a></li>
                        <li><a href="#">Hướng dẫn mượn sách</a></li>
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4 class="footer-column-title">
                        <i class="fas fa-headset"></i>
                        HỖ TRỢ
                    </h4>
                    <ul class="footer-links-list footer-contact-list">
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <a href="tel:0327888669">0327 888 669</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:support@libnet.vn">support@libnet.vn</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Hà Nội, Việt Nam</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>T2-T7: 8:00 - 18:00</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Copyright bar -->
        <div class="footer-copyright">
            <div class="copyright-content">
                <p>&copy; {{ date('Y') }} <strong>LibNet</strong> - Hệ thống Thư viện Trực tuyến. All rights reserved.</p>
                <p class="copyright-links">
                    <a href="#">Chính sách</a>
                    <span>•</span>
                    <a href="#">Điều khoản</a>
                    <span>•</span>
                    <a href="#">Sitemap</a>
                </p>
            </div>
        </div>
    </div>
</footer>

