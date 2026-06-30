<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <!-- Main Footer Sections -->
        <div class="footer-sections">
            <!-- Contact Information Section -->
            <div class="footer-section">
                <h3><i class="fas fa-address-card"></i> THÔNG TIN LIÊN HỆ</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>23/25D đường số 1, phường Bình Thuận, Q.7, TP.HCM</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>085222.5XXX</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>webmaster@tp.base.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>7:00 - 22:00 (Thứ 2 - Chủ nhật)</span>
                    </div>
                </div>
            </div>

            <!-- Customer Support Section -->
            <div class="footer-section">
                <h3><i class="fas fa-headset"></i> HỖ TRỢ KHÁCH HÀNG</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-user-tie"></i> Thông tin tuyển dụng</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Chính sách khách hàng</a></li>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Chính sách bảo mật</a></li>
                    <li><a href="#"><i class="fas fa-handshake"></i> Hợp tác nhượng quyền</a></li>
                    <li><a href="#"><i class="fas fa-file-contract"></i> Điều khoản sử dụng</a></li>
                    <li><a href="#"><i class="fas fa-truck"></i> Tra cứu vận đơn</a></li>
                </ul>
            </div>

            <!-- About Us Section -->
            <div class="footer-section">
                <h3><i class="fas fa-info-circle"></i> GIỚI THIỆU</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-briefcase"></i> Thông tin tuyển dụng</a></li>
                    <li><a href="#"><i class="fas fa-credit-card"></i> Chính sách thẻ khách hàng</a></li>
                    <li><a href="#"><i class="fas fa-lock"></i> Chính sách bảo mật</a></li>
                    <li><a href="#"><i class="fas fa-handshake"></i> Hợp tác nhượng quyền</a></li>
                    <li><a href="#"><i class="fas fa-gavel"></i> Điều khoản sử dụng</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Copyright Bar -->
        <div class="copyright-bar">
            <div class="copyright-content">
                <p>Thiết kế bởi nhóm sinh viên DHMT16A2HN - Trường đại học Kinh tế - Kỹ thuật công nghiệp</p>
            </div>
        </div>
    </div>
</footer>

<style>
:root {
    --primary-green: #2E7D32;
    --dark-green: #1B5E20;
    --light-green: #4CAF50;
    --accent-green: #81C784;
    --text-light: #FFFFFF;
    --text-gray: #E8F5E9;
    --border-light: rgba(255,255,255,0.1);
    --shadow-dark: rgba(0,0,0,0.2);
    --gradient-primary: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
    --gradient-secondary: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
    --gradient-footer: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
    --gradient-modern: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-professional: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
    --shadow-soft: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-medium: 0 8px 30px rgba(0,0,0,0.12);
    --shadow-strong: 0 12px 40px rgba(0,0,0,0.15);
    --shadow-glow: 0 0 20px rgba(76, 175, 80, 0.3);
    --border-radius: 12px;
    --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.footer {
    background: var(--gradient-footer);
    color: #2c3e50;
    padding: 80px 0 0;
    margin-top: 80px;
    position: relative;
    width: 100%;
    box-sizing: border-box;
    border-top: 4px solid;
    border-image: var(--gradient-primary) 1;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    z-index: 2;
}

.footer::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(76, 175, 80, 0.02) 0%, rgba(46, 125, 50, 0.02) 100%);
    pointer-events: none;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    position: relative;
    z-index: 1;
}

.footer-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 50px;
    margin-bottom: 60px;
}

.footer-section {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 35px 30px;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition-smooth);
    position: relative;
    overflow: hidden;
}

.footer-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.6s ease;
}

.footer-section:hover::before {
    transform: scaleX(1);
}

.footer-section:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-medium);
    background: rgba(255, 255, 255, 0.9);
}

/* Section Headers */
.footer-section h3 {
    color: var(--primary-green);
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 30px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    padding-bottom: 15px;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--gradient-primary);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.footer-section:hover h3::after {
    width: 80px;
}

