<?php
session_start();
require_once __DIR__ . '/../includes/db_helper.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chức năng này']);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];
$product_id = (int)$_POST['product_id'];

try {
    $conn = getDBConnection();
    
    // Delete from wishlist
    $query = "DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $customer_id, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm khỏi danh sách yêu thích']);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
}
?> 