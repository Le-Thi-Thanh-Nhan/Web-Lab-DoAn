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
    
    // Check if product exists
    $check_query = "SELECT product_id FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Sản phẩm không tồn tại');
    }
    
    // Check if already in wishlist
    $check_wishlist = "SELECT wishlist_id FROM wishlists WHERE customer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_wishlist);
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong danh sách yêu thích']);
        exit;
    }
    
    // Add to wishlist
    $query = "INSERT INTO wishlists (customer_id, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $customer_id, $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào danh sách yêu thích']);
    } else {
        throw new Exception('Không thể thêm sản phẩm vào danh sách yêu thích');
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()]);
}
?>