.footer-section h3 i {
    color: var(--primary-green);
    font-size: 24px;
    background: linear-gradient(135deg, var(--primary-green), var(--light-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Contact Info */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    color: #34495e;
    font-size: 16px;
    line-height: 1.6;
    transition: var(--transition-smooth);
    padding: 12px 15px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(76, 175, 80, 0.1);
}

.contact-item:hover {
    color: var(--primary-green);
    transform: translateX(8px);
    background: rgba(76, 175, 80, 0.05);
    border-color: rgba(76, 175, 80, 0.2);
    box-shadow: var(--shadow-soft);
}

.contact-item i {
    color: var(--primary-green);
    width: 20px;
    margin-top: 4px;
    font-size: 18px;
    background: linear-gradient(135deg, var(--primary-green), var(--light-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 15px;
}

.footer-links a {
    color: #34495e;
    text-decoration: none;
    font-size: 16px;
    transition: var(--transition-smooth);
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(76, 175, 80, 0.1);
    position: relative;
    overflow: hidden;
}

.footer-links a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.1), transparent);
    transition: left 0.5s ease;
}

.footer-links a:hover::before {
    left: 100%;
}

.footer-links a i {
    color: var(--primary-green);
    width: 18px;
    font-size: 16px;
    background: linear-gradient(135deg, var(--primary-green), var(--light-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.footer-links a:hover {
    color: var(--primary-green);
    background: rgba(76, 175, 80, 0.08);
    border-color: rgba(76, 175, 80, 0.3);
    transform: translateX(8px);
    box-shadow: var(--shadow-soft);
}

/* Category Buttons */
.category-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-bottom: 30px;
    padding: 30px 0;
    border-top: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
}

.category-btn {
    background: var(--gradient-secondary);
    color: white;
    padding: 15px 25px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.category-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.category-btn:hover::before {
    left: 100%;
}

.category-btn:hover {
    background: var(--gradient-primary);
    transform: translateY(-3px);
    box-shadow: var(--shadow-medium);
}

.category-btn i {
    font-size: 16px;
}

/* Copyright Bar */
.copyright-bar {
    background: var(--gradient-professional);
    color: white;
    text-align: center;
    padding: 25px 0;
    font-size: 15px;
    position: relative;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    margin-top: 40px;
}

.copyright-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
}

.copyright-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
    pointer-events: none;
}

.copyright-content {
    display: flex;
    justify-content: center;
    align-items: center;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
}

.copyright-content p {
    margin: 0;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 15px;
}

.payment-methods span {
    font-size: 12px;
    opacity: 0.9;
}

.payment-methods i {
    font-size: 20px;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.payment-methods i:hover {
    opacity: 1;
    transform: scale(1.1);
}

/* Floating Zalo Icon */
.zalo-float {
    position: fixed;
    bottom: 120px;
    right: 25px;
    z-index: 1000;
}

.zalo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #0068ff, #0052cc);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    box-shadow: var(--shadow-medium);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.zalo-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.zalo-icon:hover::before {
    opacity: 1;
}

.zalo-icon:hover {
    transform: scale(1.15);
    box-shadow: var(--shadow-strong);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .footer-sections {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
    }
    
    .footer-content {
        padding: 0 30px;
    }
}

@media (max-width: 768px) {
    .footer {
        padding: 60px 0 0;
        margin-top: 60px;
    }
    
    .footer-content {
        padding: 0 20px;
    }
    
    .footer-sections {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-section {
        padding: 25px 20px;
        text-align: center;
    }
    
    .footer-section h3 {
        justify-content: center;
        font-size: 18px;
    }
    
    .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .contact-item, .footer-links a {
        justify-content: center;
        text-align: center;
    }
    
    .copyright-content {
        padding: 0 20px;
    }
    
    .copyright-content p {
        font-size: 14px;
        line-height: 1.5;
    }
}

@media (max-width: 480px) {
    .footer {
        padding: 50px 0 0;
    }
    
    .footer-sections {
        gap: 25px;
    }
    
    .footer-section {
        padding: 20px 15px;
    }
    
    .footer-section h3 {
        font-size: 16px;
        margin-bottom: 25px;
    }
    
    .contact-item, .footer-links a {
        font-size: 14px;
        padding: 10px 12px;
    }
    
    .copyright-content p {
        font-size: 13px;
    }
}

/* Animation for page load */
.footer-section {
    animation: fadeInUp 0.8s ease-out;
    animation-fill-mode: both;
}

.footer-section:nth-child(1) { animation-delay: 0.1s; }
.footer-section:nth-child(2) { animation-delay: 0.2s; }
.footer-section:nth-child(3) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects for better interactivity */
.footer-section {
    transition: var(--transition-smooth);
}

.footer-section:hover {
    transform: translateY(-8px) scale(1.02);
}

/* Smooth scrolling for anchor links */
html {
    scroll-behavior: smooth;
}

/* Glass morphism effect */
.footer-section {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Enhanced shadows and depth */
.footer-section {
    box-shadow: 
        0 4px 20px rgba(0,0,0,0.08),
        0 1px 3px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.2);
}

.footer-section:hover {
    box-shadow: 
        0 8px 30px rgba(0,0,0,0.12),
        0 2px 6px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.3);
}

/* Gradient text effects */
.footer-section h3 {
    background: linear-gradient(135deg, var(--primary-green), var(--light-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Micro-interactions */
.contact-item, .footer-links a {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.contact-item:active, .footer-links a:active {
    transform: scale(0.98);
}

/* Focus states for accessibility */
.footer-links a:focus {
    outline: 2px solid var(--primary-green);
    outline-offset: 2px;
}

/* Loading animation */
.footer {
    opacity: 0;
    animation: footerLoad 1s ease-out 0.5s forwards;
}

@keyframes footerLoad {
    to {
        opacity: 1;
    }
}
</style> 