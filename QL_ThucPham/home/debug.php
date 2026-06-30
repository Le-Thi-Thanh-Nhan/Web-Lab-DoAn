<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Debug Information</h1>";

// Test PHP
echo "<h2>✅ PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";

// Test database connection
echo "<h2>✅ Database Connection Test</h2>";
try {
    require_once('../config/db_connect.php');
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test basic queries
        $tables = ['products', 'categories', 'subcategories', 'banners', 'menus'];
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<p>📊 Table '$table': " . $row['count'] . " records</p>";
            } else {
                echo "<p style='color: red;'>❌ Error querying table '$table': " . $conn->error . "</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test file permissions
echo "<h2>✅ File System Test</h2>";
$files_to_check = [
    '../config/db_connect.php',
    'css/style.css',
    'css/index.css',
    'css/banner-fix.css',
    '../images/banner1.jpg',
    '../images/banner2.jpg',
    'script.js'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ File exists: $file</p>";
    } else {
        echo "<p style='color: red;'>❌ File missing: $file</p>";
    }
}

// Test session
echo "<h2>✅ Session Test</h2>";
session_start();
echo "<p>Session ID: " . session_id() . "</p>";
if (isset($_SESSION['user'])) {
    echo "<p>User logged in: " . htmlspecialchars($_SESSION['user']['name']) . "</p>";
} else {
    echo "<p>No user logged in</p>";
}

// Test includes
echo "<h2>✅ Include Test</h2>";
try {
    ob_start();
    include 'slide-menu.php';
    $slide_menu_output = ob_get_clean();
    echo "<p style='color: green;'>✅ slide-menu.php included successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error including slide-menu.php: " . $e->getMessage() . "</p>";
}

// Test CSS variables
echo "<h2>✅ CSS Variables Test</h2>";
echo "<div style='background: var(--primary-color, #4CAF50); color: white; padding: 10px; margin: 10px 0;'>";
echo "CSS Variable Test (--primary-color)";
echo "</div>";

// Test JavaScript
echo "<h2>✅ JavaScript Test</h2>";
echo "<script>";
echo "console.log('JavaScript is working');";
echo "document.write('<p style=\"color: green;\">✅ JavaScript is working</p>');";
echo "</script>";

// Test Swiper
echo "<h2>✅ Swiper Test</h2>";
echo "<div id='swiper-test' style='width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;'>";
echo "<p>Swiper container ready</p>";
echo "</div>";

echo "<h2>🔗 Quick Links</h2>";
echo "<p><a href='index.php' style='color: #4CAF50;'>← Back to Homepage</a></p>";
echo "<p><a href='test.php' style='color: #4CAF50;'>→ Test Page</a></p>";
echo "<p><a href='banner-test.html' style='color: #4CAF50;'>→ Banner Test</a></p>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1, h2 { color: #2E7D32; }";
echo "p { margin: 5px 0; }";
echo "</style>";
?> 