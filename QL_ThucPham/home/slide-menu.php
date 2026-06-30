<?php
if (!isset($_SESSION)) {
    session_start();
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ql_thucpham';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    $conn = null;
}
?>

<style>
:root {
    --primary-color: #2E7D32;
    --primary-dark: #1B5E20;
    --primary-light: #4CAF50;
    --primary-lighter: #66BB6A;
    --primary-darkest: #0D4A0D;
    --text-light: #FFFFFF;
    --text-dark: #333333;
    --text-muted: #666666;
    --hover-color: #388E3C;
    --hover-light: #81C784;
    --notification-bg: rgba(27, 94, 32, 0.08);
    --notification-text: #2E7D32;
    --shadow-light: rgba(0,0,0,0.1);
    --shadow-medium: rgba(0,0,0,0.15);
    --shadow-heavy: rgba(0,0,0,0.2);
    --border-light: rgba(255,255,255,0.2);
    --border-medium: rgba(255,255,255,0.3);
}

/* Top Section */
.top-section {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    height: 75px;
    padding: 8px 0;
    color: var(--text-light);
    box-shadow: 0 4px 20px var(--shadow-medium);
    position: relative;
    overflow: hidden;
}

.top-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.05), transparent);
    pointer-events: none;
}

.top-container {
    max-width: 100%;
    margin: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0;
    position: relative;
    z-index: 2;
    width: 100%;
}

/* Logo Section - Sát viền trái */
.logo-section {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 0 0 auto;
    margin-left: 15px;
    padding-left: 0;
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
}

.logo-section a {
    display: flex;
    align-items: center;
    gap: 20px;
    text-decoration: none;
    color: var(--text-light);
    transition: all 0.3s ease;
}

.logo-section a:hover {
    transform: translateY(-2px);
    filter: drop-shadow(0 4px 8px var(--shadow-medium));
}

.logo-section img {
    height: 50px;
    width: auto;
    filter: drop-shadow(0 3px 6px var(--shadow-light));
    transition: all 0.3s ease;
}

.logo-section:hover img {
    transform: scale(1.05);
}

.logo-text {
    font-size: 20px;
    font-weight: 700;
    text-shadow: 0 2px 4px var(--shadow-light);
    letter-spacing: 0.5px;
}

/* Slogan Section - Ở giữa */
.slogan-section {
    text-align: center;
    flex: 1;
    margin: 0;
    padding: 0;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: auto;
}

.slogan-text {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    text-shadow: 0 2px 4px var(--shadow-light);
    letter-spacing: 1px;
    position: relative;
}

.slogan-text::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: var(--text-light);
    transition: width 0.3s ease;
}

.slogan-section:hover .slogan-text::after {
    width: 80%;
}

/* Auth Section - Sát viền phải */
.auth-section {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 0 0 auto;
    margin-right: 15px;
    padding-right: 0;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
}

.auth-buttons {
    display: flex;
    gap: 15px;
}

.auth-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px var(--shadow-light);
}

.signup-btn {
    background: transparent;
    border: 2px solid var(--text-light);
    color: var(--text-light);
}

.login-btn {
    background: var(--text-light);
    color: var(--primary-color);
    border: 2px solid var(--text-light);
}

