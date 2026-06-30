<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test Đơn Giản</h1>";
echo "<p>✅ PHP đang hoạt động!</p>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";

// Test database connection
echo "<h2>🔍 Test Database</h2>";

try {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'ql_thucpham';
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Kết nối database thất bại: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Kết nối database thành công!</p>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p style='color: green;'>✅ Có $count sản phẩm trong database</p>";
    } else {
        echo "<p style='color: red;'>❌ Lỗi truy vấn database</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi database: " . $e->getMessage() . "</p>";
}

// Test file includes
echo "<h2>📁 Test Files</h2>";

$files = [
    'css/style.css',
    'css/index.css',
    'script.js',
    '../images/Logo.png'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ File tồn tại: $file</p>";
    } else {
        echo "<p style='color: red;'>❌ File không tồn tại: $file</p>";
    }
}

echo "<h2>🔗 Links</h2>";
echo "<p><a href='index.php' style='color: #4CAF50;'>🏠 Trang chủ</a></p>";
echo "<p><a href='check-database.php' style='color: #4CAF50;'>🔧 Kiểm tra Database</a></p>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1, h2 { color: #2E7D32; }";
echo "p { margin: 5px 0; }";
echo "a { text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
?> 