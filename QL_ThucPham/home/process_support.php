<?php
session_start();
require_once('../config/db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    $customer_id = null;

    // If user is logged in, get their customer_id
    if (isset($_SESSION['user']) && isset($_SESSION['user']['customer_id'])) {
        $customer_id = $_SESSION['user']['customer_id'];
    }

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Vui lòng điền đầy đủ thông tin'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email không hợp lệ'
        ]);
        exit;
    }

    // Validate phone number (Vietnam format)
    if (!preg_match('/^(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})$/', $phone)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Số điện thoại không hợp lệ'
        ]);
        exit;
    }

    try {
        // Prepare and execute the SQL statement
        if ($customer_id) {
            $stmt = $conn->prepare("INSERT INTO support (customer_id, name, email, phone, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $customer_id, $name, $email, $phone, $message);
        } else {
            $stmt = $conn->prepare("INSERT INTO support (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $phone, $message);
        }

    if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Cảm ơn bạn đã gửi yêu cầu hỗ trợ. Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất!'
            ]);
    } else {
            throw new Exception('Có lỗi xảy ra khi gửi yêu cầu');
    }

    $stmt->close();
} catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Phương thức không được hỗ trợ'
    ]);
}

$conn->close();
?> 