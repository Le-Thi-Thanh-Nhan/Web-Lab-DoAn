<?php
session_start();
require_once('../config/db_connect.php');

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test query
$test_query = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($test_query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();
$total_products = $row['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page - Thực Phẩm Mộc</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #2E7D32, #1B5E20);
            color: white;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #81C784;
        }
        .status {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .success {
            background: rgba(76, 175, 80, 0.3);
            border-left: 4px solid #4CAF50;
        }
        .error {
            background: rgba(244, 67, 54, 0.3);
            border-left: 4px solid #F44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test Page - Thực Phẩm Mộc</h1>
        
        <div class="status success">
            <h3>✅ Database Connection</h3>
            <p>Kết nối database thành công!</p>
            <p>Tổng số sản phẩm trong database: <strong><?php echo $total_products; ?></strong></p>
        </div>
        
        <div class="status success">
            <h3>✅ Session Status</h3>
            <p>Session ID: <strong><?php echo session_id(); ?></strong></p>
            <?php if (isset($_SESSION['user'])): ?>
                <p>User logged in: <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong></p>
            <?php else: ?>
                <p>No user logged in</p>
            <?php endif; ?>
        </div>
        
        <div class="status success">
            <h3>✅ PHP Configuration</h3>
            <p>PHP Version: <strong><?php echo phpversion(); ?></strong></p>
            <p>Server: <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong></p>
        </div>
        
        <div class="status">
            <h3>📁 File Structure</h3>
            <p>Current directory: <strong><?php echo __DIR__; ?></strong></p>
            <p>Config file exists: <strong><?php echo file_exists('../config/db_connect.php') ? 'Yes' : 'No'; ?></strong></p>
            <p>CSS files exist: <strong><?php echo file_exists('css/style.css') ? 'Yes' : 'No'; ?></strong></p>
        </div>
        
        <div class="status">
            <h3>🔗 Quick Links</h3>
            <p><a href="index.php" style="color: #81C784;">← Back to Homepage</a></p>
            <p><a href="../auth/auth.php" style="color: #81C784;">→ Login/Register</a></p>
        </div>
    </div>
</body>
</html> 