.signup-btn:hover {
    background: rgba(255,255,255,0.15);
    border-color: var(--hover-light);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.login-btn:hover {
    background: var(--hover-light);
    color: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-welcome {
    background: rgba(255,255,255,0.15);
    padding: 8px 16px;
    border-radius: 20px;
    border: 1px solid var(--border-light);
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px var(--shadow-light);
}

.user-welcome:hover {
    background: rgba(255,255,255,0.25);
    border-color: var(--border-medium);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.cart-btn {
    background: var(--primary-light);
    color: var(--text-light);
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px var(--shadow-light);
    letter-spacing: 0.5px;
}

.cart-btn:hover {
    background: var(--hover-color);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.cart-btn i {
    font-size: 16px;
}

/* Menu Toggle Button */
.menu-toggle {
    background: rgba(255,255,255,0.15);
    border: 2px solid var(--border-light);
    color: var(--text-light);
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px var(--shadow-light);
}

.menu-toggle:hover {
    background: rgba(255,255,255,0.25);
    border-color: var(--border-medium);
    transform: translateY(-3px) rotate(5deg);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

/* Notification Slider - Chiều cao giảm */
.notification-slider {
    background: var(--notification-bg);
    padding: 12px 0;
    border-top: 1px solid rgba(27, 94, 32, 0.15);
    border-bottom: 1px solid rgba(27, 94, 32, 0.15);
    overflow: hidden;
    height: 50px;
    display: flex;
    align-items: center;
    position: relative;
}

.notification-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
    position: relative;
    width: 100%;
}

.notification-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.notification-icon {
    color: var(--notification-text);
    font-size: 16px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.notification-text {
    color: var(--notification-text);
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 75%;
    letter-spacing: 0.3px;
    position: relative;
    transition: transform 0.5s ease;
}

.notification-dots {
    display: none;
    gap: 8px;
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
}

.notification-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--notification-text);
    opacity: 0.3;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-dot:hover {
    opacity: 0.6;
    transform: scale(1.2);
}

.notification-dot.active {
    opacity: 1;
    transform: scale(1.1);
}

/* Top Menu - Fixed position */
.top-menu {
    background: var(--primary-dark);
    padding: 0;
    box-shadow: 0 4px 20px var(--shadow-medium);
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 3px solid var(--primary-light);
}

.menu-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
}

.menu-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    gap: 0;
}

.menu-item {
    position: relative;
}

.menu-link {
    color: var(--text-light);
    text-decoration: none;
    padding: 18px 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
}

.menu-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s ease;
}

.menu-link:hover::before {
    left: 100%;
}

.menu-link:hover,
.menu-link.active {
    background: var(--hover-color);
    border-bottom-color: var(--text-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.menu-link.active {
    background: var(--primary-light);
}

.menu-link i {
    font-size: 16px;
    transition: transform 0.3s ease;
    }

.menu-link:hover i {
    transform: scale(1.2);
}

/* Slide Menu */
.slide-menu {
    position: fixed;
    top: 0;
    right: -320px;
    width: 320px;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 20px var(--shadow-heavy);
    transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
    overflow-y: auto;
    border-left: 4px solid var(--primary-color);
    scrollbar-width: none;
    -ms-overflow-style: none;
    box-sizing: border-box;
    max-width: 100vw;
    max-height: 100vh;
    transform: translateX(-100%);
    clip-path: inset(0 0 0 0);
}

/* Ẩn scrollbar cho Webkit browsers (Chrome, Safari, Edge) */
.slide-menu::-webkit-scrollbar {
    display: none;
}

.slide-menu.active {
    right: 0;
    box-sizing: border-box;
    max-width: 100vw;
    max-height: 100vh;
    transform: translateX(0);
    clip-path: inset(0 0 0 0);
}

.slide-menu-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 25px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px var(--shadow-light);
}

.slide-menu-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.slide-menu-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slide-menu-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.slide-menu-user {
    padding: 25px 20px;
    border-bottom: 1px solid #eee;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.user-details {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: all 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px var(--shadow-heavy);
}

.user-avatar i {
    font-size: 28px;
    color: white;
}

.user-info-text {
    flex: 1;
}

.user-name {
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
    font-size: 18px;
    letter-spacing: 0.3px;
}

.user-email {
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 500;
}

/* Slide Menu Navigation */
.slide-menu-nav {
    padding: 0;
}

.nav-section {
    margin-bottom: 0;
}

.nav-section-title {
    padding: 20px 20px 12px;
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 18px 20px;
    color: var(--text-dark);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 16px;
    font-weight: 500;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.nav-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-color);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.nav-item:hover::before {
    transform: scaleY(1);
}

.nav-item:hover {
    background: linear-gradient(90deg, #f8f9fa, #e9ecef);
    color: var(--primary-color);
    transform: translateX(5px);
    box-shadow: 0 2px 8px var(--shadow-light);
}

.nav-item i {
    width: 24px;
    margin-right: 15px;
    font-size: 18px;
    transition: transform 0.3s ease;
}

.nav-item:hover i {
    transform: scale(1.2);
}

.nav-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, #e9ecef, transparent);
    margin: 0;
}

/* Guest Menu */
.guest-menu {
    padding: 25px 20px;
}

.guest-menu-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.guest-menu-btn {
    flex: 1;
    padding: 15px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px var(--shadow-light);
}

.guest-signup {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: none;
}

.guest-login {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.guest-menu-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px var(--shadow-medium);
}

.guest-signup:hover {
    background: linear-gradient(135deg, var(--hover-color), var(--primary-dark));
}

.guest-login:hover {
    background: var(--primary-color);
    color: white;
}

.menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: none;
    z-index: 999;
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
}

.menu-overlay.active {
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .top-container {
        flex-direction: column;
        gap: 20px;
        padding: 0 20px;
    }
    
    .logo-section {
        margin-right: 0;
        gap: 15px;
    }
    
    .logo-section img {
        height: 50px;
    }
    
    .logo-text {
        font-size: 20px;
    }
    
    .slogan-section {
        margin: 0 20px;
    }
    
    .slogan-text {
        font-size: 18px;
    }
    
    .auth-section {
        margin-left: 0;
        gap: 15px;
    }
    
    .auth-buttons {
        gap: 10px;
    }
    
    .auth-btn {
        padding: 10px 18px;
        font-size: 14px;
    }
    
    .user-info {
        gap: 15px;
    }
    
    .user-welcome {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .cart-btn {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .menu-toggle {
        width: 44px;
        height: 44px;
        padding: 10px;
    }
    
    .menu-list {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .menu-link {
        padding: 15px 18px;
        font-size: 14px;
    }
    
    .notification-slider {
        height: 45px;
        padding: 10px 0;
    }
    
    .notification-text {
        font-size: 13px;
    }
    
    .slide-menu {
        width: 280px;
        right: -280px;
        max-width: 100vw;
        max-height: 100vh;
        transform: translateX(0);
        margin-right: -5px;
    }
}

/* Slide Cart - Professional UI */
.slide-cart {
    position: fixed;
    top: 0;
    right: -380px;
    width: 360px;
    height: 100vh;
    background: #fff;
    box-shadow: -4px 0 24px rgba(44,62,80,0.18);
    transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1100;
    overflow-y: auto;
    border-left: 4px solid var(--primary-color);
    max-width: 100vw;
    max-height: 100vh;
    display: flex;
    flex-direction: column;
    font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
}
.slide-cart.active { right: 0; }
.slide-cart-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 22px 24px 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px var(--shadow-light);
    border-top-left-radius: 8px;
}
.slide-cart-header h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.slide-cart-close {
    background: rgba(255,255,255,0.18);
    border: none;
    color: white;
    font-size: 26px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.slide-cart-close:hover {
    background: rgba(255,255,255,0.32);
    transform: rotate(90deg) scale(1.08);
}
.slide-cart-content {
    flex: 1;
    padding: 18px 18px 0 18px;
    overflow-y: auto;
}
.slide-cart-empty {
    text-align: center;
    color: var(--text-muted);
    margin-top: 48px;
    font-size: 17px;
    font-weight: 500;
}
.slide-cart-product {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
    background: #f8fafb;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(44,62,80,0.06);
    padding: 14px 14px 14px 10px;
    position: relative;
    transition: box-shadow 0.2s;
}
.slide-cart-product:hover {
    box-shadow: 0 4px 18px rgba(44,62,80,0.13);
}
.slide-cart-product-img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 50%;
    background: #f5f5f5;
    border: 1.5px solid #e0e0e0;
    box-shadow: 0 1px 4px rgba(44,62,80,0.08);
}
.slide-cart-product-info {
    flex: 1;
    min-width: 0;
}
.slide-cart-product-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.slide-cart-product-price {
    color: #e53935;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.slide-cart-product-qty {
    background: #e0f2f1;
    color: #00897b;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    padding: 2px 8px 2px 8px;
    margin-left: 6px;
    display: inline-block;
}
.slide-cart-product-remove {
    color: #e53935;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 10px;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.slide-cart-product-remove:hover {
    background: #ffeaea;
    color: #b71c1c;
}
.slide-cart-footer {
    padding: 18px 20px 18px 20px;
    border-top: 1.5px solid #eee;
    background: #fafafa;
    box-shadow: 0 -2px 8px var(--shadow-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.slide-cart-total {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-dark);
    margin: 0;
    letter-spacing: 0.5px;
}
.slide-cart-checkout-btn {
    padding: 12px 32px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(44,62,80,0.10);
    transition: background 0.2s, transform 0.2s;
    outline: none;
    white-space: nowrap;
}
.slide-cart-checkout-btn:hover {
    background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
    transform: translateY(-2px) scale(1.04);
}
@media (max-width: 768px) {
    .slide-cart {
        width: 95vw;
        right: -100vw;
    }
    .slide-cart.active {
        right: 0;
    }
}
</style>

<!-- Top Section -->
<div class="top-section">
    <div class="top-container">
        <!-- Logo Section - Gần viền trái -->
        <div class="logo-section">
        <a href="index.php">
                <img src="../images/Logo.png" alt="Logo Thực Phẩm Mộc">
            <span class="logo-text">Thực phẩm Mộc</span>
        </a>
    </div>
        
        <!-- Slogan Section - Ở giữa -->
        <div class="slogan-section">
            <h1 class="slogan-text">Tươi Sạch Mỗi Ngày</h1>
    </div>
        
        <!-- Auth Section - Bên phải -->
        <div class="auth-section">
        <?php if (isset($_SESSION['user'])): ?>
            <div class="user-info">
                <div class="user-welcome">
                        Xin chào, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                </div>
                    <button type="button" class="cart-btn" onclick="toggleSlideCart()">
                        <i class="fas fa-shopping-cart"></i>
                        Giỏ hàng
                    </button>
                    <button class="menu-toggle" onclick="toggleSlideMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        <?php else: ?>
                <div class="auth-buttons">
                    <a href="../auth/auth.php" class="auth-btn signup-btn">Đăng ký</a>
                    <a href="../auth/auth.php" class="auth-btn login-btn">Đăng nhập</a>
                    <button class="menu-toggle" onclick="toggleSlideMenu()">
                        <i class="fas fa-bars"></i>
                    </button>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notification Slider - Chiều cao giảm -->
<div class="notification-slider">
    <div class="notification-container">
        <div class="notification-content">
            <div class="notification-text" id="notification-text">
        <?php
                if ($conn) {
                    try {
                        $notifications_query = "SELECT subject FROM notifications WHERE recipient_type = 'all' ORDER BY created_at DESC LIMIT 5";
                        $notifications = $conn->query($notifications_query);
                        
                        if ($notifications && $notifications->num_rows > 0) {
                            $notification_data = [];
                            while ($row = $notifications->fetch_assoc()) {
                                $notification_data[] = $row['subject'];
                            }
                            echo '<script>var notifications = ' . json_encode($notification_data) . ';</script>';
                            echo htmlspecialchars($notification_data[0]);
                        } else {
                            echo "Chào mừng đến với Thực Phẩm Mộc - Nơi cung cấp thực phẩm tươi sạch, an toàn!";
                        }
                    } catch (Exception $e) {
                        echo "Chào mừng đến với Thực Phẩm Mộc!";
                }
            } else {
                    echo "Chào mừng đến với Thực Phẩm Mộc!";
            }
            ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Menu - Fixed position -->
<nav class="top-menu">
    <div class="menu-container">
        <ul class="menu-list">
            <li class="menu-item">
                <a href="index.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
            </li>
            <li class="menu-item">
                <a href="san-pham.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'san-pham.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i>
                    Sản phẩm
                </a>
            </li>
            <li class="menu-item">
                <a href="thong-bao.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'thong-bao.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    Thông báo
                </a>
            </li>
            <li class="menu-item">
                <a href="ma-giam-gia.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'ma-giam-gia.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    Mã giảm giá
                </a>
            </li>
            <li class="menu-item">
                <a href="cua-hang.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'cua-hang.php' ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i>
                    Cửa hàng
                </a>
            </li>
            <li class="menu-item">
                <a href="lien-he.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'lien-he.php' ? 'active' : ''; ?>">
                    <i class="fas fa-phone"></i>
                    Liên hệ
                </a>
            </li>
            <li class="menu-item">
                <a href="index.php#gioi-thieu" class="menu-link">
                    <i class="fas fa-info-circle"></i>
                    Giới thiệu
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Slide Menu -->
<div class="slide-menu" id="slideMenu">
    <div class="slide-menu-header">
        <h3>Menu</h3>
        <button class="slide-menu-close" onclick="toggleSlideMenu()">&times;</button>
    </div>
    
    <?php if (isset($_SESSION['user'])): ?>
        <div class="slide-menu-user">
            <div class="user-details">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info-text">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                </div>
            </div>
        </div>
            <div class="nav-section">
                <div class="nav-section-title">Tài khoản</div>
                <a href="my-account.php" class="nav-item">
                    <i class="fas fa-user"></i>Tài khoản của tôi
                </a>
                <a href="cart.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>Giỏ hàng
                </a>
                <a href="wishlist.php" class="nav-item">
                    <i class="fas fa-heart"></i>Yêu thích
                </a>
                </div>
            
        <nav class="slide-menu-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu chính</div>
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i>Trang chủ
                </a>
                <a href="san-pham.php" class="nav-item">
                    <i class="fas fa-shopping-bag"></i>Sản phẩm
                </a>
                <a href="thong-bao.php" class="nav-item">
                    <i class="fas fa-bell"></i>Thông báo
                </a>
                <a href="ma-giam-gia.php" class="nav-item">
                    <i class="fas fa-tags"></i>Mã giảm giá
                </a>
                <a href="cua-hang.php" class="nav-item">
                    <i class="fas fa-store"></i>Cửa hàng
                </a>
                <a href="lien-he.php" class="nav-item">
                    <i class="fas fa-phone"></i>Liên hệ
                </a>
                <a href="index.php#gioi-thieu" class="nav-item">
                    <i class="fas fa-info-circle"></i>Giới thiệu
                </a>
            </div>
            
            <div class="nav-divider"></div>
            
            <div class="nav-section">
            <a href="../auth/logout.php" class="nav-item" style="color: #dc3545;">
                <i class="fas fa-sign-out-alt"></i>Đăng xuất
            </a>
            </div>
        </nav>
    <?php else: ?>
        <div class="guest-menu">
            <div class="guest-menu-buttons">
                <a href="../auth/auth.php" class="guest-menu-btn guest-signup">Đăng ký</a>
                <a href="../auth/auth.php" class="guest-menu-btn guest-login">Đăng nhập</a>
            </div>
            <nav class="slide-menu-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Menu chính</div>
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-home"></i>Trang chủ
                    </a>
                    <a href="san-pham.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i>Sản phẩm
                    </a>
                    <a href="thong-bao.php" class="nav-item">
                        <i class="fas fa-bell"></i>Thông báo
                    </a>
                    <a href="ma-giam-gia.php" class="nav-item">
                        <i class="fas fa-tags"></i>Mã giảm giá
                    </a>
                    <a href="cua-hang.php" class="nav-item">
                        <i class="fas fa-store"></i>Cửa hàng
                    </a>
                    <a href="lien-he.php" class="nav-item">
                        <i class="fas fa-phone"></i>Liên hệ
                    </a>
                    <a href="index.php#gioi-thieu" class="nav-item">
                        <i class="fas fa-info-circle"></i>Giới thiệu
                    </a>
                </div>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Slide Cart -->
<div class="slide-cart" id="slideCart">
    <div class="slide-cart-header">
        <h3>Giỏ hàng</h3>
        <button class="slide-cart-close" onclick="toggleSlideCart()">&times;</button>
    </div>
    <div class="slide-cart-content">
        <?php
        if (!isset($_SESSION['user'])) {
            echo '<div class="slide-cart-empty">Vui lòng đăng nhập để xem giỏ hàng.</div>';
        } else {
            $customer_id = $_SESSION['user']['customer_id'];
            $sql = "SELECT c.*, p.name, p.price, p.image_url FROM carts c JOIN products p ON c.product_id = p.product_id WHERE c.customer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $cart_items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if (empty($cart_items)) {
                echo '<div class="slide-cart-empty">Giỏ hàng của bạn đang trống.</div>';
            } else {
                $total = 0;
                foreach ($cart_items as $item) {
                    $img = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : '../images/Logo.png';
                    $name = htmlspecialchars($item['name']);
                    $price = number_format($item['price'], 0, ',', '.');
                    $qty = intval($item['quantity']);
                    $subtotal = $item['price'] * $qty;
                    $total += $subtotal;
                    echo '<div class="slide-cart-product">';
                    echo '<img class="slide-cart-product-img" src="' . $img . '" alt="' . $name . '">';
                    echo '<div class="slide-cart-product-info">';
                    echo '<div class="slide-cart-product-title">' . $name . '</div>';
                    echo '<div class="slide-cart-product-price">' . $price . 'đ <span class="slide-cart-product-qty">' . $qty . '</span></div>';
                    echo '</div>';
                    echo '<button class="slide-cart-product-remove" title="Xóa sản phẩm">&times;</button>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
    <div class="slide-cart-footer">
        <?php if (isset($cart_items) && !empty($cart_items)) {
            echo '<div class="slide-cart-total">Tổng cộng: ' . number_format($total, 0, ',', '.') . 'đ</div>';
            echo '<a href="thanh-toan.php" class="slide-cart-checkout-btn">Thanh toán</a>';
        } ?>
    </div>
</div>

<div class="menu-overlay" id="menuOverlay" onclick="closeAllSlides()"></div>

<script>
// Notification Slider
document.addEventListener('DOMContentLoaded', function() {
    const notificationText = document.getElementById('notification-text');
    
    if (typeof notifications !== 'undefined' && notifications.length > 1) {
        let currentIndex = 0;

        function updateNotification() {
            // Tạo hiệu ứng slide: thông báo cũ trượt sang trái và mất đi
            notificationText.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                // Cập nhật nội dung mới
                notificationText.textContent = notifications[currentIndex];
                
                // Thông báo mới xuất hiện từ phải trượt vào giữa
                notificationText.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    notificationText.style.transform = 'translateX(0)';
                }, 50);
                
                // Chuyển sang thông báo tiếp theo
                currentIndex = (currentIndex + 1) % notifications.length;
            }, 500);
        }
        
        // Change notification every 7 seconds
        setInterval(updateNotification, 7000);
    }
});

// Slide Menu Toggle
function toggleSlideMenu() {
    const slideMenu = document.getElementById('slideMenu');
    const menuOverlay = document.getElementById('menuOverlay');
    
    slideMenu.classList.toggle('active');
    menuOverlay.classList.toggle('active');
}

function toggleSlideCart() {
    const slideCart = document.getElementById('slideCart');
    const menuOverlay = document.getElementById('menuOverlay');
    const slideMenu = document.getElementById('slideMenu');
    // Đóng slide-menu nếu đang mở
    if (slideMenu.classList.contains('active')) {
        slideMenu.classList.remove('active');
    }
    // Nếu mở slide-cart thì load lại nội dung
    if (!slideCart.classList.contains('active')) {
        loadSlideCartContent();
    }
    slideCart.classList.toggle('active');
    menuOverlay.classList.toggle('active');
}
function closeAllSlides() {
    document.getElementById('slideMenu').classList.remove('active');
    document.getElementById('slideCart').classList.remove('active');
    document.getElementById('menuOverlay').classList.remove('active');
}

function loadSlideCartContent() {
    fetch('get_slide_cart.php')
        .then(res => res.text())
        .then(html => {
            // Tách phần content và footer
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const content = temp.querySelector('.slide-cart-content') ? temp.querySelector('.slide-cart-content').innerHTML : html;
            const footer = temp.querySelector('.slide-cart-footer') ? temp.querySelector('.slide-cart-footer').innerHTML : '';
            document.querySelector('.slide-cart-content').innerHTML = content;
            document.querySelector('.slide-cart-footer').innerHTML = footer;
        });
}

// Hook vào fetch thêm sản phẩm (nếu có window.addToCart)
if (typeof addToCart === 'function') {
    const oldAddToCart = addToCart;
    window.addToCart = function(productId, quantity) {
        oldAddToCart(productId, quantity);
        setTimeout(loadSlideCartContent, 500); // delay nhỏ để backend cập nhật xong
    }
}
// Nếu dùng fetch riêng, hãy gọi loadSlideCartContent() sau khi thêm sản phẩm thành công
</script> 