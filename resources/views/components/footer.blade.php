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
                    <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-link" title="Email"><i class="fas fa-envelope"></i></a>
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

