<?php
session_start();
require_once('../config/db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng mã giảm giá']);
    exit;
}

if (!isset($_POST['code_id']) || !isset($_POST['total_amount'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
    exit;
}

$code_id = intval($_POST['code_id']);
$total_amount = floatval($_POST['total_amount']);
$customer_id = $_SESSION['user']['customer_id'];

try {
    // Check if customer has already used this discount code
    $stmt = $conn->prepare("
        SELECT COUNT(*) as used_count
        FROM customer_discount_usage
        WHERE code_id = ? AND customer_id = ?
    ");

    if (!$stmt) {
        throw new Exception("Lỗi hệ thống: " . $conn->error);
    }

    $stmt->bind_param("ii", $code_id, $customer_id);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi truy vấn: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $usage = $result->fetch_assoc();
    $stmt->close();

    if ($usage['used_count'] > 0) {
        throw new Exception('Bạn đã sử dụng mã giảm giá này rồi');
    }

    // Check if the discount code is valid and active
    $stmt = $conn->prepare("
        SELECT * 
        FROM discount_codes 
        WHERE code_id = ? 
        AND is_active = 1
        AND end_date >= CURRENT_DATE
        AND (usage_limit IS NULL OR times_used < usage_limit)
    ");

    if (!$stmt) {
        throw new Exception("Lỗi hệ thống: " . $conn->error);
    }

    $stmt->bind_param("i", $code_id);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi truy vấn: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $discount = $result->fetch_assoc();
    $stmt->close();

    if (!$discount) {
        throw new Exception('Mã giảm giá không hợp lệ hoặc đã hết hạn');
    }

    if ($total_amount < $discount['min_order_value']) {
        throw new Exception('Giá trị đơn hàng chưa đạt tối thiểu ' . number_format($discount['min_order_value'], 0, ',', '.') . 'đ');
    }

    // Calculate discount amount
    $discount_amount = 0;
    if ($discount['discount_type'] === 'percentage') {
        $discount_amount = ($total_amount * $discount['discount_value']) / 100;
        if ($discount['max_discount'] && $discount_amount > $discount['max_discount']) {
            $discount_amount = $discount['max_discount'];
        }
    } else {
        $discount_amount = $discount['discount_value'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công!',
        'discount' => [
            'amount' => $discount_amount,
            'formatted_amount' => number_format($discount_amount, 0, ',', '.') . 'đ'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 