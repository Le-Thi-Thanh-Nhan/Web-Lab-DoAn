<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Kiểm tra Database</h1>";

// Test basic MySQL connection
echo "<h2>✅ Kiểm tra kết nối MySQL</h2>";

try {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    
    // Connect without database first
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE 'ql_thucpham'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Database 'ql_thucpham' exists!</p>";
        
        // Connect to the database
        $conn->select_db('ql_thucpham');
        
        // Check tables
        $tables = ['products', 'categories', 'subcategories', 'customers', 'orders', 'carts', 'banners', 'menus', 'footers'];
        
        echo "<h2>📊 Kiểm tra các bảng</h2>";
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_result->fetch_assoc()['count'];
                echo "<p style='color: green;'>✅ Table '$table': $count records</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' does not exist!</p>";
            }
        }
        
        // Check if there are any products
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $product_count = $result->fetch_assoc()['count'];
        
        if ($product_count > 0) {
            echo "<p style='color: green;'>✅ Found $product_count products in database</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No products found in database</p>";
            echo "<p>You may need to import the database data.</p>";
        }
        
        // Check if there are any categories
        $result = $conn->query("SELECT COUNT(*) as count FROM categories");
        $category_count = $result->fetch_assoc()['count'];
        
        if ($category_count > 0) {
            echo "<p style='color: green;'>✅ Found $category_count categories in database</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No categories found in database</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database 'ql_thucpham' does not exist!</p>";
        echo "<h3>🔧 Cách tạo database:</h3>";
        echo "<ol>";
        echo "<li>Mở phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Tạo database mới tên 'ql_thucpham'</li>";
        echo "<li>Import file SQL từ thư mục includes/ql_thucpham.sql</li>";
        echo "</ol>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<h3>🔧 Kiểm tra XAMPP:</h3>";
    echo "<ol>";
    echo "<li>Đảm bảo XAMPP đang chạy</li>";
    echo "<li>Kiểm tra Apache và MySQL đã Start</li>";
    echo "<li>Kiểm tra port 3306 không bị conflict</li>";
    echo "</ol>";
}

// Test the actual db_connect.php file
echo "<h2>✅ Kiểm tra file db_connect.php</h2>";

if (file_exists('../config/db_connect.php')) {
    echo "<p style='color: green;'>✅ File db_connect.php exists</p>";
    
    try {
        require_once('../config/db_connect.php');
        echo "<p style='color: green;'>✅ db_connect.php loaded successfully</p>";
        
        if (isset($conn) && $conn instanceof mysqli) {
            echo "<p style='color: green;'>✅ Database connection object created</p>";
            
            // Test a simple query
            $result = $conn->query("SELECT 1 as test");
            if ($result) {
                echo "<p style='color: green;'>✅ Database query test successful</p>";
            } else {
                echo "<p style='color: red;'>❌ Database query test failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database connection object not created</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error loading db_connect.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ File db_connect.php not found!</p>";
}

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='simple-test.php' style='color: #4CAF50;'>🧪 Simple Test</a></p>";
echo "<p><a href='index.php' style='color: #4CAF50;'>🏠 Homepage</a></p>";
echo "<p><a href='debug.php' style='color: #4CAF50;'>🔧 Debug Page</a></p>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1, h2, h3 { color: #2E7D32; }";
echo "p { margin: 5px 0; }";
echo "ol { margin: 10px 0; padding-left: 20px; }";
echo "li { margin: 5px 0; }";
echo "a { text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
?> 