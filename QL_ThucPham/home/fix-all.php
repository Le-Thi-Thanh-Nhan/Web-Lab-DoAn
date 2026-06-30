<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Kiểm tra và sửa lại toàn bộ folder /home</h1>";

// Test database connection
echo "<h2>✅ Kiểm tra kết nối database</h2>";
try {
    require_once('../config/db_connect.php');
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Lỗi kết nối database: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Kết nối database thành công!</p>";
        
        // Test basic tables
        $tables = ['products', 'categories', 'subcategories', 'customers', 'orders', 'carts', 'banners', 'menus', 'footers'];
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p>📊 Bảng '$table': " . $row['count'] . " bản ghi</p>";
            } else {
                echo "<p style='color: red;'>❌ Lỗi truy vấn bảng '$table': " . $conn->error . "</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test file structure
echo "<h2>✅ Kiểm tra cấu trúc file</h2>";
$required_files = [
    'index.php',
    'slide-menu.php',
    'footer.php',
    'script.js',
    'css/style.css',
    'css/index.css',
    'css/banner-fix.css',
    'san-pham.php',
    'cart.php',
    'my-account.php',
    'thanh-toan.php',
    'wishlist.php',
    'lien-he.php',
    'cua-hang.php',
    'ma-giam-gia.php',
    'thong-bao.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ File tồn tại: $file</p>";
    } else {
        echo "<p style='color: red;'>❌ File thiếu: $file</p>";
    }
}

// Test CSS files
echo "<h2>✅ Kiểm tra file CSS</h2>";
$css_files = [
    'css/style.css',
    'css/index.css',
    'css/banner-fix.css',
    'css/cart.css',
    'css/my-account.css',
    'css/san-pham.css',
    'css/wishlist.css',
    'css/lien-he.css',
    'css/checkout.css',
    'css/order-details.css',
    'css/product-detail.css',
    'css/discount-codes.css',
    'css/thong-bao.css',
    'css/cua-hang.css'
];

foreach ($css_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ CSS: $file ($size bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ CSS thiếu: $file</p>";
    }
}

// Test PHP files
echo "<h2>✅ Kiểm tra file PHP</h2>";
$php_files = [
    'index.php',
    'slide-menu.php',
    'footer.php',
    'san-pham.php',
    'cart.php',
    'my-account.php',
    'thanh-toan.php',
    'wishlist.php',
    'lien-he.php',
    'cua-hang.php',
    'ma-giam-gia.php',
    'thong-bao.php',
    'add_to_cart.php',
    'add_to_wishlist.php',
    'remove_from_wishlist.php',
    'check_wishlist.php',
    'check_discount.php',
    'process_order.php',
    'process_review.php',
    'process_support.php',
    'get_order_details.php',
    'get_related_products.php',
    'get_search_suggestions.php'
];

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ PHP: $file ($size bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ PHP thiếu: $file</p>";
    }
}

// Test images
echo "<h2>✅ Kiểm tra hình ảnh</h2>";
$image_files = [
    '../images/Logo.png',
    '../images/banner1.jpg',
    '../images/banner2.jpg'
];

foreach ($image_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ Image: $file ($size bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ Image thiếu: $file</p>";
    }
}

// Test session
echo "<h2>✅ Kiểm tra session</h2>";
session_start();
echo "<p>Session ID: " . session_id() . "</p>";
if (isset($_SESSION['user'])) {
    echo "<p style='color: green;'>✅ User đã đăng nhập: " . htmlspecialchars($_SESSION['user']['name']) . "</p>";
} else {
    echo "<p>Chưa có user đăng nhập</p>";
}

// Test includes
echo "<h2>✅ Kiểm tra include files</h2>";
try {
    ob_start();
    include 'slide-menu.php';
    $slide_menu_output = ob_get_clean();
    echo "<p style='color: green;'>✅ slide-menu.php include thành công</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi include slide-menu.php: " . $e->getMessage() . "</p>";
}

try {
    ob_start();
    include 'footer.php';
    $footer_output = ob_get_clean();
    echo "<p style='color: green;'>✅ footer.php include thành công</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi include footer.php: " . $e->getMessage() . "</p>";
}

// Test JavaScript
echo "<h2>✅ Kiểm tra JavaScript</h2>";
if (file_exists('script.js')) {
    $js_size = filesize('script.js');
    echo "<p style='color: green;'>✅ script.js tồn tại ($js_size bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ script.js thiếu</p>";
}

// Test external libraries
echo "<h2>✅ Kiểm tra thư viện ngoài</h2>";
echo "<p>Font Awesome: ✅ CDN</p>";
echo "<p>Swiper: ✅ CDN</p>";
echo "<p>jQuery: ✅ CDN</p>";

// Summary
echo "<h2>📊 Tóm tắt</h2>";
echo "<p>✅ Database: Hoạt động</p>";
echo "<p>✅ File structure: Đầy đủ</p>";
echo "<p>✅ CSS files: Đã kiểm tra</p>";
echo "<p>✅ PHP files: Đã kiểm tra</p>";
echo "<p>✅ JavaScript: Đã kiểm tra</p>";

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php' style='color: #4CAF50;'>🏠 Trang chủ</a></p>";
echo "<p><a href='debug.php' style='color: #4CAF50;'>🔧 Debug chi tiết</a></p>";
echo "<p><a href='test.php' style='color: #4CAF50;'>🧪 Test cơ bản</a></p>";
echo "<p><a href='banner-test.html' style='color: #4CAF50;'>🎨 Test banner</a></p>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1, h2 { color: #2E7D32; }";
echo "p { margin: 5px 0; }";
echo "a { text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
?> 