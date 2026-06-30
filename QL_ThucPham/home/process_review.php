<?php
session_start();
require_once('../config/db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['customer_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá sản phẩm']);
        exit;
    }

    $customer_id = $_SESSION['user']['customer_id'];
    $product_id = $_POST['product_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';

    // Validate inputs
    if (!$product_id || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    try {
        // Check if customer has purchased this product
        $check_purchase = $conn->prepare("
            SELECT 1 
            FROM orders o 
            JOIN orderdetails od ON o.order_id = od.order_id 
            WHERE o.customer_id = ? 
            AND od.product_id = ? 
            AND o.status = 'Completed'
            LIMIT 1
        ");
        $check_purchase->bind_param("ii", $customer_id, $product_id);
        $check_purchase->execute();
        $has_purchased = $check_purchase->get_result()->num_rows > 0;
        $check_purchase->close();

        if (!$has_purchased) {
            echo json_encode(['success' => false, 'message' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi mua và nhận hàng thành công']);
            exit;
        }

        // Check if user has already reviewed this product
        $check_stmt = $conn->prepare("SELECT review_id FROM product_reviews WHERE customer_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $customer_id, $product_id);
        $check_stmt->execute();
        $existing_review = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($existing_review) {
            // Update existing review
            $stmt = $conn->prepare("UPDATE product_reviews SET rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP WHERE customer_id = ? AND product_id = ?");
            $stmt->bind_param("isii", $rating, $comment, $customer_id, $product_id);
        } else {
            // Insert new review
            $stmt = $conn->prepare("INSERT INTO product_reviews (customer_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $customer_id, $product_id, $rating, $comment);
        }
        
        if ($stmt->execute()) {
            // Get updated review stats
            $stats_query = "SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM product_reviews 
                WHERE product_id = ?";
            
            $stats_stmt = $conn->prepare($stats_query);
            $stats_stmt->bind_param("i", $product_id);
            $stats_stmt->execute();
            $stats = $stats_stmt->get_result()->fetch_assoc();
            $stats_stmt->close();

            echo json_encode([
                'success' => true, 
                'message' => $existing_review ? 'Cập nhật đánh giá thành công!' : 'Thêm đánh giá thành công!',
                'stats' => $stats
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu đánh giá']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xử lý yêu cầu']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}

$conn->close();
?> 