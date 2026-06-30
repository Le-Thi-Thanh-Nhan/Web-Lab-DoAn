<?php
// Database connection
function connectDB() {
    $conn = new mysqli('localhost', 'root', '', 'ql_thucpham');
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Check admin login
function checkAdminLogin() {
    session_start();
    if (!isset($_SESSION['admin'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Show notification
function showNotification($message, $type = 'success') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Update SQL file
function updateSQLFile($table = null) {
    $conn = connectDB();
    
    // Path to mysqldump
    $mysqldump = 'mysqldump';
    
    // Database credentials
    $db_name = 'ql_thucpham';
    $db_user = 'root';
    $db_pass = '';
    
    // Output file
    $output_file = __DIR__ . '/ql_thucpham.sql';
    
    // Build command
    $command = sprintf(
        '%s --user=%s --password=%s %s > %s',
        $mysqldump,
        escapeshellarg($db_user),
        escapeshellarg($db_pass),
        escapeshellarg($db_name),
        escapeshellarg($output_file)
    );
    
    // Execute command
    exec($command);
    
    return true;
}

// Format price
function formatPrice($price) {
    return number_format($price) . 'đ';
}

// Format date
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

// Get order status text
function getOrderStatus($status) {
    $statuses = [
        'Pending' => 'Chờ xử lý',
        'Processing' => 'Đang xử lý',
        'Shipping' => 'Đang giao hàng',
        'Completed' => 'Hoàn thành',
        'Cancelled' => 'Đã hủy'
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : $status;
}

// Upload image
function uploadImage($file, $target_dir) {
    if ($file['error'] === 0) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return str_replace('..', '', $target_file); // Return relative path
        }
    }
    return '';
}

// Validate input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 